<?php

namespace App\Services;

use App\Models\MabaData;
use App\Models\Pemeriksaan;
use App\Models\TahunMaba;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardMonitoringService
{
    /**
     * Calculate comprehensive dashboard monitoring statistics.
     */
    public function getDashboardStats(?string $selectedTahun = null, ?User $user = null): array
    {
        $activeYearModel = TahunMabaService::getActiveYear();
        $activeYearInt = TahunMabaService::getActiveYearInt();

        $yearsInMaster = TahunMaba::orderBy('tahun', 'desc')->pluck('tahun')->toArray();
        $yearsInDb = Pemeriksaan::select('tahun_masuk')->distinct()->orderBy('tahun_masuk', 'desc')->pluck('tahun_masuk')->toArray();
        $availableYears = array_unique(array_merge([$activeYearInt], $yearsInMaster, $yearsInDb));
        rsort($availableYears);

        if ($selectedTahun === null) {
            $selectedTahun = (string)$activeYearInt;
        } else if ($selectedTahun !== 'all') {
            $selectedTahun = preg_replace('/[^0-9]/', '', (string)$selectedTahun);
            if ($selectedTahun !== '' && !in_array((int)$selectedTahun, $availableYears)) {
                $selectedTahun = (string)$activeYearInt;
            }
        }

        $targetYearInt = ($selectedTahun !== 'all' && $selectedTahun !== '') ? (int)$selectedTahun : null;
        $tahunMabaModel = $targetYearInt ? TahunMaba::where('tahun', $targetYearInt)->first() : null;

        // Base Maba Query
        $mabaQuery = MabaData::query();
        if ($targetYearInt && $tahunMabaModel) {
            $mabaQuery->where('tahun_maba_id', $tahunMabaModel->id);
        }

        // Maba Status Counts (DB Aggregation)
        $statusCounts = (clone $mabaQuery)
            ->select('status_biodata', DB::raw('COUNT(*) as total'))
            ->groupBy('status_biodata')
            ->pluck('total', 'status_biodata')
            ->toArray();

        $totalMaba = (clone $mabaQuery)->count();
        $belumMengisi = $statusCounts['BELUM_MENGISI'] ?? 0;
        $menungguVerifikasi = $statusCounts['MENUNGGU_VERIFIKASI'] ?? 0;
        $perluPerbaikan = $statusCounts['PERLU_PERBAIKAN'] ?? 0;
        $terverifikasi = $statusCounts['TERVERIFIKASI'] ?? 0;
        $pemeriksaanSelesai = $statusCounts['PEMERIKSAAN_SELESAI'] ?? 0;

        $belumDiperiksa = $terverifikasi; // TERVERIFIKASI but not yet PEMERIKSAAN_SELESAI

        $progressPercentage = $totalMaba > 0 ? round(($pemeriksaanSelesai / $totalMaba) * 100, 1) : 0;

        // Base Pemeriksaan Query
        $pemQuery = Pemeriksaan::query();
        if ($targetYearInt) {
            $pemQuery->where('tahun_masuk', $targetYearInt);
        }

        $totalPemeriksaan = (clone $pemQuery)->count();
        $emailTerkirim = (clone $pemQuery)->where('status_pengiriman', 'Terkirim')->count();
        $emailDalamAntrean = (clone $pemQuery)->whereIn('status_pengiriman', ['Dalam antrean', 'Mengirim', 'Proses'])->count();
        $emailGagal = (clone $pemQuery)->where('status_pengiriman', 'Gagal')->count();
        $emailBelum = (clone $pemQuery)->where(function ($q) {
            $q->whereNull('status_pengiriman')->orWhere('status_pengiriman', 'Menunggu');
        })->count();

        // Program Studi Distribution (Top 6)
        $prodiDistribution = [];
        if ($totalMaba > 0) {
            $prodiDistribution = (clone $mabaQuery)
                ->select('program_studi_biro', DB::raw('COUNT(*) as total'))
                ->groupBy('program_studi_biro')
                ->orderBy('total', 'desc')
                ->take(6)
                ->get();
        }

        // Schedule Breakdown
        $jadwalBreakdown = [];
        if ($totalMaba > 0) {
            $jadwalBreakdown = (clone $mabaQuery)
                ->whereNotNull('tanggal_jadwal')
                ->select('tanggal_jadwal', DB::raw('COUNT(*) as total'), DB::raw("SUM(CASE WHEN status_biodata = 'PEMERIKSAAN_SELESAI' THEN 1 ELSE 0 END) as selesai"))
                ->groupBy('tanggal_jadwal')
                ->orderBy('tanggal_jadwal', 'asc')
                ->take(5)
                ->get();
        }

        // Pemeriksaan Hari Ini
        $todayDate = Carbon::today()->format('Y-m-d');
        $hariIniTotal = (clone $mabaQuery)->whereDate('tanggal_jadwal', $todayDate)->count();
        $hariIniSelesai = (clone $mabaQuery)->whereDate('tanggal_jadwal', $todayDate)->where('status_biodata', 'PEMERIKSAAN_SELESAI')->count();
        $hariIniBelumDiperiksa = (clone $mabaQuery)->whereDate('tanggal_jadwal', $todayDate)->where('status_biodata', 'TERVERIFIKASI')->count();
        $hariIniMenungguVerifikasi = (clone $mabaQuery)->whereDate('tanggal_jadwal', $todayDate)->where('status_biodata', 'MENUNGGU_VERIFIKASI')->count();
        $hariIniPerluPerhatian = (clone $mabaQuery)->whereDate('tanggal_jadwal', $todayDate)->whereIn('status_biodata', ['BELUM_MENGISI', 'PERLU_PERBAIKAN'])->count();
        $hariIniBelum = max(0, $hariIniTotal - $hariIniSelesai);

        // Actionable Previews
        $pendingVerifications = (clone $mabaQuery)
            ->where('status_biodata', 'MENUNGGU_VERIFIKASI')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        $perluPerbaikans = (clone $mabaQuery)
            ->where('status_biodata', 'PERLU_PERBAIKAN')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        $pemeriksaanTerbaru = (clone $pemQuery)->latest()->take(5)->get();

        // User Counts
        $totalUser = User::count();
        $totalAdmin = User::where('role', 'admin')->count();
        $totalOperator = User::where('role', 'operator')->count();

        return compact(
            'activeYearModel',
            'activeYearInt',
            'availableYears',
            'selectedTahun',
            'targetYearInt',
            'totalMaba',
            'belumMengisi',
            'menungguVerifikasi',
            'perluPerbaikan',
            'terverifikasi',
            'pemeriksaanSelesai',
            'belumDiperiksa',
            'progressPercentage',
            'totalPemeriksaan',
            'emailTerkirim',
            'emailDalamAntrean',
            'emailGagal',
            'emailBelum',
            'prodiDistribution',
            'jadwalBreakdown',
            'hariIniTotal',
            'hariIniSelesai',
            'hariIniBelum',
            'hariIniBelumDiperiksa',
            'hariIniMenungguVerifikasi',
            'hariIniPerluPerhatian',
            'pendingVerifications',
            'perluPerbaikans',
            'pemeriksaanTerbaru',
            'totalUser',
            'totalAdmin',
            'totalOperator'
        );
    }
}
