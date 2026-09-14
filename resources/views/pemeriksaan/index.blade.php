@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <!-- Header & Action Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 py-1">
        <div>
            @php
                $cleanYear = preg_replace('/[^0-9]/', '', (string)($selectedTahun ?? 'all'));
            @endphp
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                @if(($selectedTahun ?? 'all') === 'all' || empty($cleanYear))
                    Pusat Operasional Pemeriksaan Medis
                @else
                    Operasional Pemeriksaan Maba {{ $cleanYear }}
                @endif
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">
                Pusat antrean pemeriksaan fisik maba, entri hasil dokter, dan verifikasi penerbitan Surat Sehat.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('pemeriksaan.exportExcel', request()->all()) }}" class="h-[40px] px-3.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-medium text-xs rounded-lg shadow-2xs transition duration-150 flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </a>
            <a href="{{ route('pemeriksaan.exportPdf', request()->all()) }}" class="h-[40px] px-3.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-medium text-xs rounded-lg shadow-2xs transition duration-150 flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export PDF
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200/80 rounded-lg text-xs font-medium text-emerald-800 flex items-center gap-2.5">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div class="p-3.5 bg-rose-50 border border-rose-200/80 rounded-lg text-xs font-medium text-rose-800 flex items-center gap-2.5">
            <svg class="h-4 w-4 text-rose-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Category Tabs (Segmented Control Layout) -->
    <div class="inline-flex p-1 bg-slate-100/80 rounded-lg border border-slate-200/80 gap-1">
        <a href="{{ route('pemeriksaan.index', array_merge(request()->query(), ['status_antrean' => 'belum_diperiksa'])) }}" 
           class="h-[36px] px-3.5 rounded-md font-semibold text-xs flex items-center gap-2 transition duration-150 {{ ($status_antrean ?? 'belum_diperiksa') === 'belum_diperiksa' ? 'bg-white text-emerald-800 shadow-2xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}">
            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Belum Diperiksa
            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ ($status_antrean ?? 'belum_diperiksa') === 'belum_diperiksa' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                {{ $countBelumDiperiksa }}
            </span>
        </a>
        <a href="{{ route('pemeriksaan.index', array_merge(request()->query(), ['status_antrean' => 'selesai'])) }}" 
           class="h-[36px] px-3.5 rounded-md font-semibold text-xs flex items-center gap-2 transition duration-150 {{ ($status_antrean ?? 'belum_diperiksa') === 'selesai' ? 'bg-white text-emerald-800 shadow-2xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}">
            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Pemeriksaan Selesai
            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ ($status_antrean ?? 'belum_diperiksa') === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                {{ $countSudahDiperiksa }}
            </span>
        </a>
    </div>

    <!-- Filter Toolbar Container -->
    <div class="bg-white rounded-lg border border-slate-200/80 shadow-2xs p-4 sm:p-5">
        <form action="{{ route('pemeriksaan.index') }}" method="GET" class="space-y-3.5">
            <input type="hidden" name="status_antrean" value="{{ $status_antrean ?? 'belum_diperiksa' }}">
            
            <!-- Row 1: Dropdown Filters -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                <!-- Year Filter -->
                <div>
                    <label for="tahun_masuk" class="block text-[10px] font-medium text-slate-500 uppercase tracking-wider mb-1">Tahun Maba</label>
                    @php
                        $yearOptions = ['all' => 'Semua Tahun'];
                        foreach ($availableYears as $yr) {
                            $yearOptions[(string)$yr] = 'Maba ' . $yr;
                        }
                    @endphp
                    <x-form-select name="tahun_masuk" id="tahun_masuk" :value="$selectedTahun ?? (string)$activeYear" placeholder="Pilih Tahun" :options="$yearOptions" onchange="this.closest('form').submit()" />
                </div>

                <!-- Date Filter -->
                <div>
                    <label for="tanggal" class="block text-[10px] font-medium text-slate-500 uppercase tracking-wider mb-1">Tanggal Jadwal/Pemeriksa</label>
                    <x-form-datepicker name="tanggal" id="tanggal" :value="$tanggal ?? ''" onchange="this.closest('form').submit()" />
                </div>

                <!-- Session Filter -->
                <div>
                    <label for="sesi" class="block text-[10px] font-medium text-slate-500 uppercase tracking-wider mb-1">Sesi</label>
                    @php
                        $sesiOptions = ['' => 'Semua Sesi'];
                        foreach ($availableSesi as $s) {
                            $sesiOptions[(string)$s] = 'Sesi ' . $s;
                        }
                    @endphp
                    <x-form-select name="sesi" id="sesi" :value="$sesi ?? ''" placeholder="Semua Sesi" :options="$sesiOptions" onchange="this.closest('form').submit()" />
                </div>

                <!-- Prodi Filter -->
                <div>
                    <label for="prodi" class="block text-[10px] font-medium text-slate-500 uppercase tracking-wider mb-1">Program Studi</label>
                    @php
                        $prodiOptions = ['' => 'Semua Program Studi'];
                        foreach ($availableProdis as $p) {
                            $prodiOptions[$p] = $p;
                        }
                    @endphp
                    <x-form-select name="prodi" id="prodi" :value="$prodi ?? ''" placeholder="Semua Program Studi" :options="$prodiOptions" onchange="this.closest('form').submit()" />
                </div>
            </div>

            <!-- Row 2: Search, Status Email & Action Controls -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3.5">
                <div class="flex-1">
                    <label for="search" class="block text-[10px] font-medium text-slate-500 uppercase tracking-wider mb-1">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" value="{{ $search ?? '' }}" placeholder="Cari Nama, NIK, Email, atau Nomor Surat..." class="w-full h-[42px] pl-9 pr-3 bg-white border border-[#D9E1E7] rounded-lg text-[13px] text-slate-800 font-medium focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15 transition duration-150">
                    </div>
                </div>

                @if(($status_antrean ?? 'belum_diperiksa') === 'selesai')
                    <div class="w-full sm:w-[230px] flex-shrink-0">
                        <label for="status_email" class="block text-[10px] font-medium text-slate-500 uppercase tracking-wider mb-1">Status Email</label>
                        <x-form-select name="status_email" id="status_email" :value="$status_email ?? ''" placeholder="Status Email" :options="[
                            '' => 'Semua Email',
                            'Belum dikirim' => 'Belum dikirim',
                            'Dalam antrean' => 'Dalam antrean',
                            'Mengirim' => 'Mengirim',
                            'Terkirim' => 'Terkirim',
                            'Gagal' => 'Gagal'
                        ]" onchange="this.closest('form').submit()" />
                    </div>
                @endif

                <div class="flex items-center gap-2 flex-shrink-0">
                    <button type="submit" class="w-full sm:w-[100px] h-[42px] px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-medium text-xs rounded-md shadow-none transition duration-150 flex items-center justify-center">
                        Cari
                    </button>
                    <a href="{{ route('pemeriksaan.index', ['status_antrean' => $status_antrean, 'tahun_masuk' => $selectedTahun, 'clear_filter' => 1]) }}" class="w-full sm:w-[75px] h-[42px] px-3 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-medium text-xs rounded-md shadow-none transition duration-150 flex items-center justify-center">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Outer Table Card Container -->
    <div class="bg-white rounded-lg border border-slate-200/80 shadow-2xs overflow-hidden">
        <!-- TAB CONTENT 1: BELUM DIPERIKSA QUEUE -->
        @if(($status_antrean ?? 'belum_diperiksa') === 'belum_diperiksa')
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[10px] font-semibold tracking-wider">
                        <tr>
                            <th class="py-2.5 px-4">Nama Maba</th>
                            <th class="py-2.5 px-4">Program Studi / Fakultas</th>
                            <th class="py-2.5 px-4">Jadwal / Sesi</th>
                            <th class="py-2.5 px-4">Status Biodata</th>
                            <th class="py-2.5 px-4 text-center">Aksi Operasional</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($unexaminedQueue as $maba)
                            <tr class="hover:bg-slate-50/60 transition duration-150">
                                <td class="py-3 px-4 text-xs">
                                    <div class="font-bold text-slate-900">{{ $maba->nama_lengkap ?? $maba->nama_biro }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">NIK: {{ $maba->nik ?? '-' }} | Email: {{ $maba->email ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <div class="font-semibold text-slate-800">{{ $maba->program_studi_biro ?? $maba->program_studi ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $maba->fakultas ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <div class="text-xs font-semibold text-slate-800">
                                        {{ $maba->tanggal_jadwal ? $maba->tanggal_jadwal->format('d/m/Y') : '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        Sesi {{ $maba->sesi_jadwal ?? '-' }} {{ $maba->waktu_jadwal ? '('.$maba->waktu_jadwal.')' : '' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        ✓ TERVERIFIKASI
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs text-center">
                                    <a href="{{ route('pemeriksaan.create', ['maba_id' => $maba->id]) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-medium text-xs rounded-md shadow-2xs transition duration-150">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Mulai Pemeriksaan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-9 text-center text-xs text-slate-400 font-normal">
                                    Tidak ada maba terverifikasi yang menunggu pemeriksaan pada filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($unexaminedQueue && $unexaminedQueue->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
                    {{ $unexaminedQueue->links() }}
                </div>
            @endif

        <!-- TAB CONTENT 2: PEMERIKSAAN SELESAI -->
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[10px] font-semibold tracking-wider">
                        <tr>
                            <th class="py-2.5 px-4">No. Surat / Nama</th>
                            <th class="py-2.5 px-4">Program Studi / Fakultas</th>
                            <th class="py-2.5 px-4">Kesimpulan Medis</th>
                            <th class="py-2.5 px-4">Status Email</th>
                            <th class="py-2.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pemeriksaans as $pemeriksaan)
                            <tr class="hover:bg-slate-50/60 transition duration-150">
                                <td class="py-3 px-4 text-xs">
                                    <div class="font-mono text-[11px] font-semibold text-slate-700 bg-slate-100 inline-block px-1.5 py-0.5 rounded border border-slate-200 mb-0.5">
                                        {{ $pemeriksaan->nomor_surat ?? 'Belum terbit' }}
                                    </div>
                                    <div class="font-bold text-slate-900">{{ $pemeriksaan->nama }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">NIK: {{ $pemeriksaan->nik ?? '-' }} | Email: {{ $pemeriksaan->email ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <div class="font-semibold text-slate-800">
                                        {{ $pemeriksaan->mabaData ? ($pemeriksaan->mabaData->program_studi_biro ?? $pemeriksaan->mabaData->program_studi) : ($pemeriksaan->fakultas ?? '-') }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $pemeriksaan->fakultas ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/60">
                                        {{ $pemeriksaan->kesimpulan ?? 'SEHAT' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    @if($pemeriksaan->status_pengiriman === 'Terkirim')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            ✓ Terkirim
                                        </span>
                                    @elseif($pemeriksaan->status_pengiriman === 'Gagal')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">
                                            ⚠ Gagal
                                        </span>
                                    @elseif(in_array($pemeriksaan->status_pengiriman, ['Dalam antrean', 'Mengirim']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200/60">
                                            {{ $pemeriksaan->status_pengiriman }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                                            Belum dikirim
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('pemeriksaan.show', $pemeriksaan->id) }}" class="h-7 px-2.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-medium text-[11px] rounded transition duration-150 inline-flex items-center justify-center">
                                            Detail
                                        </a>
                                        <a href="{{ route('pemeriksaan.download', $pemeriksaan->id) }}" target="_blank" class="h-7 px-2.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-medium text-[11px] rounded transition duration-150 inline-flex items-center justify-center">
                                            Cetak PDF
                                        </a>

                                        <!-- Email Action Button -->
                                        @if($pemeriksaan->status_pengiriman === 'Terkirim' || $pemeriksaan->status_pengiriman === 'Gagal')
                                            <form action="{{ route('pemeriksaan.sendEmail', $pemeriksaan->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="h-7 px-2.5 bg-white hover:bg-slate-50 border border-amber-300 text-amber-800 font-medium text-[11px] rounded transition duration-150 inline-flex items-center justify-center">
                                                    Kirim Ulang
                                                </button>
                                            </form>
                                        @elseif(!in_array($pemeriksaan->status_pengiriman, ['Dalam antrean', 'Mengirim']))
                                            <form action="{{ route('pemeriksaan.sendEmail', $pemeriksaan->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="h-7 px-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-medium text-[11px] rounded transition duration-150 inline-flex items-center justify-center">
                                                    Kirim
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-9 text-center text-xs text-slate-400 font-normal">
                                    Belum ada data pemeriksaan selesai yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pemeriksaans && $pemeriksaans->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
                    {{ $pemeriksaans->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
