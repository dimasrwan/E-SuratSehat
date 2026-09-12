@extends('layouts.app')

@section('content')
<div>
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 tracking-tight">
                @php
                    $cleanYear = preg_replace('/[^0-9]/', '', (string)($selectedTahun ?? 'all'));
                @endphp
                @if(($selectedTahun ?? 'all') === 'all' || empty($cleanYear))
                    Pusat Operasional Pemeriksaan Medis
                @else
                    Operasional Pemeriksaan Maba {{ $cleanYear }}
                @endif
            </h2>
            <p class="text-xs text-slate-500 mt-1">Pusat antrean pemeriksaan fisik maba, entri hasil dokter, dan verifikasi penerbitan Surat Sehat.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('pemeriksaan.exportExcel', request()->all()) }}" class="inline-flex items-center px-4 py-2 bg-white border border-emerald-600 rounded-lg font-semibold text-xs text-emerald-700 tracking-wide shadow-sm hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Excel
            </a>
            <a href="{{ route('pemeriksaan.exportPdf', request()->all()) }}" class="inline-flex items-center px-4 py-2 bg-white border border-amber-600 rounded-lg font-semibold text-xs text-amber-700 tracking-wide shadow-sm hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export PDF
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-md shadow-sm" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-md shadow-sm" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Category Tabs -->
    <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200 pb-2">
        <a href="{{ route('pemeriksaan.index', array_merge(request()->query(), ['status_antrean' => 'belum_diperiksa'])) }}" 
           class="inline-flex items-center px-4 py-2.5 rounded-t-lg font-bold text-sm transition {{ ($status_antrean ?? 'belum_diperiksa') === 'belum_diperiksa' ? 'bg-amber-500 text-white border-b-2 border-amber-600 shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Belum Diperiksa
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ ($status_antrean ?? 'belum_diperiksa') === 'belum_diperiksa' ? 'bg-amber-700 text-white' : 'bg-gray-200 text-gray-800' }}">
                {{ $countBelumDiperiksa }}
            </span>
        </a>
        <a href="{{ route('pemeriksaan.index', array_merge(request()->query(), ['status_antrean' => 'selesai'])) }}" 
           class="inline-flex items-center px-4 py-2.5 rounded-t-lg font-bold text-sm transition {{ ($status_antrean ?? 'belum_diperiksa') === 'selesai' ? 'bg-emerald-600 text-white border-b-2 border-emerald-700 shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Pemeriksaan Selesai
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ ($status_antrean ?? 'belum_diperiksa') === 'selesai' ? 'bg-emerald-800 text-white' : 'bg-gray-200 text-gray-800' }}">
                {{ $countSudahDiperiksa }}
            </span>
        </a>
    </div>

    <!-- Container -->
    <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-100">
        <div class="p-6 bg-white">
            <!-- Filter Bar -->
            <form action="{{ route('pemeriksaan.index') }}" method="GET" class="mb-6">
                <input type="hidden" name="status_antrean" value="{{ $status_antrean ?? 'belum_diperiksa' }}">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <!-- Year Filter -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tahun Maba</label>
                        @php
                            $yearOptions = ['all' => 'Semua Tahun'];
                            foreach ($availableYears as $yr) {
                                $yearOptions[(string)$yr] = 'Maba ' . $yr;
                            }
                        @endphp
                        <x-form-select name="tahun_masuk" :value="$selectedTahun ?? (string)$activeYear" placeholder="Pilih Tahun" :options="$yearOptions" onchange="this.closest('form').submit()" />
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Jadwal/Periksa</label>
                        <input type="date" name="tanggal" value="{{ $tanggal ?? '' }}" onchange="this.closest('form').submit()" class="border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200 rounded-lg shadow-sm block w-full py-2 px-3 text-sm">
                    </div>

                    <!-- Session Filter -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Sesi</label>
                        @php
                            $sesiOptions = ['' => 'Semua Sesi'];
                            foreach ($availableSesi as $s) {
                                $sesiOptions[(string)$s] = 'Sesi ' . $s;
                            }
                        @endphp
                        <x-form-select name="sesi" :value="$sesi ?? ''" placeholder="Semua Sesi" :options="$sesiOptions" onchange="this.closest('form').submit()" />
                    </div>

                    <!-- Prodi Filter -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Program Studi</label>
                        @php
                            $prodiOptions = ['' => 'Semua Program Studi'];
                            foreach ($availableProdis as $p) {
                                $prodiOptions[$p] = $p;
                            }
                        @endphp
                        <x-form-select name="prodi" :value="$prodi ?? ''" placeholder="Semua Prodi" :options="$prodiOptions" onchange="this.closest('form').submit()" />
                    </div>
                </div>

                <!-- Search & Email Status Row -->
                <div class="flex flex-col md:flex-row gap-3">
                    <div class="relative flex-grow">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari Nama, NIK, Email, atau Nomor Surat..." class="pl-9 border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200 rounded-lg shadow-sm block w-full py-2 text-sm">
                    </div>

                    @if(($status_antrean ?? 'belum_diperiksa') === 'selesai')
                        <div class="w-full md:w-48 flex-shrink-0">
                            <x-form-select name="status_email" :value="$status_email ?? ''" placeholder="Status Email" :options="[
                                '' => 'Semua Email',
                                'Belum dikirim' => 'Belum dikirim',
                                'Dalam antrean' => 'Dalam antrean',
                                'Mengirim' => 'Mengirim',
                                'Terkirim' => 'Terkirim',
                                'Gagal' => 'Gagal'
                            ]" onchange="this.closest('form').submit()" />
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-lg font-semibold text-xs text-white tracking-wide shadow-sm hover:bg-gray-700 transition">
                            Cari
                        </button>
                        <a href="{{ route('pemeriksaan.index', ['status_antrean' => $status_antrean, 'tahun_masuk' => $selectedTahun, 'clear_filter' => 1]) }}" class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg font-semibold text-xs text-gray-600 hover:bg-gray-200 transition" title="Reset Filter">
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            <!-- TAB CONTENT 1: BELUM DIPERIKSA QUEUE -->
            @if(($status_antrean ?? 'belum_diperiksa') === 'belum_diperiksa')
                <div class="overflow-x-auto rounded-lg border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-amber-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-amber-900 uppercase">Nama Maba</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-amber-900 uppercase">Program Studi / Fakultas</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-amber-900 uppercase">Jadwal / Sesi</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-amber-900 uppercase">Status Biodata</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-amber-900 uppercase">Aksi Operasional</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($unexaminedQueue as $maba)
                                <tr class="hover:bg-amber-50/50 transition">
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-bold text-gray-900">{{ $maba->nama_lengkap ?? $maba->nama_biro }}</div>
                                        <div class="text-xs text-gray-500">NIK: {{ $maba->nik ?? '-' }} | Email: {{ $maba->email ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-semibold text-gray-800">{{ $maba->program_studi_biro ?? $maba->program_studi ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $maba->fakultas ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="text-xs font-bold text-gray-700">
                                            {{ $maba->tanggal_jadwal ? $maba->tanggal_jadwal->format('d/m/Y') : '-' }}
                                        </div>
                                        <div class="text-[11px] text-gray-500">
                                            Sesi {{ $maba->sesi_jadwal ?? '-' }} {{ $maba->waktu_jadwal ? '('.$maba->waktu_jadwal.')' : '' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-emerald-100 text-emerald-800">
                                            ✓ TERVERIFIKASI
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <a href="{{ route('pemeriksaan.create', ['maba_id' => $maba->id]) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-amber-500 border border-transparent rounded-lg font-bold text-xs text-white tracking-wide shadow-sm hover:bg-amber-600 focus:outline-none transition">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Mulai Pemeriksaan
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-sm text-center text-gray-500 italic">
                                        Tidak ada maba terverifikasi yang menunggu pemeriksaan pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($unexaminedQueue)
                    <div class="mt-4">
                        {{ $unexaminedQueue->links() }}
                    </div>
                @endif

            <!-- TAB CONTENT 2: PEMERIKSAAN SELESAI -->
            @else
                <div class="overflow-x-auto rounded-lg border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-emerald-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-emerald-900 uppercase">No. Surat / Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-emerald-900 uppercase">Program Studi / Fakultas</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-emerald-900 uppercase">Kesimpulan Medis</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-emerald-900 uppercase">Status Email</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-emerald-900 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($pemeriksaans as $pemeriksaan)
                                <tr class="hover:bg-emerald-50/40 transition">
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-mono text-xs font-bold text-emerald-800 bg-emerald-50 inline-block px-2 py-0.5 rounded border border-emerald-200 mb-0.5">
                                            {{ $pemeriksaan->nomor_surat ?? 'Belum terbit' }}
                                        </div>
                                        <div class="font-bold text-gray-900">{{ $pemeriksaan->nama }}</div>
                                        <div class="text-xs text-gray-500">NIK: {{ $pemeriksaan->nik ?? '-' }} | Email: {{ $pemeriksaan->email ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-semibold text-gray-800">
                                            {{ $pemeriksaan->mabaData ? ($pemeriksaan->mabaData->program_studi_biro ?? $pemeriksaan->mabaData->program_studi) : ($pemeriksaan->fakultas ?? '-') }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $pemeriksaan->fakultas ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-emerald-100 text-emerald-800">
                                            {{ $pemeriksaan->kesimpulan ?? 'SEHAT' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($pemeriksaan->status_pengiriman === 'Terkirim')
                                            <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                                                ✓ Sudah Terkirim
                                            </span>
                                        @elseif($pemeriksaan->status_pengiriman === 'Gagal')
                                            <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full bg-rose-100 text-rose-800">
                                                ⚠ Gagal
                                            </span>
                                        @elseif(in_array($pemeriksaan->status_pengiriman, ['Dalam antrean', 'Mengirim']))
                                            <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full bg-sky-100 text-sky-800">
                                                {{ $pemeriksaan->status_pengiriman }}
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full bg-gray-100 text-gray-700">
                                                Belum dikirim
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('pemeriksaan.show', $pemeriksaan->id) }}" class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded transition">
                                                Detail
                                            </a>
                                            <a href="{{ route('pemeriksaan.download', $pemeriksaan->id) }}" target="_blank" class="px-2.5 py-1 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded transition">
                                                Cetak PDF
                                            </a>

                                            <!-- Email Action Button -->
                                            @if($pemeriksaan->status_pengiriman === 'Terkirim' || $pemeriksaan->status_pengiriman === 'Gagal')
                                                <form action="{{ route('pemeriksaan.sendEmail', $pemeriksaan->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-sky-700 bg-sky-50 hover:bg-sky-100 border border-sky-200 rounded transition">
                                                        Kirim Ulang
                                                    </button>
                                                </form>
                                            @elseif(!in_array($pemeriksaan->status_pengiriman, ['Dalam antrean', 'Mengirim']))
                                                <form action="{{ route('pemeriksaan.sendEmail', $pemeriksaan->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded transition">
                                                        Kirim
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-sm text-center text-gray-500 italic">
                                        Belum ada data pemeriksaan selesai yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($pemeriksaans)
                    <div class="mt-4">
                        {{ $pemeriksaans->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
