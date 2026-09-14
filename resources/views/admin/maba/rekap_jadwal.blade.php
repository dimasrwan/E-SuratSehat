@extends('layouts.app')

@section('title', 'Rekap Operasional Jadwal Pemeriksaan')

@section('content')
<div class="space-y-5">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 py-1">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Rekap Operasional Jadwal Pemeriksaan</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">Breakdown progress pemeriksaan berdasarkan tanggal dan sesi jadwal.</p>
        </div>
        
        <!-- Filter Form Controls -->
        <form method="GET" action="{{ route('maba.rekapJadwal') }}" class="flex flex-wrap items-center gap-2">
            <div class="w-36 sm:w-40">
                @php
                    $tahunOptions = [];
                    foreach($tahunList as $t) {
                        $tahunOptions[(string)$t] = 'Maba ' . $t;
                    }
                @endphp
                <x-form-select name="tahun" id="tahun" :value="(string)$tahunSelected" placeholder="Pilih Tahun" :options="$tahunOptions" onchange="this.closest('form').submit()" />
            </div>
            <div class="w-52 sm:w-60">
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

    <!-- 4 KPI Overview Cards (Clean & Minimalist without decorative dots) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Maba -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-4.5 shadow-2xs">
            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider block">Total Maba (Filter Active)</span>
            <div class="mt-1.5">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($totalMaba) }}</span>
            </div>
        </div>

        <!-- Pemeriksaan Selesai -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-4.5 shadow-2xs">
            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider block">Pemeriksaan Selesai</span>
            <div class="mt-1.5">
                <span class="text-2xl sm:text-3xl font-extrabold text-emerald-600 tracking-tight">{{ number_format($totalSelesai) }}</span>
            </div>
        </div>

        <!-- Belum Diperiksa -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-4.5 shadow-2xs">
            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider block">Belum Diperiksa</span>
            <div class="mt-1.5">
                <span class="text-2xl sm:text-3xl font-extrabold text-amber-600 tracking-tight">{{ number_format($totalBelumDipemeriksa) }}</span>
            </div>
        </div>

        <!-- Terverifikasi -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-4.5 shadow-2xs">
            <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider block">Terverifikasi</span>
            <div class="mt-1.5">
                <span class="text-2xl sm:text-3xl font-extrabold text-sky-600 tracking-tight">{{ number_format($totalTerverifikasi) }}</span>
            </div>
        </div>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-emerald-50/40 border-b border-slate-200/80 text-[11px] uppercase font-semibold text-slate-700 tracking-wider">
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
                <tbody class="divide-y divide-slate-200/70">
                    @forelse($rekapJadwal as $row)
                    <tr class="hover:bg-slate-50/80 transition duration-150">
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            {{ $row->tanggal_jadwal ? \Carbon\Carbon::parse($row->tanggal_jadwal)->isoFormat('D MMMM Y') : 'Belum Terjadwal' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-semibold text-slate-800">{{ $row->sesi_jadwal ?? '-' }}</span>
                            @if($row->waktu_jadwal)
                                <span class="text-[11px] text-slate-500 block font-normal">{{ $row->waktu_jadwal }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-900">{{ number_format($row->total_maba) }}</td>
                        <td class="px-4 py-3 text-right text-slate-500 font-medium">{{ number_format($row->total_belum_mengisi) }}</td>
                        <td class="px-4 py-3 text-right text-amber-700 font-medium">{{ number_format($row->total_menunggu_verifikasi) }}</td>
                        <td class="px-4 py-3 text-right text-rose-700 font-medium">{{ number_format($row->total_perlu_perbaikan ?? 0) }}</td>
                        <td class="px-4 py-3 text-right text-sky-700 font-medium">{{ number_format($row->total_terverifikasi) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-700">{{ number_format($row->total_selesai) }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('maba.index', ['tahun' => $tahunSelected, 'tanggal' => $row->tanggal_jadwal, 'sesi' => $row->sesi_jadwal]) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200/80 hover:bg-emerald-100 transition duration-150">
                                Lihat Maba
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center py-2">
                                <svg class="w-7 h-7 text-emerald-600/70 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="font-normal text-xs text-slate-500">Belum ada jadwal pemeriksaan terdaftar.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


