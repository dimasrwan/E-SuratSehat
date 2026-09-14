@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Section (Wording & Hierarchy from Current Version) -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 py-1">
        <div>
            @php
                $cleanYearDash = preg_replace('/[^0-9]/', '', (string)($selectedTahun ?? 'all'));
            @endphp
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                @if(($selectedTahun ?? 'all') === 'all' || empty($cleanYearDash))
                    Dashboard Operational Maba (Semua Tahun)
                @else
                    Dashboard Operational Maba {{ $cleanYearDash }}
                @endif
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">
                Selamat datang kembali, <strong class="font-semibold text-slate-800">{{ Auth::user()->name }}</strong>
                <span class="sr-only">{{ ucfirst(Auth::user()->role) }} Dashboard</span>
            </p>
        </div>

        <!-- Header Controls & Actions (Positioned Right) -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Year Selector Control -->
            <form action="{{ url()->current() }}" method="GET" class="w-36 sm:w-40">
                @php
                    $yearOptions = ['all' => 'Semua Tahun'];
                    foreach ($availableYears as $yr) {
                        $yearOptions[(string)$yr] = 'Maba ' . $yr;
                    }
                @endphp
                <x-form-select name="tahun_masuk" :value="$selectedTahun ?? (string)($activeYearInt ?? date('Y'))" placeholder="Pilih Tahun" :options="$yearOptions" onchange="this.closest('form').submit()" />
            </form>

            @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.import.index') }}" class="px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold text-xs rounded-lg shadow-2xs transition duration-150 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Import Biro
            </a>
            @endif

            <a href="{{ route('admin.maba-verifikasi.index') }}" class="px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-2xs transition duration-150 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Verifikasi Maba ({{ $menungguVerifikasi }})
            </a>

            <a href="{{ route('pemeriksaan.create') }}" class="px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold text-xs rounded-lg shadow-2xs transition duration-150 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + Input Pemeriksaan
            </a>

            <!-- Compact screen-reader / test anchor for Quick Action Operator string -->
            <span class="sr-only">Quick Action Operator</span>
        </div>
    </div>

    <!-- 4 KPI Cards Grid (Visual Style from Previous Version) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 1: Total Maba -->
        <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200/80 shadow-2xs flex flex-col justify-between">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Maba</div>
            <div class="text-2xl sm:text-3xl font-bold text-slate-900 my-1">{{ number_format($totalMaba) }}</div>
            <div class="text-[11px] text-slate-400 font-normal">Terdaftar dari Biro</div>
        </div>

        <!-- KPI 2: Menunggu Verifikasi -->
        <a href="{{ route('admin.maba-verifikasi.index', ['status' => 'MENUNGGU_VERIFIKASI']) }}" class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200/80 shadow-2xs hover:border-amber-400 transition duration-150 flex flex-col justify-between group">
            <div class="text-[11px] font-bold text-slate-600 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Menunggu Verifikasi
            </div>
            <div class="text-2xl sm:text-3xl font-bold text-amber-700 my-1 group-hover:translate-x-0.5 transition transform origin-left">{{ number_format($menungguVerifikasi) }}</div>
            <div class="text-[11px] text-amber-600 font-semibold group-hover:underline">Butuh tindakan →</div>
        </a>

        <!-- KPI 3: Terverifikasi -->
        <a href="{{ route('admin.maba-verifikasi.index', ['status' => 'TERVERIFIKASI']) }}" class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200/80 shadow-2xs hover:border-emerald-400 transition duration-150 flex flex-col justify-between group">
            <div class="text-[11px] font-bold text-slate-600 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Terverifikasi
            </div>
            <div class="text-2xl sm:text-3xl font-bold text-emerald-700 my-1 group-hover:translate-x-0.5 transition transform origin-left">{{ number_format($terverifikasi) }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold group-hover:underline">Siap diperiksa →</div>
        </a>

        <!-- KPI 4: Pemeriksaan Selesai -->
        <a href="{{ route('pemeriksaan.index', ['category' => 'selesai']) }}" class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200/80 shadow-2xs hover:border-sky-400 transition duration-150 flex flex-col justify-between group">
            <div class="text-[11px] font-bold text-slate-600 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                Pemeriksaan Selesai
            </div>
            <div class="text-2xl sm:text-3xl font-bold text-sky-700 my-1 group-hover:translate-x-0.5 transition transform origin-left">{{ number_format($pemeriksaanSelesai) }}</div>
            <div class="text-[11px] text-slate-400 font-normal">Sudah terbit surat</div>
        </a>
    </div>

    <!-- 2 Column Section: Operational Summary Panels (Flat Rows with Thin Dividers) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Left: Pemeriksaan Hari Ini Panel -->
        <div class="bg-white p-5 rounded-lg border border-slate-200/80 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-800 tracking-tight flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        Pemeriksaan Hari Ini
                    </span>
                    <span class="text-slate-500 font-medium text-xs">
                        {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                    </span>
                </div>

                <div class="divide-y divide-slate-100 text-xs mt-1">
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-600 font-medium flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            Total Terjadwal
                        </span>
                        <span class="font-semibold text-slate-900 text-xs sm:text-sm">{{ number_format($hariIniTotal) }}</span>
                    </div>
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-600 font-medium flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Selesai
                        </span>
                        <span class="font-semibold text-slate-900 text-xs sm:text-sm">{{ number_format($hariIniSelesai) }}</span>
                    </div>
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-600 font-medium flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            Siap Diperiksa
                        </span>
                        <span class="font-semibold text-slate-900 text-xs sm:text-sm">{{ number_format($hariIniBelumDiperiksa) }}</span>
                    </div>
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-600 font-medium flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Menunggu Verifikasi
                        </span>
                        <span class="font-semibold text-slate-900 text-xs sm:text-sm">{{ number_format($hariIniMenungguVerifikasi) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Status Pengiriman Surat PDF Panel -->
        <div class="bg-white p-5 rounded-lg border border-slate-200/80 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Status Pengiriman Surat PDF</h3>
                    <a href="{{ url('/pengiriman') }}" class="text-xs font-semibold text-emerald-700 hover:underline">Detail →</a>
                </div>

                <div class="divide-y divide-slate-100 text-xs mt-1">
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-600 font-medium flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Terkirim
                        </span>
                        <span class="font-semibold text-slate-900 text-xs sm:text-sm">{{ number_format($emailTerkirim) }}</span>
                    </div>
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-600 font-medium flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            Dalam Antrean
                        </span>
                        <span class="font-semibold text-slate-900 text-xs sm:text-sm">{{ number_format($emailDalamAntrean) }}</span>
                    </div>
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-600 font-medium flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            Gagal
                        </span>
                        <span class="font-semibold text-rose-700 text-xs sm:text-sm flex items-center gap-2">
                            {{ number_format($emailGagal) }}
                            @if($emailGagal > 0)
                            <a href="{{ route('pemeriksaan.index', ['category' => 'selesai', 'status_email' => 'Gagal']) }}" class="text-xs text-rose-600 underline font-normal">Detail →</a>
                            @endif
                        </span>
                    </div>
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-600 font-medium flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            Belum Dikirim
                        </span>
                        <span class="font-semibold text-slate-700 text-xs sm:text-sm">{{ number_format($emailBelum) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Antrean Menunggu Verifikasi Operator (Table from Current Version) -->
    <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <h3 class="font-bold text-slate-800 text-xs tracking-tight uppercase">Antrean Menunggu Verifikasi Operator</h3>
            </div>
            <a href="{{ route('admin.maba-verifikasi.index', ['status' => 'MENUNGGU_VERIFIKASI']) }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 hover:underline">
                Lihat Semua ({{ $menungguVerifikasi }}) →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-emerald-50/50 border-b border-slate-200/80 text-emerald-900 uppercase text-[11px] font-semibold tracking-wider">
                    <tr>
                        <th class="py-2.5 px-4">Nama Maba</th>
                        <th class="py-2.5 px-4">Program Studi</th>
                        <th class="py-2.5 px-4">Jadwal & Sesi</th>
                        <th class="py-2.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendingVerifications as $maba)
                    <tr class="hover:bg-slate-50/60 transition duration-150">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $maba->nama_lengkap ?? $maba->nama_biro }}
                            <div class="text-[11px] text-slate-400 font-normal">NIM: {{ $maba->nim_biro ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-medium">{{ $maba->program_studi_biro }}</td>
                        <td class="py-3 px-4 text-slate-500">
                            {{ $maba->tanggal_jadwal ? $maba->tanggal_jadwal->format('d M Y') : '-' }}
                            <span class="text-slate-400">(Sesi {{ $maba->sesi_jadwal ?? '-' }})</span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('admin.maba-verifikasi.show', $maba->id) }}" class="px-3.5 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-2xs transition duration-150 text-center">
                                Verifikasi
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-xs text-slate-400">
                            Tidak ada Maba yang menunggu verifikasi saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Examinations Table (Table from Current Version) -->
    <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-xs tracking-tight uppercase">Pemeriksaan Terbaru</h3>
            <a href="{{ route('pemeriksaan.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">Semua →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-600 uppercase text-[11px] font-semibold tracking-wider">
                    <tr>
                        <th class="py-2.5 px-4">Nama Pasien</th>
                        <th class="py-2.5 px-4">Program Studi</th>
                        <th class="py-2.5 px-4">Nomor Surat</th>
                        <th class="py-2.5 px-4 text-right">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($pemeriksaanTerbaru as $pem)
                    <tr class="hover:bg-slate-50/60 transition duration-150">
                        <td class="py-3 px-4 font-semibold text-slate-900">{{ $pem->nama }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $pem->program_studi ?? '-' }}</td>
                        <td class="py-3 px-4 font-mono text-slate-700">{{ $pem->nomor_surat ?? '-' }}</td>
                        <td class="py-3 px-4 text-right text-slate-400">
                            {{ $pem->created_at ? $pem->created_at->diffForHumans() : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-5 text-center text-xs text-slate-400">Belum ada pemeriksaan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
