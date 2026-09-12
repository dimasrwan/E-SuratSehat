@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Header Title & Top Controls -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Data Maba</h1>
            <p class="text-slate-500 text-sm mt-1">Pusat data operasional pendaftaran dan pemeriksaan kesehatan mahasiswa baru.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('maba.rekapJadwal') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition duration-150 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Rekap Jadwal
            </a>

            <a href="{{ route('maba.rekapProdi') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition duration-150 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Rekap Prodi
            </a>

            <a href="{{ route('maba.exportExcel', request()->all()) }}" class="px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition duration-150 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5">
        <form action="{{ route('maba.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Cari Data Maba</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nama / NIK / Email / Prodi..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500">
            </div>

            <!-- Tahun -->
            <div>
                <label for="tahun_masuk" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tahun Maba</label>
                <select name="tahun_masuk" id="tahun_masuk" onchange="this.form.submit()" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500">
                    <option value="all" {{ request('tahun_masuk') == 'all' ? 'selected' : '' }}>Semua Tahun</option>
                    @foreach($availableYears as $yr)
                    <option value="{{ $yr }}" {{ (string)$selectedTahun === (string)$yr ? 'selected' : '' }}>Maba {{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Biodata -->
            <div>
                <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status Biodata</label>
                <select name="status" id="status" onchange="this.form.submit()" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="BELUM_MENGISI" {{ request('status') == 'BELUM_MENGISI' ? 'selected' : '' }}>Belum Mengisi</option>
                    <option value="MENUNGGU_VERIFIKASI" {{ request('status') == 'MENUNGGU_VERIFIKASI' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="PERLU_PERBAIKAN" {{ request('status') == 'PERLU_PERBAIKAN' ? 'selected' : '' }}>Perlu Perbaikan</option>
                    <option value="TERVERIFIKASI" {{ request('status') == 'TERVERIFIKASI' ? 'selected' : '' }}>Terverifikasi</option>
                    <option value="PEMERIKSAAN_SELESAI" {{ request('status') == 'PEMERIKSAAN_SELESAI' ? 'selected' : '' }}>Pemeriksaan Selesai</option>
                </select>
            </div>

            <!-- Submit Controls -->
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition duration-150">
                    Filter
                </button>
                <a href="{{ route('maba.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition duration-150">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Mahasiswa</th>
                        <th class="px-6 py-4">Program Studi</th>
                        <th class="px-6 py-4">Jadwal Biro</th>
                        <th class="px-6 py-4">Status Biodata</th>
                        <th class="px-6 py-4">Pemeriksaan Medis</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($mabaList as $maba)
                    <tr class="hover:bg-slate-50/50 transition duration-150">
                        <td class="px-6 py-4">
                            <div class="font-extrabold text-slate-900 text-sm">{{ $maba->nama_lengkap ?? $maba->nama_biro }}</div>
                            @if($maba->nama_lengkap && $maba->nama_lengkap !== $maba->nama_biro)
                            <div class="text-[11px] text-slate-400">Biro: {{ $maba->nama_biro }}</div>
                            @endif
                            <div class="text-[11px] text-slate-400 font-mono">Tahun: Maba {{ $maba->tahunMaba->tahun ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-emerald-700">{{ $maba->program_studi_biro }}</div>
                            <div class="text-slate-500 text-[11px]">{{ $maba->fakultas ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">{{ $maba->tanggal_jadwal ? $maba->tanggal_jadwal->format('d M Y') : '-' }}</div>
                            <div class="text-slate-400 text-[11px]">Sesi {{ $maba->sesi_jadwal ?? '-' }} ({{ $maba->waktu_jadwal ?? '-' }})</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($maba->status_biodata === 'BELUM_MENGISI')
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full font-bold text-[10px] uppercase">Belum Mengisi</span>
                            @elseif($maba->status_biodata === 'MENUNGGU_VERIFIKASI')
                                <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full font-bold text-[10px] uppercase">Menunggu Verifikasi</span>
                            @elseif($maba->status_biodata === 'PERLU_PERBAIKAN')
                                <span class="px-3 py-1 bg-rose-100 text-rose-800 rounded-full font-bold text-[10px] uppercase">Perlu Perbaikan</span>
                            @elseif($maba->status_biodata === 'TERVERIFIKASI')
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full font-bold text-[10px] uppercase">Terverifikasi</span>
                            @elseif($maba->status_biodata === 'PEMERIKSAAN_SELESAI')
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-bold text-[10px] uppercase">Pemeriksaan Selesai</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($maba->pemeriksaan)
                                <div class="font-bold text-slate-900 font-mono text-[11px]">{{ $maba->pemeriksaan->nomor_surat }}</div>
                                <span class="text-[10px] font-semibold text-emerald-600">SELESAI</span>
                            @else
                                <span class="text-[11px] text-slate-400">Belum diperiksa</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('maba.show', $maba->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition duration-150">
                                    Detail
                                </a>

                                @if($maba->status_biodata === 'MENUNGGU_VERIFIKASI')
                                <a href="{{ route('admin.maba-verifikasi.show', $maba->id) }}" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-xs shadow-sm transition duration-150">
                                    Verifikasi
                                </a>
                                @elseif($maba->status_biodata === 'TERVERIFIKASI')
                                <a href="{{ route('pemeriksaan.create', ['maba_id' => $maba->id]) }}" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-lg text-xs shadow-sm transition duration-150">
                                    + Input Exam
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400 font-medium">
                            Tidak ada data Maba yang sesuai dengan kriteria filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mabaList->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $mabaList->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
