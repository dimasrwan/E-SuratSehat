<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Surat Sehat | Klinik UIN Ar-Raniry Banda Aceh</title>
    <link rel="icon" type="image/png" href="{{ asset('logo-uin.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-slate-800 font-sans antialiased selection:bg-emerald-600 selection:text-white">

    <!-- 1. NAVBAR -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-slate-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Brand / Logo -->
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('logo-uin.png') }}" alt="Logo UIN Ar-Raniry" class="w-8 h-8 object-contain">
                    <div>
                        <div class="text-base font-extrabold text-slate-900 tracking-tight leading-none">
                            E-Surat Sehat
                        </div>
                        <div class="text-[10px] text-slate-500 font-medium leading-tight mt-0.5">
                            Klinik UIN Ar-Raniry
                        </div>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-8 text-xs font-semibold text-slate-600">
                    <a href="#beranda" class="hover:text-emerald-700 transition-colors">Beranda</a>
                    <a href="{{ route('portal.index') }}" class="hover:text-emerald-700 transition-colors">Portal Maba</a>
                    <a href="#tentang" class="hover:text-emerald-700 transition-colors">Tentang</a>
                    <a href="#fitur" class="hover:text-emerald-700 transition-colors">Fitur</a>
                    <a href="#alur" class="hover:text-emerald-700 transition-colors">Alur Pelayanan</a>
                </nav>

                <!-- Action Button -->
                <div class="hidden md:flex items-center gap-3">
                    <a href="{{ route('portal.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs rounded-lg border border-emerald-200 transition-all">
                        <span>Isi Data Maba</span>
                        <span>&rarr;</span>
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-800 hover:bg-emerald-900 text-white font-semibold text-xs rounded-lg transition-all">
                            <span>Buka Dashboard</span>
                            <span class="text-xs">&rarr;</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-lg transition-all">
                            <span>Masuk ke Sistem</span>
                            <span class="text-xs">&rarr;</span>
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Toggle Button -->
                <div class="flex md:hidden">
                    <button id="mobile-menu-btn" type="button" class="p-2 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-50 focus:outline-none" aria-label="Toggle Navigation">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-b border-slate-100 bg-white px-4 pt-2 pb-4 space-y-2 text-sm font-semibold">
            <a href="#beranda" class="block px-3 py-2 rounded-md text-slate-700 hover:bg-slate-50">Beranda</a>
            <a href="{{ route('portal.index') }}" class="block px-3 py-2 rounded-md text-emerald-800 font-bold hover:bg-emerald-50">Portal Maba</a>
            <a href="#tentang" class="block px-3 py-2 rounded-md text-slate-700 hover:bg-slate-50">Tentang</a>
            <a href="#fitur" class="block px-3 py-2 rounded-md text-slate-700 hover:bg-slate-50">Fitur</a>
            <a href="#alur" class="block px-3 py-2 rounded-md text-slate-700 hover:bg-slate-50">Alur Pelayanan</a>
            <div class="pt-2 space-y-2">
                <a href="{{ route('portal.index') }}" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-700 text-white font-bold rounded-lg text-xs">
                    Isi Data Maba &rarr;
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 text-white font-bold rounded-lg text-xs">
                        Buka Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-800 font-bold rounded-lg text-xs border border-slate-200">
                        Masuk ke Sistem &rarr;
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- 2. HERO SECTION -->
    <section id="beranda" class="pt-16 pb-20 md:pt-24 md:pb-28 bg-white border-b border-slate-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Left Column: Copy & Actions -->
                <div class="lg:col-span-7 space-y-6">
                    <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-widest block">
                        SISTEM PELAYANAN DIGITAL KLINIK
                    </span>

                    <div class="space-y-2">
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                            E-Surat Sehat
                        </h1>
                        <p class="text-lg sm:text-xl font-bold text-emerald-800 tracking-tight">
                            Sistem Digital Surat Keterangan Sehat
                        </p>
                    </div>

                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-xl">
                        Platform internal Klinik UIN Ar-Raniry Banda Aceh untuk mengelola pemeriksaan kesehatan mahasiswa dan penerbitan Surat Keterangan Sehat secara terintegrasi.
                    </p>

                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <a href="{{ route('portal.index') }}" class="inline-flex items-center justify-center gap-2 h-11 px-6 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs sm:text-sm rounded-lg transition-colors shadow-sm shadow-emerald-700/20">
                            <span>Isi Data Maba</span>
                            <span>&rarr;</span>
                        </a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 h-11 px-5 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs sm:text-sm rounded-lg transition-colors">
                                <span>Buka Dashboard</span>
                                <span>&rarr;</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 h-11 px-5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs sm:text-sm rounded-lg border border-slate-200 transition-colors">
                                <span>Masuk ke Sistem</span>
                                <span>&rarr;</span>
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Right Column: Minimal Supporting Visual (Clean Document Preview Card) -->
                <div class="lg:col-span-5 flex justify-center lg:justify-end">
                    <div class="w-full max-w-sm bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                            <img src="{{ asset('logo-uin.png') }}" alt="UIN Logo" class="w-8 h-8 object-contain">
                            <div>
                                <div class="text-xs font-bold text-slate-900">Klinik UIN Ar-Raniry</div>
                                <div class="text-[10px] text-slate-500">Banda Aceh</div>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-100 space-y-2 text-xs">
                            <div class="text-center pb-1.5 border-b border-slate-200">
                                <div class="font-bold text-slate-800 text-[11px] uppercase">Surat Keterangan Sehat</div>
                                <div class="text-[10px] text-slate-500 font-mono">0172/Un.08/PPKES/09/2026</div>
                            </div>
                            <div class="flex justify-between pt-1">
                                <span class="text-slate-500">Nama:</span>
                                <span class="font-semibold text-slate-800">Ahmad Mahasiswa</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Status:</span>
                                <span class="font-bold text-emerald-700">SEHAT FISIK & MENTAL</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                            <span class="flex items-center gap-1 text-emerald-800 font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                Verifikasi Digital PDF
                            </span>
                            <span>Email Queued</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION KHUSUS MAHASISWA BARU -->
    <section class="py-12 bg-emerald-50/70 border-b border-emerald-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl border border-emerald-200/80 p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-2xs">
                <div class="space-y-2 max-w-2xl">
                    <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-widest block">
                        DATA MAHASISWA BARU
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        Belum mengisi data diri?
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Lengkapi data pribadi Anda secara online sebelum proses pemeriksaan kesehatan di Klinik UIN Ar-Raniry Banda Aceh.
                    </p>
                </div>
                <div class="flex-shrink-0 w-full md:w-auto">
                    <a href="{{ route('portal.index') }}" class="inline-flex items-center justify-center gap-2 w-full md:w-auto px-6 py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs sm:text-sm rounded-lg transition-colors shadow-2xs">
                        <span>Isi Data Maba</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. TENTANG & FITUR UTAMA -->
    <section id="tentang" class="py-20 bg-slate-50/60 border-b border-slate-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Minimal About Header -->
            <div id="fitur" class="max-w-2xl mx-auto text-center space-y-2 mb-14">
                <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-widest block">
                    TENTANG & FITUR UTAMA
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Administrasi kesehatan mahasiswa yang lebih terstruktur
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed pt-1">
                    E-Surat Sehat dikembangkan untuk membantu petugas Klinik UIN Ar-Raniry Banda Aceh dalam mengelola hasil pemeriksaan kesehatan mahasiswa, menerbitkan Surat Keterangan Sehat, serta mendukung pengiriman dokumen secara digital.
                </p>
            </div>

            <!-- ONLY 3 Main Features -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Feature 1 -->
                <div class="bg-white p-6 rounded-xl border border-slate-200/80 shadow-none hover:border-emerald-300 transition-colors space-y-3">
                    <div class="text-xs font-mono font-bold text-emerald-700">01</div>
                    <h3 class="text-base font-bold text-slate-900">Pemeriksaan Kesehatan</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pengelolaan hasil pemeriksaan kesehatan fisik dan medis mahasiswa secara terstruktur.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-6 rounded-xl border border-slate-200/80 shadow-none hover:border-emerald-300 transition-colors space-y-3">
                    <div class="text-xs font-mono font-bold text-emerald-700">02</div>
                    <h3 class="text-base font-bold text-slate-900">Surat Keterangan Sehat</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Penerbitan Surat Keterangan Sehat otomatis dalam format PDF resmi dengan nomor surat unik.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-6 rounded-xl border border-slate-200/80 shadow-none hover:border-emerald-300 transition-colors space-y-3">
                    <div class="text-xs font-mono font-bold text-emerald-700">03</div>
                    <h3 class="text-base font-bold text-slate-900">Pengiriman Digital</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pengiriman dokumen ke email mahasiswa melalui sistem antrean yang cepat dan aman.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. ALUR PELAYANAN -->
    <section id="alur" class="py-20 bg-white border-b border-slate-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-xl mx-auto mb-14 space-y-2">
                <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-widest block">ALUR PELAYANAN</span>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Dari pemeriksaan hingga surat diterima mahasiswa
                </h2>
            </div>

            <!-- Horizontal Step Timeline -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                <!-- Step 1 -->
                <div class="space-y-2 text-left">
                    <div class="text-xs font-mono font-extrabold text-emerald-700">01</div>
                    <h3 class="text-sm font-bold text-slate-900">Pemeriksaan</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">Data hasil pemeriksaan dimasukkan ke dalam sistem.</p>
                </div>

                <!-- Step 2 -->
                <div class="space-y-2 text-left">
                    <div class="text-xs font-mono font-extrabold text-emerald-700">02</div>
                    <h3 class="text-sm font-bold text-slate-900">Verifikasi</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">Data diperiksa dan disimpan secara terstruktur.</p>
                </div>

                <!-- Step 3 -->
                <div class="space-y-2 text-left">
                    <div class="text-xs font-mono font-extrabold text-emerald-700">03</div>
                    <h3 class="text-sm font-bold text-slate-900">Surat Sehat</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">Sistem menghasilkan dokumen Surat Keterangan Sehat.</p>
                </div>

                <!-- Step 4 -->
                <div class="space-y-2 text-left">
                    <div class="text-xs font-mono font-extrabold text-emerald-700">04</div>
                    <h3 class="text-sm font-bold text-slate-900">Pengiriman</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">Dokumen dikirim ke alamat email mahasiswa.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. CTA SECTION -->
    <section class="py-14 bg-slate-900 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Siap menggunakan E-Surat Sehat?</h2>
            <p class="text-slate-300 text-xs sm:text-sm max-w-md mx-auto leading-relaxed">
                Masuk ke sistem untuk mengelola pemeriksaan dan Surat Keterangan Sehat mahasiswa.
            </p>
            <div class="pt-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 h-10 px-6 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-lg transition-colors">
                        <span>Masuk ke Dashboard</span>
                        <span>&rarr;</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 h-10 px-6 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-lg transition-colors">
                        <span>Masuk ke Sistem &rarr;</span>
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- 6. FOOTER -->
    <footer class="bg-slate-950 text-slate-400 text-xs border-t border-slate-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 pb-6 border-b border-slate-800">
                <!-- Left: Logo & Brand -->
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('logo-uin.png') }}" alt="Logo UIN" class="w-7 h-7 object-contain">
                    <div>
                        <div class="font-bold text-white text-sm">E-Surat Sehat</div>
                        <div class="text-[11px] text-slate-400">Klinik UIN Ar-Raniry Banda Aceh</div>
                    </div>
                </div>

                <!-- Right: Simple Links -->
                <div class="flex flex-wrap items-center gap-6 text-slate-400 font-medium">
                    <a href="#beranda" class="hover:text-emerald-400 transition-colors">Beranda</a>
                    <a href="#fitur" class="hover:text-emerald-400 transition-colors">Fitur</a>
                    <a href="#alur" class="hover:text-emerald-400 transition-colors">Alur Pelayanan</a>
                    <a href="{{ route('login') }}" class="text-white hover:text-emerald-400 font-semibold transition-colors">Masuk ke Sistem</a>
                </div>
            </div>

            <!-- Bottom: Copyright -->
            <div class="pt-6 text-center text-slate-500">
                <p>&copy; 2026 Klinik UIN Ar-Raniry Banda Aceh. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            if (btn && menu) {
                btn.addEventListener('click', function() {
                    menu.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>
