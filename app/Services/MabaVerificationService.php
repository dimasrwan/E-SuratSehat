<?php

namespace App\Services;

use App\Models\MabaData;
use App\Models\User;

class MabaVerificationService
{
    /**
     * Get Maba records pending verification for Operator dashboard.
     */
    public function getVerifications(?int $tahunId = null, ?string $search = null, ?string $statusFilter = null)
    {
        $activeYear = TahunMabaService::getActiveYear();
        $tahunIdTarget = $tahunId ?? ($activeYear ? $activeYear->id : null);

        $query = MabaData::query()->with(['tahunMaba', 'verifier']);

        if ($tahunIdTarget) {
            $query->where('tahun_maba_id', $tahunIdTarget);
        }

        if (!empty($statusFilter)) {
            $query->where('status_biodata', $statusFilter);
        } else {
            $query->whereIn('status_biodata', ['MENUNGGU_VERIFIKASI', 'PERLU_PERBAIKAN', 'TERVERIFIKASI', 'PEMERIKSAAN_SELESAI']);
        }

        if (!empty($search)) {
            $clean = mb_strtolower(trim($search), 'UTF-8');
            $query->where(function ($q) use ($clean) {
                $q->whereRaw('LOWER(nama_biro) LIKE ?', ["%{$clean}%"])
                  ->orWhereRaw('LOWER(nama_lengkap) LIKE ?', ["%{$clean}%"])
                  ->orWhere('nik', 'like', "%{$clean}%")
                  ->orWhereRaw('LOWER(program_studi_biro) LIKE ?', ["%{$clean}%"]);
            });
        }

        return $query->orderBy('updated_at', 'desc')->paginate(15);
    }

    /**
     * Verify physical identity of Maba by Operator.
     */
    public function verifyIdentity(MabaData $mabaData, User $operator): MabaData
    {
        if (in_array($mabaData->status_biodata, ['BELUM_MENGISI'])) {
            throw new \Exception('Mahasiswa belum mengisi biodata pribadi.');
        }

        $mabaData->update([
            'status_biodata' => 'TERVERIFIKASI',
            'verified_at' => now(),
            'verified_by' => $operator->id,
        ]);

        return $mabaData->fresh();
    }

    /**
     * Request correction for biodata by Operator.
     */
    public function requestCorrection(MabaData $mabaData, User $operator, ?string $catatan = null): MabaData
    {
        $mabaData->update([
            'status_biodata' => 'PERLU_PERBAIKAN',
            'catatan_perbaikan' => $catatan ? trim($catatan) : null,
        ]);

        return $mabaData->fresh();
    }
}
