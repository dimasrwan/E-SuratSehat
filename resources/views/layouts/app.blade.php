<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Surat Sehat | Klinik UIN Ar-Raniry</title>
    <link rel="icon" type="image/png" href="{{ asset('logo-uin.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Smooth transition for dropdowns */
        .dropdown-menu {
            transition: opacity 0.15s ease, transform 0.15s ease;
        }
    </style>
</head>
<body class="bg-slate-50/70 text-slate-800 font-sans antialiased min-h-screen flex flex-col">
    <!-- Navbar -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 sm:h-18 items-center">
                <!-- Left: Logo & Brand -->
                <div class="flex items-center gap-8">
                    <a href="/" class="flex items-center gap-3 group">
                        <img src="{{ asset('logo-uin.png') }}" alt="Logo UIN Ar-Raniry" class="w-10 h-10 object-contain p-0.5 rounded-full bg-slate-50 border border-slate-200/60 group-hover:border-emerald-500 transition duration-150">
                        <div class="flex flex-col">
                            <span class="text-base font-bold text-slate-900 tracking-tight leading-snug group-hover:text-emerald-700 transition">E-Surat Sehat</span>
                            <span class="text-[11px] font-medium text-slate-500 leading-none">Klinik UIN Ar-Raniry Banda Aceh</span>
                        </div>
                    </a>

                    <!-- Middle Navigation Links (Desktop) -->
                    <nav class="hidden md:flex items-center space-x-1">
                        <!-- Dashboard -->
                        <a href="/" class="px-3.5 py-2 rounded-lg text-sm font-medium transition duration-150 {{ request()->is('/') ? 'text-emerald-700 bg-emerald-50/80 font-semibold border-b-2 border-emerald-600 rounded-b-none' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            Dashboard
                        </a>

                        <!-- Data Maba -->
                        <a href="/maba" class="px-3.5 py-2 rounded-lg text-sm font-medium transition duration-150 {{ request()->is('maba') || (request()->is('maba/*') && !request()->is('maba/rekap*')) ? 'text-emerald-700 bg-emerald-50/80 font-semibold border-b-2 border-emerald-600 rounded-b-none' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            Data Maba
                        </a>

                        <!-- Data Pemeriksaan -->
                        <a href="/pemeriksaan" class="px-3.5 py-2 rounded-lg text-sm font-medium transition duration-150 {{ request()->is('pemeriksaan*') ? 'text-emerald-700 bg-emerald-50/80 font-semibold border-b-2 border-emerald-600 rounded-b-none' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                            Pemeriksaan
                        </a>

                        <!-- Laporan Dropdown -->
                        <div class="relative nav-dropdown-wrapper">
                            <button type="button" class="nav-dropdown-trigger inline-flex items-center gap-1 px-3.5 py-2 rounded-lg text-sm font-medium transition duration-150 {{ request()->is('pengiriman*') || request()->is('maba/rekap*') ? 'text-emerald-700 bg-emerald-50/80 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                                <span>Laporan</span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-150 dropdown-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="nav-dropdown-panel hidden absolute left-0 mt-1.5 w-52 bg-white rounded-xl shadow-lg border border-slate-200/90 py-1.5 z-50 dropdown-menu">
                                <a href="/pengiriman" class="flex items-center px-4 py-2 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->is('pengiriman*') ? 'bg-emerald-50 font-bold text-emerald-800' : '' }}">
                                    Pengiriman Surat PDF
                                </a>
                                <a href="{{ route('maba.rekapJadwal') }}" class="flex items-center px-4 py-2 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->is('maba/rekap-jadwal') ? 'bg-emerald-50 font-bold text-emerald-800' : '' }}">
                                    Rekap Jadwal Pemeriksaan
                                </a>
                                <a href="{{ route('maba.rekapProdi') }}" class="flex items-center px-4 py-2 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->is('maba/rekap-prodi') ? 'bg-emerald-50 font-bold text-emerald-800' : '' }}">
                                    Rekap Program Studi
                                </a>
                            </div>
                        </div>

                        <!-- Pengaturan Dropdown (Admin Only) -->
                        @if(Auth::check() && Auth::user()->isAdmin())
                        <div class="relative nav-dropdown-wrapper">
                            <button type="button" class="nav-dropdown-trigger inline-flex items-center gap-1 px-3.5 py-2 rounded-lg text-sm font-medium transition duration-150 {{ request()->is('admin*') ? 'text-emerald-700 bg-emerald-50/80 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                                <span>Pengaturan</span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-150 dropdown-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="nav-dropdown-panel hidden absolute left-0 mt-1.5 w-56 bg-white rounded-xl shadow-lg border border-slate-200/90 py-1.5 z-50 dropdown-menu">
                                <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->is('admin/users*') ? 'bg-emerald-50 font-bold text-emerald-800' : '' }}">
                                    Manajemen Pengguna
                                </a>
                                <a href="{{ route('admin.tahun-maba.index') }}" class="flex items-center px-4 py-2 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->is('admin/tahun-maba*') ? 'bg-emerald-50 font-bold text-emerald-800' : '' }}">
                                    Manajemen Tahun Maba
                                </a>
                                <a href="{{ route('admin.import.index') }}" class="flex items-center px-4 py-2 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 {{ request()->is('admin/import*') ? 'bg-emerald-50 font-bold text-emerald-800' : '' }}">
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
                    <!-- Subtle Active Year Pill -->
                    <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50/80 border border-emerald-200/80 text-emerald-800 rounded-full text-xs font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Tahun Aktif · <strong class="font-bold">Maba {{ \App\Services\TahunMabaService::getActiveYearInt() }}</strong></span>
                    </div>

                    <!-- User Account Dropdown -->
                    <div class="relative nav-dropdown-wrapper">
                        <button type="button" class="nav-dropdown-trigger flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-slate-100/80 transition duration-150 border border-transparent hover:border-slate-200">
                            <div class="w-8 h-8 rounded-full bg-emerald-800 text-white font-extrabold text-xs flex items-center justify-center shadow-xs">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <div class="hidden lg:flex flex-col text-left">
                                <span class="text-xs font-bold text-slate-900 leading-tight">{{ Auth::user()->name }}</span>
                                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">{{ Auth::user()->role }}</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 dropdown-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div class="nav-dropdown-panel hidden absolute right-0 mt-1.5 w-56 bg-white rounded-xl shadow-lg border border-slate-200/90 py-2 z-50 dropdown-menu">
                            <div class="px-4 py-2 border-b border-slate-100 mb-1">
                                <div class="font-bold text-xs text-slate-900">{{ Auth::user()->name }}</div>
                                <div class="text-[11px] text-slate-500 truncate">{{ Auth::user()->email }}</div>
                                <span class="inline-block mt-1 px-2 py-0.5 bg-slate-100 text-slate-700 text-[10px] font-bold rounded uppercase">
                                    {{ Auth::user()->role }}
                                </span>
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

                    <!-- Mobile Hamburger Button -->
                    <button type="button" id="mobile-menu-trigger" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
                @endauth
            </div>
        </div>

        <!-- Mobile Menu Container -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-4 space-y-2 text-sm">
            <a href="/" class="block px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-800">Dashboard</a>
            <a href="/maba" class="block px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-800">Data Maba</a>
            <a href="/pemeriksaan" class="block px-3 py-2 rounded-lg font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-800">Data Pemeriksaan</a>
            
            <div class="pt-2 border-t border-slate-100">
                <span class="px-3 text-xs font-bold text-slate-400 uppercase">Laporan</span>
                <a href="/pengiriman" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">Pengiriman Surat PDF</a>
                <a href="{{ route('maba.rekapJadwal') }}" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">Rekap Jadwal</a>
                <a href="{{ route('maba.rekapProdi') }}" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">Rekap Prodi</a>
            </div>

            @if(Auth::check() && Auth::user()->isAdmin())
            <div class="pt-2 border-t border-slate-100">
                <span class="px-3 text-xs font-bold text-slate-400 uppercase">Pengaturan (Admin)</span>
                <a href="{{ route('admin.users.index') }}" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">Manajemen Pengguna</a>
                <a href="{{ route('admin.tahun-maba.index') }}" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">Manajemen Tahun Maba</a>
                <a href="{{ route('admin.import.index') }}" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">Import Data Biro</a>
            </div>
            @endif
        </div>
    </header>

    <!-- Page Content -->
    <main class="flex-grow max-w-7xl w-full mx-auto py-6 sm:py-8 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 py-4 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            E-Surat Sehat &copy; {{ date('Y') }} Klinik UIN Ar-Raniry Banda Aceh. Hak Cipta Dilindungi.
        </div>
    </footer>

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // General Nav Dropdowns Toggle
            const dropdownWrappers = document.querySelectorAll('.nav-dropdown-wrapper');
            
            dropdownWrappers.forEach(wrapper => {
                const trigger = wrapper.querySelector('.nav-dropdown-trigger');
                const panel = wrapper.querySelector('.nav-dropdown-panel');
                const chevron = wrapper.querySelector('.dropdown-chevron');

                if (trigger && panel) {
                    trigger.addEventListener('click', function (e) {
                        e.stopPropagation();
                        // Close other open panels
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

            // Close nav dropdowns on outside click
            document.addEventListener('click', function () {
                dropdownWrappers.forEach(w => {
                    const p = w.querySelector('.nav-dropdown-panel');
                    const c = w.querySelector('.dropdown-chevron');
                    if (p) p.classList.add('hidden');
                    if (c) c.classList.remove('rotate-180');
                });
            });

            // Mobile menu toggle
            const mobileTrigger = document.getElementById('mobile-menu-trigger');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileTrigger && mobileMenu) {
                mobileTrigger.addEventListener('click', function () {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Custom Select Handler (For Form Controls)
            let activeOpenWrapper = null;

            function closeDropdown(wrapper) {
                if (!wrapper) return;
                const trigger = wrapper.querySelector('.custom-select-trigger');
                const panel = wrapper.querySelector('.custom-select-panel');
                const arrow = wrapper.querySelector('.custom-select-arrow');

                if (panel) {
                    panel.classList.add('hidden', 'opacity-0', '-translate-y-1', 'scale-[0.99]');
                    panel.classList.remove('opacity-100', 'translate-y-0', 'scale-100');
                }
                if (trigger) {
                    trigger.setAttribute('aria-expanded', 'false');
                    trigger.classList.remove('border-emerald-500', 'ring-2', 'ring-emerald-500/20');
                }
                if (arrow) {
                    arrow.classList.remove('rotate-180');
                }
                if (activeOpenWrapper === wrapper) {
                    activeOpenWrapper = null;
                }
            }

            function openDropdown(wrapper) {
                if (!wrapper || wrapper.getAttribute('data-disabled') === 'true') return;

                // Close any currently open dropdown
                if (activeOpenWrapper && activeOpenWrapper !== wrapper) {
                    closeDropdown(activeOpenWrapper);
                }

                const trigger = wrapper.querySelector('.custom-select-trigger');
                const panel = wrapper.querySelector('.custom-select-panel');
                const arrow = wrapper.querySelector('.custom-select-arrow');

                if (panel) {
                    panel.classList.remove('hidden');
                    // Force reflow for transition animation
                    void panel.offsetWidth;
                    panel.classList.remove('opacity-0', '-translate-y-1', 'scale-[0.99]');
                    panel.classList.add('opacity-100', 'translate-y-0', 'scale-100');
                }
                if (trigger) {
                    trigger.setAttribute('aria-expanded', 'true');
                    trigger.classList.add('border-emerald-500', 'ring-2', 'ring-emerald-500/20');
                }
                if (arrow) {
                    arrow.classList.add('rotate-180');
                }
                activeOpenWrapper = wrapper;

                // Scroll selected option into view inside panel
                const selectedOpt = panel.querySelector('.custom-select-option[aria-selected="true"]');
                if (selectedOpt) {
                    selectedOpt.scrollIntoView({ block: 'nearest' });
                }
            }

            function selectOption(wrapper, optionEl) {
                const hiddenInput = wrapper.querySelector('input[type="hidden"]');
                const labelSpan = wrapper.querySelector('.custom-select-label');
                const options = wrapper.querySelectorAll('.custom-select-option');

                const newVal = optionEl.getAttribute('data-value') || '';
                const newLabel = optionEl.getAttribute('data-label') || '';

                if (hiddenInput) {
                    const oldVal = hiddenInput.value;
                    hiddenInput.value = newVal;
                    if (oldVal !== newVal) {
                        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }

                if (labelSpan) {
                    labelSpan.textContent = newLabel;
                }

                // Update aria-selected and visual checkmarks
                options.forEach(opt => {
                    const isTarget = opt === optionEl;
                    opt.setAttribute('aria-selected', isTarget ? 'true' : 'false');
                    const check = opt.querySelector('.custom-select-check');
                    if (isTarget) {
                        opt.classList.add('bg-emerald-50', 'text-emerald-900', 'font-semibold');
                        opt.classList.remove('text-slate-700');
                        if (check) check.classList.remove('hidden');
                    } else {
                        opt.classList.remove('bg-emerald-50', 'text-emerald-900', 'font-semibold');
                        opt.classList.add('text-slate-700');
                        if (check) check.classList.add('hidden');
                    }
                });

                closeDropdown(wrapper);
            }

            // Click Handler
            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('.custom-select-trigger');
                const option = e.target.closest('.custom-select-option');

                if (trigger) {
                    const wrapper = trigger.closest('.custom-select-wrapper');
                    const isExpanded = trigger.getAttribute('aria-expanded') === 'true';
                    if (isExpanded) {
                        closeDropdown(wrapper);
                    } else {
                        openDropdown(wrapper);
                    }
                    return;
                }

                if (option) {
                    const wrapper = option.closest('.custom-select-wrapper');
                    selectOption(wrapper, option);
                    return;
                }

                // Click outside
                if (activeOpenWrapper && !e.target.closest('.custom-select-wrapper')) {
                    closeDropdown(activeOpenWrapper);
                }
            });

            // Keyboard Navigation (Accessibility)
            document.addEventListener('keydown', function (e) {
                if (!activeOpenWrapper) return;

                const wrapper = activeOpenWrapper;
                const options = Array.from(wrapper.querySelectorAll('.custom-select-option'));
                if (!options.length) return;

                let currentIndex = options.findIndex(opt => opt.getAttribute('aria-selected') === 'true');
                if (currentIndex === -1) currentIndex = 0;

                if (e.key === 'Escape') {
                    e.preventDefault();
                    closeDropdown(wrapper);
                    const trigger = wrapper.querySelector('.custom-select-trigger');
                    if (trigger) trigger.focus();
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    let nextIndex = (currentIndex + 1) % options.length;
                    selectOption(wrapper, options[nextIndex]);
                    openDropdown(wrapper);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    let prevIndex = (currentIndex - 1 + options.length) % options.length;
                    selectOption(wrapper, options[prevIndex]);
                    openDropdown(wrapper);
                } else if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    if (currentIndex >= 0 && currentIndex < options.length) {
                        selectOption(wrapper, options[currentIndex]);
                    }
                } else if (e.key === 'Tab') {
                    closeDropdown(wrapper);
                }
            });
        });
    </script>
</body>
</html>
