@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <!-- Page Header & Top Action Controls -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 py-1">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Data Maba</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">Pusat data operasional pendaftaran dan pemeriksaan kesehatan mahasiswa baru.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('maba.rekapJadwal') }}" class="px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-medium text-xs rounded-md shadow-none transition duration-150 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Rekap Jadwal
            </a>

            <a href="{{ route('maba.rekapProdi') }}" class="px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-medium text-xs rounded-md shadow-none transition duration-150 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Rekap Prodi
            </a>

            <a href="{{ route('maba.exportExcel', request()->all()) }}" class="px-3.5 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-medium text-xs rounded-md shadow-none transition duration-150 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Area Toolbar -->
    <div class="bg-white rounded-lg border border-slate-200/80 shadow-2xs p-4 sm:p-5">
        <form action="{{ route('maba.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
            <!-- Search Field (Widest column ~40%) -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-[10px] font-medium text-slate-500 uppercase tracking-wider mb-1">Cari Data Maba</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nama / NIK / Email / Prodi..." class="w-full h-10 px-3 bg-slate-50 border border-slate-300 rounded-md text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition duration-150">
            </div>

            <!-- Tahun Field (~18%) -->
            <div>
                <label for="tahun_masuk" class="block text-[10px] font-medium text-slate-500 uppercase tracking-wider mb-1">Tahun Maba</label>
                @php
                    $tahunOptions = ['all' => 'Semua Tahun'];
                    foreach ($availableYears as $yr) {
                        $tahunOptions[(string)$yr] = 'Maba ' . $yr;
                    }
                @endphp
                <x-form-select name="tahun_masuk" id="tahun_masuk" :value="request('tahun_masuk', (string)$selectedTahun)" :options="$tahunOptions" onchange="this.closest('form').submit()" />
            </div>

            <!-- Status Biodata Field (~20%) -->
            <div>
                <label for="status" class="block text-[10px] font-medium text-slate-500 uppercase tracking-wider mb-1">Status Biodata</label>
                @php
                    $statusOptions = [
                        '' => 'Semua Status',
                        'BELUM_MENGISI' => 'Belum Mengisi',
                        'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi',
                        'PERLU_PERBAIKAN' => 'Perlu Perbaikan',
                        'TERVERIFIKASI' => 'Terverifikasi',
                        'PEMERIKSAAN_SELESAI' => 'Pemeriksaan Selesai',
                    ];
                @endphp
                <x-form-select name="status" id="status" :value="request('status', '')" :options="$statusOptions" onchange="this.closest('form').submit()" />
            </div>

            <!-- Action Controls (~20%) -->
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full h-10 px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-medium text-xs rounded-md shadow-none transition duration-150 flex items-center justify-center">
                    Filter
                </button>
                <a href="{{ route('maba.index') }}" class="h-10 px-3.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-medium text-xs rounded-md shadow-none transition duration-150 flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white rounded-lg border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[10px] font-semibold tracking-wider">
                    <tr>
                        <th class="py-2.5 px-4">Mahasiswa</th>
                        <th class="py-2.5 px-4">Program Studi</th>
                        <th class="py-2.5 px-4">Jadwal Biro</th>
                        <th class="py-2.5 px-4">Status Biodata</th>
                        <th class="py-2.5 px-4">Pemeriksaan Medis</th>
                        <th class="py-2.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($mabaList as $maba)
                    <tr class="hover:bg-slate-50/70 transition duration-150">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $maba->nama_lengkap ?? $maba->nama_biro }}</div>
                            @if($maba->nama_lengkap && $maba->nama_lengkap !== $maba->nama_biro)
                            <div class="text-[11px] text-slate-400">Biro: {{ $maba->nama_biro }}</div>
                            @endif
                            <div class="text-[11px] text-slate-400 font-mono">Tahun: Maba {{ $maba->tahunMaba->tahun ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-emerald-700">{{ $maba->program_studi_biro }}</div>
                            <div class="text-slate-500 text-[11px]">{{ $maba->fakultas ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-800">{{ $maba->tanggal_jadwal ? $maba->tanggal_jadwal->format('d M Y') : '-' }}</div>
                            <div class="text-slate-400 text-[11px]">Sesi {{ $maba->sesi_jadwal ?? '-' }} ({{ $maba->waktu_jadwal ?? '-' }})</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($maba->status_biodata === 'BELUM_MENGISI')
                                <span class="px-2.5 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-semibold uppercase border border-slate-200">Belum Mengisi</span>
                            @elseif($maba->status_biodata === 'MENUNGGU_VERIFIKASI')
                                <span class="px-2.5 py-0.5 bg-amber-50 text-amber-800 rounded text-[10px] font-semibold uppercase border border-amber-200">Menunggu Verifikasi</span>
                            @elseif($maba->status_biodata === 'PERLU_PERBAIKAN')
                                <span class="px-2.5 py-0.5 bg-rose-50 text-rose-800 rounded text-[10px] font-semibold uppercase border border-rose-200">Perlu Perbaikan</span>
                            @elseif($maba->status_biodata === 'TERVERIFIKASI')
                                <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-800 rounded text-[10px] font-semibold uppercase border border-emerald-200">Terverifikasi</span>
                            @elseif($maba->status_biodata === 'PEMERIKSAAN_SELESAI')
                                <span class="px-2.5 py-0.5 bg-sky-50 text-sky-800 rounded text-[10px] font-semibold uppercase border border-sky-200">Pemeriksaan Selesai</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($maba->pemeriksaan)
                                <div class="font-semibold text-slate-900 font-mono text-[11px]">{{ $maba->pemeriksaan->nomor_surat }}</div>
                                <span class="text-[10px] font-semibold text-emerald-700">SELESAI</span>
                            @else
                                <span class="text-[11px] text-slate-400">Belum diperiksa</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('maba.show', $maba->id) }}" class="px-2.5 py-1 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-medium rounded text-xs transition">
                                    Detail
                                </a>

                                @if($maba->status_biodata === 'MENUNGGU_VERIFIKASI')
                                <a href="{{ route('admin.maba-verifikasi.show', $maba->id) }}" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded text-xs transition">
                                    Verifikasi
                                </a>
                                @elseif($maba->status_biodata === 'TERVERIFIKASI')
                                <a href="{{ route('pemeriksaan.create', ['maba_id' => $maba->id]) }}" class="px-2.5 py-1 bg-emerald-700 hover:bg-emerald-800 text-white font-medium rounded text-xs transition">
                                    + Input Exam
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-xs text-slate-400 font-normal">
                            @if(request()->hasAny(['search', 'status', 'tahun_masuk']))
                                Tidak ada data Maba yang sesuai dengan kriteria filter.
                            @else
                                Belum ada data Maba untuk ditampilkan.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mabaList->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
            {{ $mabaList->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
