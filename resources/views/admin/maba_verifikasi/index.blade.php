@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Verifikasi Identitas Maba</h1>
            <p class="text-slate-500 text-sm mt-1">Verifikasi identitas fisik mahasiswa baru sebelum proses pemeriksaan medis oleh Dokter/Operator.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center gap-3">
        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>{{ session('error') }}</div>
    </div>
    @endif

    <!-- Filters Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 mb-8">
        <form action="{{ route('admin.maba-verifikasi.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Cari Maba</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nama / NIK / Prodi..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status Verification</label>
                <x-form-select name="status" id="status" :value="request('status', '')" placeholder="Semua Status Valid" :options="[
                    'MENUNGGU_VERIFIKASI' => 'MENUNGGU VERIFIKASI',
                    'PERLU_PERBAIKAN' => 'PERLU PERBAIKAN',
                    'TERVERIFIKASI' => 'TERVERIFIKASI',
                    'PEMERIKSAAN_SELESAI' => 'PEMERIKSAAN SELESAI'
                ]" onchange="this.closest('form').submit()" />
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition duration-150">
                    Filter Data
                </button>
                <a href="{{ route('admin.maba-verifikasi.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition duration-150">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Mahasiswa</th>
                        <th class="px-6 py-4">Prodi & Fakultas</th>
                        <th class="px-6 py-4">Identitas (NIK & Email)</th>
                        <th class="px-6 py-4">Status Biodata</th>
                        <th class="px-6 py-4 text-right">Aksi Operator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($verifications as $maba)
                    <tr class="hover:bg-slate-50/50 transition duration-150">
                        <td class="px-6 py-4">
                            <div class="font-extrabold text-slate-900 text-sm">{{ $maba->nama_lengkap ?? $maba->nama_biro }}</div>
                            <div class="text-[11px] text-slate-400">Tahun: Maba {{ $maba->tahunMaba->tahun ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-emerald-700">{{ $maba->program_studi_biro }}</div>
                            <div class="text-slate-500">{{ $maba->fakultas ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 space-y-0.5">
                            <div><span class="font-mono text-slate-700">NIK: {{ $maba->nik ?? '-' }}</span></div>
                            <div class="text-slate-500">{{ $maba->email ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($maba->status_biodata === 'MENUNGGU_VERIFIKASI')
                                <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full font-bold text-[10px] uppercase">Menunggu Verifikasi</span>
                            @elseif($maba->status_biodata === 'TERVERIFIKASI')
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full font-bold text-[10px] uppercase">Terverifikasi</span>
                            @elseif($maba->status_biodata === 'PERLU_PERBAIKAN')
                                <span class="px-3 py-1 bg-rose-100 text-rose-800 rounded-full font-bold text-[10px] uppercase">Perlu Perbaikan</span>
                            @elseif($maba->status_biodata === 'PEMERIKSAAN_SELESAI')
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-bold text-[10px] uppercase">Pemeriksaan Selesai</span>
                            @else
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full font-bold text-[10px] uppercase">{{ $maba->status_biodata }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.maba-verifikasi.show', $maba->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition duration-150">
                                    Detail
                                </a>

                                @if($maba->status_biodata === 'MENUNGGU_VERIFIKASI' || $maba->status_biodata === 'PERLU_PERBAIKAN')
                                <form action="{{ route('admin.maba-verifikasi.verify', $maba->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Verifikasi identitas fisik mahasiswa ini?')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs shadow-sm transition duration-150">
                                        Verifikasi
                                    </button>
                                </form>
                                <form action="{{ route('admin.maba-verifikasi.request-correction', $maba->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Minta perbaikan data kepada mahasiswa ini?')" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-lg text-xs transition duration-150">
                                        Minta Perbaikan
                                    </button>
                                </form>
                                @elseif($maba->status_biodata === 'TERVERIFIKASI')
                                <a href="{{ route('pemeriksaan.create', ['maba_id' => $maba->id]) }}" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-lg text-xs shadow-sm transition duration-150 flex items-center gap-1">
                                    + Input Pemeriksaan
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                            Tidak ada data Maba yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($verifications->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $verifications->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
