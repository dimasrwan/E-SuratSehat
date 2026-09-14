<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Surat Sehat | Klinik UIN Ar-Raniry</title>
    <link rel="icon" type="image/png" href="{{ asset('logo-uin.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .dropdown-menu {
            transition: opacity 0.15s ease, transform 0.15s ease;
        }
    </style>
</head>
<body class="bg-slate-100/70 text-slate-800 font-sans antialiased min-h-screen flex flex-col">
    <!-- Navbar (Institutional Green Header) -->
    <header class="bg-emerald-900 text-white sticky top-0 z-40 border-b border-emerald-950/40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Left: Brand Logo & Navigation -->
                <div class="flex items-center gap-8">
                    <a href="/" class="flex items-center gap-2.5 group">
                        <img src="{{ asset('logo-uin.png') }}" alt="Logo UIN Ar-Raniry" class="w-8 h-8 object-contain p-0.5 rounded-full bg-white/90 border border-emerald-600/50 group-hover:scale-105 transition duration-150">
                        <div class="flex flex-col">
                            <span class="text-sm sm:text-base font-bold text-white tracking-tight leading-none group-hover:text-emerald-200 transition">E-Surat Sehat</span>
                            <span class="text-[10px] sm:text-[11px] font-medium text-emerald-200/90 leading-snug">Klinik UIN Ar-Raniry Banda Aceh</span>
                        </div>
                    </a>

                    <!-- Single Row Main Navigation Links (Desktop) -->
                    <nav class="hidden md:flex items-center space-x-1">
                        <!-- Dashboard -->
                        <a href="/" class="px-3 py-1.5 rounded-md text-xs font-medium transition duration-150 {{ request()->is('/') ? 'bg-emerald-950/80 text-white border-b-2 border-emerald-400 font-semibold' : 'text-emerald-100/90 hover:text-white hover:bg-emerald-800/60' }}">
                            Dashboard
                        </a>

                        <!-- Data Maba -->
                        <a href="/maba" class="px-3 py-1.5 rounded-md text-xs font-medium transition duration-150 {{ request()->is('maba') || (request()->is('maba/*') && !request()->is('maba/rekap*')) ? 'bg-emerald-950/80 text-white border-b-2 border-emerald-400 font-semibold' : 'text-emerald-100/90 hover:text-white hover:bg-emerald-800/60' }}">
                            Data Maba
                        </a>

                        <!-- Data Pemeriksaan -->
                        <a href="/pemeriksaan" class="px-3 py-1.5 rounded-md text-xs font-medium transition duration-150 {{ request()->is('pemeriksaan*') ? 'bg-emerald-950/80 text-white border-b-2 border-emerald-400 font-semibold' : 'text-emerald-100/90 hover:text-white hover:bg-emerald-800/60' }}">
                            Data Pemeriksaan
                        </a>

                        <!-- Pengiriman Surat PDF -->
                        <a href="/pengiriman" class="px-3 py-1.5 rounded-md text-xs font-medium transition duration-150 {{ request()->is('pengiriman*') ? 'bg-emerald-950/80 text-white border-b-2 border-emerald-400 font-semibold' : 'text-emerald-100/90 hover:text-white hover:bg-emerald-800/60' }}">
                            Pengiriman
                        </a>

                        <!-- Rekap Dropdown -->
                        <div class="relative nav-dropdown-wrapper">
                            <button type="button" class="nav-dropdown-trigger inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium transition duration-150 {{ request()->is('maba/rekap*') ? 'bg-emerald-950/80 text-white border-b-2 border-emerald-400 font-semibold' : 'text-emerald-100/90 hover:text-white hover:bg-emerald-800/60' }}">
                                <span>Rekap</span>
                                <svg class="w-3.5 h-3.5 text-emerald-300 transition-transform duration-150 dropdown-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="nav-dropdown-panel hidden absolute left-0 mt-2 w-52 bg-white text-slate-800 rounded-lg shadow-lg border border-slate-200 py-1 z-50 dropdown-menu">
                                <a href="{{ route('maba.rekapJadwal') }}" class="flex items-center px-4 py-2 text-xs font-medium hover:bg-emerald-50 hover:text-emerald-900 {{ request()->is('maba/rekap-jadwal') ? 'bg-emerald-50 font-bold text-emerald-900' : '' }}">
                                    Rekap Jadwal Pemeriksaan
                                </a>
                                <a href="{{ route('maba.rekapProdi') }}" class="flex items-center px-4 py-2 text-xs font-medium hover:bg-emerald-50 hover:text-emerald-900 {{ request()->is('maba/rekap-prodi') ? 'bg-emerald-50 font-bold text-emerald-900' : '' }}">
                                    Rekap Program Studi
                                </a>
                            </div>
                        </div>

                        <!-- Manajemen Dropdown (Admin Only) -->
                        @if(Auth::check() && Auth::user()->isAdmin())
                        <div class="relative nav-dropdown-wrapper">
                            <button type="button" class="nav-dropdown-trigger inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium transition duration-150 {{ request()->is('admin*') ? 'bg-emerald-950/80 text-white border-b-2 border-emerald-400 font-semibold' : 'text-emerald-100/90 hover:text-white hover:bg-emerald-800/60' }}">
                                <span>Manajemen</span>
                                <svg class="w-3.5 h-3.5 text-emerald-300 transition-transform duration-150 dropdown-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="nav-dropdown-panel hidden absolute left-0 mt-2 w-56 bg-white text-slate-800 rounded-lg shadow-lg border border-slate-200 py-1 z-50 dropdown-menu">
                                <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2 text-xs font-medium hover:bg-emerald-50 hover:text-emerald-900 {{ request()->is('admin/users*') ? 'bg-emerald-50 font-bold text-emerald-900' : '' }}">
                                    Manajemen Pengguna
                                </a>
                                <a href="{{ route('admin.tahun-maba.index') }}" class="flex items-center px-4 py-2 text-xs font-medium hover:bg-emerald-50 hover:text-emerald-900 {{ request()->is('admin/tahun-maba*') ? 'bg-emerald-50 font-bold text-emerald-900' : '' }}">
                                    Manajemen Tahun Maba
                                </a>
                                <a href="{{ route('admin.import.index') }}" class="flex items-center px-4 py-2 text-xs font-medium hover:bg-emerald-50 hover:text-emerald-900 {{ request()->is('admin/import*') ? 'bg-emerald-50 font-bold text-emerald-900' : '' }}">
                                    Import Data Biro
                                </a>
                            </div>
                        </div>
                        @endif
                    </nav>
                </div>

                <!-- Right: Active Year Pill & User Profile -->
                @auth
                <div class="flex items-center gap-3">
                    <!-- Single Active Year Pill in Navbar -->
                    <div class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-emerald-950/70 border border-emerald-700/60 text-emerald-200 rounded-full text-xs font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Tahun Aktif · <strong class="font-bold text-white">Maba {{ \App\Services\TahunMabaService::getActiveYearInt() }}</strong></span>
                    </div>

                    <!-- Compact User Account Dropdown -->
                    <div class="relative nav-dropdown-wrapper">
                        <button type="button" class="nav-dropdown-trigger flex items-center gap-2 p-1 rounded-md hover:bg-emerald-800/80 transition duration-150 text-left">
                            <div class="w-7 h-7 rounded-full bg-emerald-800 text-emerald-100 border border-emerald-700 font-bold text-xs flex items-center justify-center">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <div class="hidden lg:flex flex-col">
                                <span class="text-xs font-bold text-white leading-none">{{ Auth::user()->name }}</span>
                                <span class="text-[10px] font-medium text-emerald-200/80 leading-tight mt-0.5">{{ ucfirst(Auth::user()->role) }}</span>
                            </div>
                            <svg class="w-3.5 h-3.5 text-emerald-300 dropdown-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div class="nav-dropdown-panel hidden absolute right-0 mt-2 w-56 bg-white text-slate-800 rounded-lg shadow-lg border border-slate-200 py-1.5 z-50 dropdown-menu">
                            <div class="px-4 py-2 border-b border-slate-100 mb-1">
                                <div class="font-bold text-xs text-slate-900">{{ Auth::user()->name }}</div>
                                <div class="text-[11px] text-slate-500 truncate">{{ Auth::user()->email }}</div>
                                <div class="text-[10px] font-semibold text-slate-600 mt-1 uppercase">{{ Auth::user()->role }}</div>
                            </div>
                            
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 flex items-center gap-2 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    Keluar dari Sistem
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Mobile Menu Trigger -->
                    <button type="button" id="mobile-menu-trigger" class="md:hidden p-1.5 rounded-md text-emerald-100 hover:bg-emerald-800 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
                @endauth
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-emerald-800 bg-emerald-950 px-4 pt-3 pb-4 space-y-1.5 text-xs text-emerald-100">
            <a href="/" class="block px-3 py-1.5 rounded font-medium hover:bg-emerald-800 hover:text-white">Dashboard</a>
            <a href="/maba" class="block px-3 py-1.5 rounded font-medium hover:bg-emerald-800 hover:text-white">Data Maba</a>
            <a href="/pemeriksaan" class="block px-3 py-1.5 rounded font-medium hover:bg-emerald-800 hover:text-white">Data Pemeriksaan</a>
            <a href="/pengiriman" class="block px-3 py-1.5 rounded font-medium hover:bg-emerald-800 hover:text-white">Pengiriman Surat PDF</a>
            
            <div class="pt-2 border-t border-emerald-800/80 space-y-1">
                <span class="px-3 text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Rekap</span>
                <a href="{{ route('maba.rekapJadwal') }}" class="block px-3 py-1 text-emerald-200 hover:bg-emerald-800/60 rounded">Rekap Jadwal</a>
                <a href="{{ route('maba.rekapProdi') }}" class="block px-3 py-1 text-emerald-200 hover:bg-emerald-800/60 rounded">Rekap Prodi</a>
            </div>

            @if(Auth::check() && Auth::user()->isAdmin())
            <div class="pt-2 border-t border-emerald-800/80 space-y-1">
                <span class="px-3 text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Manajemen</span>
                <a href="{{ route('admin.users.index') }}" class="block px-3 py-1 text-emerald-200 hover:bg-emerald-800/60 rounded">Manajemen Pengguna</a>
                <a href="{{ route('admin.tahun-maba.index') }}" class="block px-3 py-1 text-emerald-200 hover:bg-emerald-800/60 rounded">Manajemen Tahun Maba</a>
                <a href="{{ route('admin.import.index') }}" class="block px-3 py-1 text-emerald-200 hover:bg-emerald-800/60 rounded">Import Data Biro</a>
            </div>
            @endif
        </div>
    </header>

    <!-- Main Workspace Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto py-5 sm:py-6 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-3.5 text-center text-xs text-slate-500 mt-auto">
        <div class="max-w-7xl mx-auto px-4">
            E-Surat Sehat &copy; {{ date('Y') }} Klinik UIN Ar-Raniry Banda Aceh. Hak Cipta Dilindungi.
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropdownWrappers = document.querySelectorAll('.nav-dropdown-wrapper');
            
            dropdownWrappers.forEach(wrapper => {
                const trigger = wrapper.querySelector('.nav-dropdown-trigger');
                const panel = wrapper.querySelector('.nav-dropdown-panel');
                const chevron = wrapper.querySelector('.dropdown-chevron');

                if (trigger && panel) {
                    trigger.addEventListener('click', function (e) {
                        e.stopPropagation();
                        dropdownWrappers.forEach(w => {
                            if (w !== wrapper) {
                                const p = w.querySelector('.nav-dropdown-panel');
                                const c = w.querySelector('.dropdown-chevron');
                                if (p) p.classList.add('hidden');
                                if (c) c.classList.remove('rotate-180');
                            }
                        });

                        const isHidden = panel.classList.contains('hidden');
                        if (isHidden) {
                            panel.classList.remove('hidden');
                            if (chevron) chevron.classList.add('rotate-180');
                        } else {
                            panel.classList.add('hidden');
                            if (chevron) chevron.classList.remove('rotate-180');
                        }
                    });
                }
            });

            document.addEventListener('click', function () {
                dropdownWrappers.forEach(w => {
                    const p = w.querySelector('.nav-dropdown-panel');
                    const c = w.querySelector('.dropdown-chevron');
                    if (p) p.classList.add('hidden');
                    if (c) c.classList.remove('rotate-180');
                });
            });

            const mobileTrigger = document.getElementById('mobile-menu-trigger');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileTrigger && mobileMenu) {
                mobileTrigger.addEventListener('click', function () {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>
