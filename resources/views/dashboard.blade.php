@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Top Bar Header & Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-5 sm:p-6 rounded-xl border border-slate-200/80 shadow-2xs">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    @php
                        $cleanYearDash = preg_replace('/[^0-9]/', '', (string)($selectedTahun ?? 'all'));
                    @endphp
                    @if(($selectedTahun ?? 'all') === 'all' || empty($cleanYearDash))
                        Dashboard Monitoring (Semua Tahun)
                    @else
                        Dashboard Operational Maba {{ $cleanYearDash }}
                    @endif
                </h1>
                @if(isset($activeYearModel) && $activeYearModel)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-semibold rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Tahun Aktif: {{ $activeYearModel->tahun }}
                </span>
                @endif
            </div>
            <p class="text-xs sm:text-sm text-slate-500 font-normal">
                Selamat datang kembali, <strong class="font-semibold text-slate-800">{{ Auth::user()->name }}</strong> 
                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 font-semibold text-[10px] rounded uppercase tracking-wider ml-1 border border-slate-200">{{ Auth::user()->role }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <form action="{{ url()->current() }}" method="GET" class="w-40 sm:w-48">
                @php
                    $yearOptions = ['all' => 'Semua Tahun'];
                    foreach ($availableYears as $yr) {
                        $yearOptions[(string)$yr] = 'Maba ' . $yr;
                    }
                @endphp
                <x-form-select name="tahun_masuk" :value="$selectedTahun ?? (string)($activeYearInt ?? date('Y'))" placeholder="Pilih Tahun Maba" :options="$yearOptions" onchange="this.closest('form').submit()" />
            </form>

            @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.import.index') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-lg shadow-2xs transition duration-150 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
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
        </div>
    </div>

    <!-- Maba Operasional KPI Cards (Clean Restrained Color System) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <!-- Card 1: Total Maba -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs relative overflow-hidden">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Maba</div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($totalMaba) }}</div>
            <div class="text-[11px] text-slate-400 font-normal mt-1">Terdaftar dari Biro</div>
        </div>

        <!-- Card 2: Belum Mengisi -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs relative overflow-hidden">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Belum Mengisi</div>
            <div class="text-2xl font-bold text-slate-600">{{ number_format($belumMengisi) }}</div>
            <div class="text-[11px] text-slate-400 font-normal mt-1">Belum isi biodata</div>
        </div>

        <!-- Card 3: Menunggu Verifikasi -->
        <a href="{{ route('admin.maba-verifikasi.index', ['status' => 'MENUNGGU_VERIFIKASI']) }}" class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs hover:border-amber-400 transition duration-150 block group">
            <div class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider mb-1 flex items-center justify-between">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Menunggu Verifikasi
                </span>
            </div>
            <div class="text-2xl font-bold text-amber-700 group-hover:translate-x-0.5 transition transform origin-left">{{ number_format($menungguVerifikasi) }}</div>
            <div class="text-[11px] text-amber-600 font-semibold mt-1 group-hover:underline">Butuh tindakan →</div>
        </a>

        <!-- Card 4: Perlu Perbaikan -->
        <a href="{{ route('admin.maba-verifikasi.index', ['status' => 'PERLU_PERBAIKAN']) }}" class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs hover:border-rose-400 transition duration-150 block group">
            <div class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                Perlu Perbaikan
            </div>
            <div class="text-2xl font-bold text-rose-700 group-hover:translate-x-0.5 transition transform origin-left">{{ number_format($perluPerbaikan) }}</div>
            <div class="text-[11px] text-rose-600 font-semibold mt-1 group-hover:underline">Revisi Maba →</div>
        </a>

        <!-- Card 5: Terverifikasi -->
        <a href="{{ route('admin.maba-verifikasi.index', ['status' => 'TERVERIFIKASI']) }}" class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs hover:border-emerald-400 transition duration-150 block group">
            <div class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Terverifikasi
            </div>
            <div class="text-2xl font-bold text-emerald-700 group-hover:translate-x-0.5 transition transform origin-left">{{ number_format($terverifikasi) }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1 group-hover:underline">Siap diperiksa →</div>
        </a>

        <!-- Card 6: Pemeriksaan Selesai -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs relative overflow-hidden">
            <div class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                Pemeriksaan Selesai
            </div>
            <div class="text-2xl font-bold text-sky-700">{{ number_format($pemeriksaanSelesai) }}</div>
            <div class="text-[11px] text-slate-400 font-normal mt-1">Sudah terbit surat</div>
        </div>
    </div>

    <!-- Light Quick Action Bar for Operator -->
    <div class="bg-slate-100/70 p-3.5 rounded-xl border border-slate-200/80 flex flex-col md:flex-row items-center justify-between gap-3">
        <div class="flex items-center space-x-2 pb-1">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span class="font-semibold text-xs text-slate-700 tracking-wide uppercase">Aksi Cepat (Quick Action Operator):</span>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <a href="{{ route('pemeriksaan.index') }}" class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-medium rounded-lg border border-slate-200 shadow-2xs transition flex items-center gap-1.5 hover:border-slate-300">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Cari Maba
            </a>
            <a href="{{ route('admin.maba-verifikasi.index', ['status' => 'MENUNGGU_VERIFIKASI']) }}" class="px-3 py-1.5 bg-white hover:bg-amber-50 text-slate-700 hover:text-amber-900 font-medium rounded-lg border border-slate-200 hover:border-amber-300 shadow-2xs transition flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Menunggu Verifikasi ({{ $menungguVerifikasi }})
            </a>
            <a href="{{ route('pemeriksaan.index', ['category' => 'belum_diperiksa']) }}" class="px-3 py-1.5 bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-900 font-medium rounded-lg border border-slate-200 hover:border-emerald-300 shadow-2xs transition flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Maba Siap Diperiksa ({{ $terverifikasi }})
            </a>
            <a href="{{ route('pemeriksaan.index', ['category' => 'selesai']) }}" class="px-3 py-1.5 bg-white hover:bg-sky-50 text-slate-700 hover:text-sky-900 font-medium rounded-lg border border-slate-200 hover:border-sky-300 shadow-2xs transition flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                Pemeriksaan Selesai ({{ $pemeriksaanSelesai }})
            </a>
            @if($emailGagal > 0)
            <a href="{{ route('pemeriksaan.index', ['category' => 'selesai', 'status_email' => 'Gagal']) }}" class="px-3 py-1.5 bg-white hover:bg-rose-50 text-rose-700 font-medium rounded-lg border border-rose-200 hover:border-rose-300 shadow-2xs transition flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                Email Gagal ({{ $emailGagal }})
            </a>
            @endif
        </div>
    </div>

    <!-- Ringkasan Pemeriksaan Hari Ini & Progress Bar Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Hari Ini Breakdown Card -->
        <div class="bg-white p-5 rounded-xl shadow-2xs border border-slate-200/80 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-800 tracking-tight flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        Pemeriksaan Hari Ini
                    </span>
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 font-medium text-[11px] rounded border border-slate-200">
                        {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-2.5 text-xs">
                    <div class="bg-slate-50/80 p-3 rounded-lg border border-slate-200/60">
                        <div class="text-slate-500 font-medium flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            Total Terjadwal
                        </div>
                        <div class="text-xl font-bold text-slate-800 mt-1">{{ number_format($hariIniTotal) }}</div>
                    </div>
                    <div class="bg-slate-50/80 p-3 rounded-lg border border-slate-200/60">
                        <div class="text-emerald-700 font-medium flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Selesai
                        </div>
                        <div class="text-xl font-bold text-emerald-800 mt-1">{{ number_format($hariIniSelesai) }}</div>
                    </div>
                    <div class="bg-slate-50/80 p-3 rounded-lg border border-slate-200/60">
                        <div class="text-sky-700 font-medium flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            Siap Diperiksa
                        </div>
                        <div class="text-lg font-bold text-sky-800 mt-1">{{ number_format($hariIniBelumDiperiksa) }}</div>
                    </div>
                    <div class="bg-slate-50/80 p-3 rounded-lg border border-slate-200/60">
                        <div class="text-amber-700 font-medium flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Verifikasi
                        </div>
                        <div class="text-lg font-bold text-amber-800 mt-1">{{ number_format($hariIniMenungguVerifikasi) }}</div>
                    </div>
                </div>
            </div>

            @if($hariIniPerluPerhatian > 0)
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-rose-700 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    Perlu Perhatian (Revisi/Belum):
                </span>
                <span class="font-bold text-rose-700">{{ number_format($hariIniPerluPerhatian) }} Maba</span>
            </div>
            @endif
        </div>

        <!-- Overall Progress Section -->
        <div class="lg:col-span-2 bg-white p-5 rounded-xl shadow-2xs border border-slate-200/80 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                        <h3 class="font-bold text-slate-800 text-sm">Progres Pemeriksaan Medis Maba (Tahun {{ $selectedTahun === 'all' ? 'Semua' : ($targetYearInt ?? $activeYearInt) }})</h3>
                    </div>
                    <span class="text-xs font-bold text-emerald-800">{{ number_format($pemeriksaanSelesai) }} dari {{ number_format($totalMaba) }} Maba ({{ $progressPercentage }}%)</span>
                </div>
                
                <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200/60 mb-5">
                    <div class="h-full bg-emerald-600 rounded-full transition-all duration-500" style="width: {{ min(100, max(0, $progressPercentage)) }}%"></div>
                </div>

                <div class="grid grid-cols-3 gap-3 text-center text-xs">
                    <div class="p-2.5 bg-slate-50/80 rounded-lg border border-slate-200/60">
                        <div class="text-slate-500 font-medium">Total Maba</div>
                        <div class="text-base font-bold text-slate-800 mt-0.5">{{ number_format($totalMaba) }}</div>
                    </div>
                    <div class="p-2.5 bg-slate-50/80 rounded-lg border border-slate-200/60">
                        <div class="text-emerald-800 font-medium">Siap Diperiksa</div>
                        <div class="text-base font-bold text-emerald-700 mt-0.5">{{ number_format($terverifikasi) }}</div>
                    </div>
                    <div class="p-2.5 bg-slate-50/80 rounded-lg border border-slate-200/60">
                        <div class="text-sky-800 font-medium">Selesai Diperiksa</div>
                        <div class="text-base font-bold text-sky-700 mt-0.5">{{ number_format($pemeriksaanSelesai) }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-3 text-[11px] text-slate-400 text-right font-normal">
                Pembaruan otomatis real-time dari database.
            </div>
        </div>
    </div>

    <!-- 2 Column Layout: Operational Queues & Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Operational Queues -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Pending Verification Queue -->
            <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <h3 class="font-bold text-slate-800 text-xs tracking-tight">Antrean Menunggu Verifikasi Operator</h3>
                    </div>
                    <a href="{{ route('admin.maba-verifikasi.index', ['status' => 'MENUNGGU_VERIFIKASI']) }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 hover:underline">
                        Lihat Semua ({{ $menungguVerifikasi }}) →
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($pendingVerifications as $maba)
                    <div class="p-4 hover:bg-slate-50/60 transition duration-150 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div class="space-y-0.5">
                            <div class="font-bold text-slate-900 text-sm">{{ $maba->nama_lengkap ?? $maba->nama_biro }}</div>
                            <div class="font-medium text-emerald-700">{{ $maba->program_studi_biro }}</div>
                            <div class="text-[11px] text-slate-400">Jadwal: {{ $maba->tanggal_jadwal ? $maba->tanggal_jadwal->format('d M Y') : '-' }} (Sesi {{ $maba->sesi_jadwal ?? '-' }})</div>
                        </div>

                        <a href="{{ route('admin.maba-verifikasi.show', $maba->id) }}" class="px-3.5 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-2xs transition duration-150 text-center">
                            Lihat & Verifikasi
                        </a>
                    </div>
                    @empty
                    <div class="p-8 text-center text-xs text-slate-400">
                        Tidak ada Maba yang menunggu verifikasi saat ini.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Program Studi & Jadwal Breakdown -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Prodi Breakdown -->
                <div class="bg-white p-5 rounded-xl shadow-2xs border border-slate-200/80">
                    <h4 class="font-bold text-slate-800 text-xs mb-3.5 flex items-center justify-between border-b border-slate-100 pb-2">
                        <span>Distribusi Program Studi</span>
                        <span class="text-[10px] text-slate-400 font-normal">Top 6</span>
                    </h4>
                    <div class="space-y-3 text-xs">
                        @forelse($prodiDistribution as $prodi)
                        <div>
                            <div class="flex justify-between font-semibold text-slate-700 mb-1">
                                <span class="truncate max-w-[170px]">{{ $prodi->program_studi_biro }}</span>
                                <span class="text-emerald-700">{{ number_format($prodi->total) }}</span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-emerald-600 h-full rounded-full" style="width: {{ $totalMaba > 0 ? min(100, round(($prodi->total / $totalMaba) * 100)) : 0 }}%"></div>
                            </div>
                        </div>
                        @empty
                        <div class="text-slate-400 text-center py-4">Belum ada data program studi.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Jadwal Breakdown -->
                <div class="bg-white p-5 rounded-xl shadow-2xs border border-slate-200/80">
                    <h4 class="font-bold text-slate-800 text-xs mb-3.5 flex items-center justify-between border-b border-slate-100 pb-2">
                        <span>Jadwal Pemeriksaan</span>
                        <span class="text-[10px] text-slate-400 font-normal">Terdekat</span>
                    </h4>
                    <div class="space-y-2.5 text-xs">
                        @forelse($jadwalBreakdown as $jadwal)
                        <div class="flex items-center justify-between p-2.5 bg-slate-50/80 rounded-lg border border-slate-200/60">
                            <div>
                                <div class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($jadwal->tanggal_jadwal)->locale('id')->translatedFormat('D, d M Y') }}</div>
                                <div class="text-[10px] text-slate-400">Total: {{ $jadwal->total }} Peserta</div>
                            </div>
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 font-semibold text-[10px] rounded border border-emerald-200/60">
                                {{ $jadwal->selesai }} Selesai
                            </span>
                        </div>
                        @empty
                        <div class="text-slate-400 text-center py-4">Belum ada jadwal pemeriksaan.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Col: Status Pengiriman Email & Recent Examinations -->
        <div class="space-y-6">
            <!-- Email Status Card (Restrained Status Dots) -->
            <div class="bg-white p-5 rounded-xl shadow-2xs border border-slate-200/80 space-y-3.5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="font-bold text-slate-800 text-xs">Status Pengiriman Surat PDF</h3>
                    <a href="{{ url('/pengiriman') }}" class="text-xs font-semibold text-emerald-700 hover:underline">Detail →</a>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2.5 bg-slate-50/80 rounded-lg border border-slate-200/60">
                        <span class="text-slate-700 font-medium flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Terkirim
                        </span>
                        <span class="font-bold text-emerald-800">{{ number_format($emailTerkirim) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 bg-slate-50/80 rounded-lg border border-slate-200/60">
                        <span class="text-slate-700 font-medium flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                            Dalam Antrean
                        </span>
                        <span class="font-bold text-sky-800">{{ number_format($emailDalamAntrean) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 bg-slate-50/80 rounded-lg border border-slate-200/60">
                        <span class="text-slate-700 font-medium flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            Gagal
                        </span>
                        <span class="font-bold text-rose-800">{{ number_format($emailGagal) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 bg-slate-50/80 rounded-lg border border-slate-200/60">
                        <span class="text-slate-700 font-medium flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            Belum Dikirim
                        </span>
                        <span class="font-bold text-slate-700">{{ number_format($emailBelum) }}</span>
                    </div>
                </div>
            </div>

            <!-- Recent Examinations Table -->
            <div class="bg-white rounded-xl shadow-2xs border border-slate-200/80 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-xs">Pemeriksaan Terbaru</h3>
                    <a href="{{ route('pemeriksaan.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">Semua →</a>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($pemeriksaanTerbaru as $pem)
                    <div class="p-3.5 hover:bg-slate-50/60 transition duration-150">
                        <div class="font-semibold text-slate-900">{{ $pem->nama }}</div>
                        <div class="text-[11px] text-slate-400 flex items-center justify-between mt-0.5">
                            <span class="font-mono text-slate-600">{{ $pem->nomor_surat ?? '-' }}</span>
                            <span>{{ $pem->created_at ? $pem->created_at->diffForHumans() : '-' }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-xs text-slate-400">Belum ada pemeriksaan.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

