<?php

namespace App\Services;

use App\Models\MabaData;
use App\Models\TahunMaba;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PortalMabaService
{
    /**
     * Get currently active TahunMaba model instance.
     */
    public function getActiveYear(): ?TahunMaba
    {
        return TahunMabaService::getActiveYear();
    }

    /**
     * Search Maba records in the active year only.
     * Returns ONLY non-sensitive columns (Nama, Prodi, Jadwal).
     */
    public function searchMaba(string $keyword, ?string $prodiFilter = null)
    {
        $activeYear = $this->getActiveYear();
        if (!$activeYear) {
            return collect();
        }

        $cleanKeyword = mb_strtolower(trim($keyword), 'UTF-8');

        $query = MabaData::query()
            ->where('tahun_maba_id', $activeYear->id)
            ->where(function ($q) use ($cleanKeyword) {
                $q->whereRaw('LOWER(nama_biro) LIKE ?', ["%{$cleanKeyword}%"])
                  ->orWhereRaw('LOWER(nama_lengkap) LIKE ?', ["%{$cleanKeyword}%"]);
            });

        if (!empty($prodiFilter)) {
            $cleanProdi = mb_strtolower(trim($prodiFilter), 'UTF-8');
            $query->whereRaw('LOWER(program_studi_biro) LIKE ?', ["%{$cleanProdi}%"]);
        }

        // Select ONLY non-sensitive public discovery fields
        return $query->select([
            'id',
            'nama_biro',
            'program_studi_biro',
            'tanggal_jadwal',
            'sesi_jadwal',
            'waktu_jadwal',
            'status_biodata',
        ])
        ->limit(10)
        ->get();
    }

    /**
     * Claim a Maba record and create temporary server session token (30 min expiry).
     */
    public function claimRecord(int $mabaDataId): MabaData
    {
        $activeYear = $this->getActiveYear();
        if (!$activeYear) {
            throw new \Exception('Sistem belum memiliki Tahun Maba aktif.');
        }

        $mabaData = MabaData::where('tahun_maba_id', $activeYear->id)
            ->where('id', $mabaDataId)
            ->firstOrFail();

        // Reject if already submitted for verification, verified, or completed
        if (in_array($mabaData->status_biodata, ['MENUNGGU_VERIFIKASI', 'TERVERIFIKASI', 'PEMERIKSAAN_SELESAI'])) {
            throw new \Exception('Data Maba ini sedang menunggu verifikasi, telah terverifikasi, atau telah selesai.');
        }

        // If MENUNGGU_VERIFIKASI, allow view/resubmit only if specifically allowed or show info
        $claimToken = Str::uuid()->toString();

        session([
            'portal_claim' => [
                'token' => $claimToken,
                'maba_data_id' => $mabaData->id,
                'expires_at' => now()->addMinutes(30)->timestamp,
            ]
        ]);

        return $mabaData;
    }

    /**
     * Get currently claimed MabaData record from server session.
     */
    public function getClaimedMabaData(): ?MabaData
    {
        $claim = session('portal_claim');
        if (!$claim || !isset($claim['token'], $claim['maba_data_id'], $claim['expires_at'])) {
            return null;
        }

        if (now()->timestamp > $claim['expires_at']) {
            session()->forget('portal_claim');
            return null;
        }

        $activeYear = $this->getActiveYear();
        if (!$activeYear) {
            return null;
        }

        return MabaData::where('tahun_maba_id', $activeYear->id)
            ->where('id', $claim['maba_data_id'])
            ->first();
    }

    /**
     * Submit biodata form filled by Maba.
     */
    public function submitBiodata(array $validatedInput): MabaData
    {
        $mabaData = $this->getClaimedMabaData();
        if (!$mabaData) {
            throw new \Exception('Sesi pengisian telah berakhir atau tidak valid. Silakan cari data Anda kembali.');
        }

        // Strictly enforce Field Ownership Rules:
        // Program studi must be derived from program_studi_biro!
        $prodiFinal = $mabaData->program_studi_biro;

        // Calculate age on server side if tanggal_lahir is present
        $tanggalLahir = !empty($validatedInput['tanggal_lahir']) ? Carbon::parse($validatedInput['tanggal_lahir']) : null;

        $jkRaw = $validatedInput['jenis_kelamin'] ?? null;
        $jkFinal = null;
        if (in_array($jkRaw, ['Laki-laki', 'Laki-Laki', 'L'])) {
            $jkFinal = 'L';
        } elseif (in_array($jkRaw, ['Perempuan', 'P'])) {
            $jkFinal = 'P';
        }

        // Whitelist fillable data
        $updateData = [
            'nama_lengkap' => trim($validatedInput['nama_lengkap']),
            'nik' => preg_replace('/[^0-9]/', '', $validatedInput['nik']),
            'email' => strtolower(trim($validatedInput['email'])),
            'tempat_lahir' => trim($validatedInput['tempat_lahir']),
            'tanggal_lahir' => $tanggalLahir ? $tanggalLahir->format('Y-m-d') : null,
            'jenis_kelamin' => $jkFinal,
            'agama' => $validatedInput['agama'],
            'fakultas' => trim($validatedInput['fakultas']),
            'program_studi' => $prodiFinal,
            'pekerjaan' => isset($validatedInput['pekerjaan']) ? trim($validatedInput['pekerjaan']) : 'Mahasiswa',
            'alamat' => trim($validatedInput['alamat']),
            'status_biodata' => 'MENUNGGU_VERIFIKASI',
            'catatan_perbaikan' => null,
            'verified_at' => null,
            'verified_by' => null,
        ];

        $mabaData->update($updateData);

        // Invalidate session claim
        session()->forget('portal_claim');

        return $mabaData->fresh();
    }

    /**
     * Cancel current claim session.
     */
    public function forgetClaim(): void
    {
        session()->forget('portal_claim');
    }
}
