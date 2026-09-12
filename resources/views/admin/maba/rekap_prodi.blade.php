@extends('layouts.app')

@section('title', 'Rekap Operasional Program Studi')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Rekap Operasional Program Studi</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Breakdown progress pengisian biodata & pemeriksaan medis per Program Studi Biro.</p>
        </div>
        
        <!-- Filter Form -->
        <form method="GET" action="{{ route('maba.rekapProdi') }}" class="flex items-center gap-3">
            <div>
                <label for="tahun" class="sr-only">Tahun</label>
                <select name="tahun" id="tahun" onchange="this.form.submit()" class="text-sm rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ (string)$tahunSelected === (string)$t ? 'selected' : '' }}>
                            Tahun {{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold">
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
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($rekapProdi as $index => $row)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="px-4 py-3 text-xs text-slate-400 font-mono">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                            {{ $row->program_studi_biro ?? 'Belum Ditentukan' }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">{{ number_format($row->total_maba) }}</td>
                        <td class="px-4 py-3 text-right text-slate-500">{{ number_format($row->total_belum_mengisi) }}</td>
                        <td class="px-4 py-3 text-right text-amber-600 dark:text-amber-400">{{ number_format($row->total_menunggu_verifikasi) }}</td>
                        <td class="px-4 py-3 text-right text-red-600 dark:text-red-400">{{ number_format($row->total_perlu_perbaikan) }}</td>
                        <td class="px-4 py-3 text-right text-blue-600 dark:text-blue-400">{{ number_format($row->total_terverifikasi) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format($row->total_selesai) }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('maba.index', ['tahun' => $tahunSelected, 'prodi' => $row->program_studi_biro]) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 hover:bg-emerald-100">
                                Filter Maba
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                            Belum ada data Program Studi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
