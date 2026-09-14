@extends('layouts.app')

@section('title', 'Rekap Operasional Program Studi')

@section('content')
<div class="space-y-5">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 py-1">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Rekap Operasional Program Studi</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">Breakdown progress pengisian biodata & pemeriksaan medis per Program Studi Biro.</p>
        </div>
        
        <!-- Filter Form -->
        <form method="GET" action="{{ route('maba.rekapProdi') }}" class="flex items-center gap-2 w-36 sm:w-40">
            @php
                $tahunOptions = [];
                foreach($tahunList as $t) {
                    $tahunOptions[(string)$t] = 'Maba ' . $t;
                }
            @endphp
            <x-form-select name="tahun" id="tahun" :value="(string)$tahunSelected" placeholder="Pilih Tahun" :options="$tahunOptions" onchange="this.closest('form').submit()" />
        </form>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-emerald-50/40 border-b border-slate-200/80 text-[11px] uppercase font-semibold text-slate-700 tracking-wider">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Program Studi Biro</th>
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
                    @forelse($rekapProdi as $index => $row)
                    <tr class="hover:bg-slate-50/80 transition duration-150">
                        <td class="px-4 py-3 text-xs text-slate-400 font-mono">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            {{ $row->program_studi_biro ?? 'Belum Ditentukan' }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-900">{{ number_format($row->total_maba) }}</td>
                        <td class="px-4 py-3 text-right text-slate-500 font-medium">{{ number_format($row->total_belum_mengisi) }}</td>
                        <td class="px-4 py-3 text-right text-amber-700 font-medium">{{ number_format($row->total_menunggu_verifikasi) }}</td>
                        <td class="px-4 py-3 text-right text-rose-700 font-medium">{{ number_format($row->total_perlu_perbaikan) }}</td>
                        <td class="px-4 py-3 text-right text-sky-700 font-medium">{{ number_format($row->total_terverifikasi) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-700">{{ number_format($row->total_selesai) }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('maba.index', ['tahun' => $tahunSelected, 'prodi' => $row->program_studi_biro]) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200/80 hover:bg-emerald-100 transition duration-150">
                                Filter Maba
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center py-2">
                                <svg class="w-7 h-7 text-emerald-600/70 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                <span class="font-normal text-xs text-slate-500">Belum ada data Program Studi.</span>
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



