@extends('layouts.app')

@section('title', 'Rekap Operasional Jadwal Pemeriksaan')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Rekap Operasional Jadwal Pemeriksaan</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Breakdown progress pemeriksaan berdasarkan tanggal dan sesi jadwal.</p>
        </div>
        
        <!-- Filter Form -->
        <form method="GET" action="{{ route('maba.rekapJadwal') }}" class="flex flex-wrap items-center gap-3">
            <div class="w-40">
                @php
                    $tahunOptions = [];
                    foreach($tahunList as $t) {
                        $tahunOptions[(string)$t] = 'Tahun ' . $t;
                    }
                @endphp
                <x-form-select name="tahun" id="tahun" :value="(string)$tahunSelected" placeholder="Pilih Tahun" :options="$tahunOptions" onchange="this.closest('form').submit()" />
            </div>
            <div class="w-56">
                @php
                    $prodiOptions = ['' => 'Semua Program Studi'];
                    foreach($prodiList as $p) {
                        $prodiOptions[$p] = $p;
                    }
                @endphp
                <x-form-select name="prodi" id="prodi" :value="$prodiSelected" placeholder="Semua Program Studi" :options="$prodiOptions" onchange="this.closest('form').submit()" />
            </div>
        </form>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Maba (Filter Active)</span>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ number_format($totalMaba) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Pemeriksaan Selesai</span>
            <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($totalSelesai) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
            <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Belum Diperiksa</span>
            <p class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($totalBelumDipemeriksa) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
            <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Terverifikasi</span>
            <p class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($totalTerverifikasi) }}</p>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold">
                    <tr>
                        <th class="px-4 py-3">Tanggal Jadwal</th>
                        <th class="px-4 py-3">Sesi / Waktu</th>
                        <th class="px-4 py-3 text-right">Total Maba</th>
                        <th class="px-4 py-3 text-right">Belum Mengisi</th>
                        <th class="px-4 py-3 text-right">Menunggu Verifikasi</th>
                        <th class="px-4 py-3 text-right">Perlu Perbaikan</th>
                        <th class="px-4 py-3 text-right">Terverifikasi</th>
                        <th class="px-4 py-3 text-right">Selesai Pemeriksaan</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($rekapJadwal as $row)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                            {{ $row->tanggal_jadwal ? \Carbon\Carbon::parse($row->tanggal_jadwal)->isoFormat('D MMMM Y') : 'Belum Terjadwal' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-semibold">{{ $row->sesi_jadwal ?? '-' }}</span>
                            @if($row->waktu_jadwal)
                                <span class="text-xs text-slate-400 block">{{ $row->waktu_jadwal }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">{{ number_format($row->total_maba) }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ number_format($row->total_belum_mengisi) }}</td>
                        <td class="px-4 py-3 text-right text-amber-600 dark:text-amber-400">{{ number_format($row->total_menunggu_verifikasi) }}</td>
                        <td class="px-4 py-3 text-right text-red-600 dark:text-red-400">{{ number_format($row->total_perlu_perbaikan ?? 0) }}</td>
                        <td class="px-4 py-3 text-right text-blue-600 dark:text-blue-400">{{ number_format($row->total_terverifikasi) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format($row->total_selesai) }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('maba.index', ['tahun' => $tahunSelected, 'tanggal' => $row->tanggal_jadwal, 'sesi' => $row->sesi_jadwal]) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 hover:bg-emerald-100">
                                Lihat Maba
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                            Belum ada jadwal pemeriksaan terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
