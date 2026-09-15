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
                                <a href="{{ route('admin.fakultas-prodi.index') }}" class="flex items-center px-4 py-2 text-xs font-medium hover:bg-emerald-50 hover:text-emerald-900 {{ request()->is('admin/fakultas-prodi*') ? 'bg-emerald-50 font-bold text-emerald-900' : '' }}">
                                    Fakultas & Program Studi
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
                    <!-- Minimal Inline Active Year Indicator in Navbar -->
                    <div class="hidden sm:inline-flex items-center gap-1.5 text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span class="text-emerald-200/90 font-medium">Tahun Aktif <span class="text-emerald-400/60 mx-0.5">·</span> <strong class="font-semibold text-white">{{ \App\Services\TahunMabaService::getActiveYearInt() }}</strong></span>
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
                <a href="{{ route('admin.fakultas-prodi.index') }}" class="block px-3 py-1 text-emerald-200 hover:bg-emerald-800/60 rounded">Fakultas & Program Studi</a>
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
            // Navbar Dropdown Handler
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

            // Reusable Custom Select Component Handler
            function initCustomSelects() {
                const customSelects = document.querySelectorAll('.custom-select-wrapper');

                customSelects.forEach(wrapper => {
                    if (wrapper.dataset.customSelectInitialized) return;
                    wrapper.dataset.customSelectInitialized = 'true';

                    const trigger = wrapper.querySelector('.custom-select-trigger');
                    const menu = wrapper.querySelector('.custom-select-menu');
                    const labelSpan = wrapper.querySelector('.custom-select-label');
                    const hiddenInput = wrapper.querySelector('input[type="hidden"]');
                    const chevron = wrapper.querySelector('.custom-select-chevron');
                    const options = wrapper.querySelectorAll('.custom-select-option');

                    if (!trigger || !menu || !hiddenInput) return;

                    function closeMenu() {
                        menu.classList.add('hidden');
                        trigger.setAttribute('aria-expanded', 'false');
                        if (chevron) chevron.classList.remove('rotate-180');
                        trigger.classList.remove('border-emerald-600', 'ring-2', 'ring-emerald-600/15');
                    }

                    const searchInput = wrapper.querySelector('.custom-prodi-search-input');
                    const groupHeaders = wrapper.querySelectorAll('.custom-prodi-group-header');
                    const noResults = wrapper.querySelector('.custom-prodi-no-results');

                    function openMenu() {
                        // Close all other custom select menus first
                        document.querySelectorAll('.custom-select-menu').forEach(m => {
                            if (m !== menu) {
                                m.classList.add('hidden');
                                const parent = m.closest('.custom-select-wrapper');
                                if (parent) {
                                    const t = parent.querySelector('.custom-select-trigger');
                                    const c = parent.querySelector('.custom-select-chevron');
                                    if (t) t.setAttribute('aria-expanded', 'false');
                                    if (c) c.classList.remove('rotate-180');
                                }
                            }
                        });

                        menu.classList.remove('hidden');
                        trigger.setAttribute('aria-expanded', 'true');
                        if (chevron) chevron.classList.add('rotate-180');
                        trigger.classList.add('border-emerald-600', 'ring-2', 'ring-emerald-600/15');

                        if (searchInput) {
                            searchInput.value = '';
                            filterProdiOptions('');
                            setTimeout(() => searchInput.focus(), 50);
                        }
                    }

                    function filterProdiOptions(query) {
                        const q = (query || '').toLowerCase().trim();
                        let totalMatched = 0;
                        const visibleFakultasIds = new Set();

                        options.forEach(opt => {
                            const text = (opt.getAttribute('data-search-text') || opt.getAttribute('data-label') || '').toLowerCase();
                            const val = opt.getAttribute('data-value');
                            const fId = opt.getAttribute('data-fakultas-id');

                            // Default option (Semua Program Studi) is always visible unless query is typed
                            if (val === '') {
                                if (q === '') {
                                    opt.classList.remove('hidden');
                                } else {
                                    opt.classList.add('hidden');
                                }
                                return;
                            }

                            if (q === '' || text.includes(q)) {
                                opt.classList.remove('hidden');
                                totalMatched++;
                                if (fId) visibleFakultasIds.add(fId);
                            } else {
                                opt.classList.add('hidden');
                            }
                        });

                        groupHeaders.forEach(gh => {
                            const fId = gh.getAttribute('data-fakultas-id');
                            if (visibleFakultasIds.has(fId)) {
                                gh.classList.remove('hidden');
                            } else {
                                gh.classList.add('hidden');
                            }
                        });

                        if (noResults) {
                            if (totalMatched === 0 && q !== '') {
                                noResults.classList.remove('hidden');
                            } else {
                                noResults.classList.add('hidden');
                            }
                        }
                    }

                    if (searchInput) {
                        searchInput.addEventListener('input', function (e) {
                            filterProdiOptions(e.target.value);
                        });
                        searchInput.addEventListener('click', function (e) {
                            e.stopPropagation();
                        });
                        searchInput.addEventListener('keydown', function (e) {
                            if (e.key === 'Escape') {
                                closeMenu();
                            }
                        });
                    }

                    trigger.addEventListener('click', function (e) {
                        e.stopPropagation();
                        if (hiddenInput.disabled) return;
                        const isHidden = menu.classList.contains('hidden');
                        if (isHidden) {
                            openMenu();
                        } else {
                            closeMenu();
                        }
                    });

                    options.forEach(option => {
                        option.addEventListener('click', function (e) {
                            e.stopPropagation();
                            const selectedVal = this.getAttribute('data-value');
                            const selectedLabelText = this.getAttribute('data-label');

                            // Update hidden input
                            hiddenInput.value = selectedVal;

                            // Update trigger label display
                            if (labelSpan) {
                                labelSpan.textContent = selectedLabelText;
                                labelSpan.classList.remove('text-slate-400', 'font-normal');
                                labelSpan.classList.add('text-slate-800', 'font-semibold');
                            }

                            // Update options active state and checkmarks
                            options.forEach(opt => {
                                const isCurrent = opt === this;
                                opt.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
                                opt.className = 'custom-select-option px-3.5 py-2 cursor-pointer flex items-center justify-between transition-colors ' +
                                    (isCurrent ? 'bg-emerald-50/80 font-bold text-emerald-950' : 'text-slate-800 hover:bg-[#F3F7F5] font-normal');

                                let checkIcon = opt.querySelector('svg');
                                if (isCurrent) {
                                    if (!checkIcon) {
                                        checkIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                                        checkIcon.setAttribute('class', 'w-3.5 h-3.5 text-emerald-600 flex-shrink-0 ml-2');
                                        checkIcon.setAttribute('fill', 'none');
                                        checkIcon.setAttribute('stroke', 'currentColor');
                                        checkIcon.setAttribute('viewBox', '0 0 24 24');
                                        checkIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>';
                                        opt.appendChild(checkIcon);
                                    }
                                } else if (checkIcon) {
                                    checkIcon.remove();
                                }
                            });

                            closeMenu();

                            // Dispatch native change event on hidden input so forms and dynamic filters respond
                            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));

                            // Execute inline data-onchange if provided
                            const inlineOnchange = hiddenInput.getAttribute('data-onchange');
                            if (inlineOnchange) {
                                try {
                                    const fn = new Function(inlineOnchange);
                                    fn.call(hiddenInput);
                                } catch (err) {
                                    console.error('Error executing inline onchange:', err);
                                }
                            }
                        });
                    });

                    // Keyboard navigation
                    trigger.addEventListener('keydown', function (e) {
                        if (hiddenInput.disabled) return;

                        if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            if (menu.classList.contains('hidden')) {
                                openMenu();
                            }
                        } else if (e.key === 'Escape') {
                            closeMenu();
                        }
                    });
                });
            }

            initCustomSelects();

            // Reusable Custom Datepicker Component Handler
            function initCustomDatepickers() {
                const datepickers = document.querySelectorAll('.custom-datepicker-wrapper');
                const monthNamesIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                datepickers.forEach(wrapper => {
                    if (wrapper.dataset.customDatepickerInitialized) return;
                    wrapper.dataset.customDatepickerInitialized = 'true';

                    const hiddenInput = wrapper.querySelector('input[type="hidden"]');
                    const triggerInput = wrapper.querySelector('.custom-datepicker-trigger');
                    const popover = wrapper.querySelector('.custom-datepicker-popover');
                    const monthYearSpan = wrapper.querySelector('.custom-datepicker-monthyear');
                    const prevBtn = wrapper.querySelector('.custom-datepicker-prev');
                    const nextBtn = wrapper.querySelector('.custom-datepicker-next');
                    const daysGrid = wrapper.querySelector('.custom-datepicker-days');
                    const clearBtn = wrapper.querySelector('.custom-datepicker-clear');
                    const todayBtn = wrapper.querySelector('.custom-datepicker-today');

                    if (!hiddenInput || !triggerInput || !popover || !daysGrid) return;

                    let currentViewDate = new Date();
                    let selectedDateIso = hiddenInput.value ? hiddenInput.value : '';

                    if (selectedDateIso) {
                        const parts = selectedDateIso.split('-');
                        if (parts.length === 3) {
                            currentViewDate = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                        }
                    }

                    function formatIso(year, monthIndex, day) {
                        const y = year;
                        const m = String(monthIndex + 1).padStart(2, '0');
                        const d = String(day).padStart(2, '0');
                        return `${y}-${m}-${d}`;
                    }

                    function formatDisplay(year, monthIndex, day) {
                        const d = String(day).padStart(2, '0');
                        const m = String(monthIndex + 1).padStart(2, '0');
                        const y = year;
                        return `${d}/${m}/${y}`;
                    }

                    function renderCalendar() {
                        const year = currentViewDate.getFullYear();
                        const month = currentViewDate.getMonth();

                        if (monthYearSpan) {
                            monthYearSpan.textContent = `${monthNamesIndo[month]} ${year}`;
                        }

                        daysGrid.innerHTML = '';

                        const firstDayOfMonth = new Date(year, month, 1).getDay();
                        const daysInMonth = new Date(year, month + 1, 0).getDate();
                        const daysInPrevMonth = new Date(year, month, 0).getDate();

                        const todayObj = new Date();
                        const todayIso = formatIso(todayObj.getFullYear(), todayObj.getMonth(), todayObj.getDate());

                        // Render Previous Month Days
                        for (let i = firstDayOfMonth - 1; i >= 0; i--) {
                            const prevDayNum = daysInPrevMonth - i;
                            const dayBtn = document.createElement('button');
                            dayBtn.type = 'button';
                            dayBtn.className = 'h-8 w-8 mx-auto flex items-center justify-center rounded-full text-slate-300 text-xs font-normal cursor-pointer hover:bg-slate-50 transition';
                            dayBtn.textContent = prevDayNum;

                            dayBtn.addEventListener('click', function (e) {
                                e.stopPropagation();
                                const targetMonth = month === 0 ? 11 : month - 1;
                                const targetYear = month === 0 ? year - 1 : year;
                                selectDate(targetYear, targetMonth, prevDayNum);
                            });

                            daysGrid.appendChild(dayBtn);
                        }

                        // Render Current Month Days
                        for (let d = 1; d <= daysInMonth; d++) {
                            const dateIso = formatIso(year, month, d);
                            const isSelected = (dateIso === selectedDateIso);
                            const isToday = (dateIso === todayIso);

                            const dayBtn = document.createElement('button');
                            dayBtn.type = 'button';
                            
                            let classNames = 'h-8 w-8 mx-auto flex items-center justify-center rounded-full text-xs transition duration-150 cursor-pointer ';
                            if (isSelected) {
                                classNames += 'bg-emerald-700 font-bold text-white shadow-2xs ';
                            } else if (isToday) {
                                classNames += 'bg-emerald-50 text-emerald-900 font-bold border border-emerald-300 ';
                            } else {
                                classNames += 'text-slate-800 hover:bg-[#F3F7F5] hover:text-emerald-900 font-medium ';
                            }

                            dayBtn.className = classNames;
                            dayBtn.textContent = d;

                            dayBtn.addEventListener('click', function (e) {
                                e.stopPropagation();
                                selectDate(year, month, d);
                            });

                            daysGrid.appendChild(dayBtn);
                        }

                        // Render Next Month Days to complete grid rows
                        const totalRendered = firstDayOfMonth + daysInMonth;
                        const remainder = (7 - (totalRendered % 7)) % 7;
                        for (let n = 1; n <= remainder; n++) {
                            const dayBtn = document.createElement('button');
                            dayBtn.type = 'button';
                            dayBtn.className = 'h-8 w-8 mx-auto flex items-center justify-center rounded-full text-slate-300 text-xs font-normal cursor-pointer hover:bg-slate-50 transition';
                            dayBtn.textContent = n;

                            dayBtn.addEventListener('click', function (e) {
                                e.stopPropagation();
                                const targetMonth = month === 11 ? 0 : month + 1;
                                const targetYear = month === 11 ? year + 1 : year;
                                selectDate(targetYear, targetMonth, n);
                            });

                            daysGrid.appendChild(dayBtn);
                        }
                    }

                    function selectDate(year, monthIndex, day) {
                        const isoStr = formatIso(year, monthIndex, day);
                        const displayStr = formatDisplay(year, monthIndex, day);

                        selectedDateIso = isoStr;
                        hiddenInput.value = isoStr;
                        triggerInput.value = displayStr;

                        currentViewDate = new Date(year, monthIndex, day);
                        closePopover();

                        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));

                        const inlineOnchange = hiddenInput.getAttribute('data-onchange');
                        if (inlineOnchange) {
                            try {
                                const fn = new Function(inlineOnchange);
                                fn.call(hiddenInput);
                            } catch (err) {
                                console.error('Error executing inline datepicker onchange:', err);
                            }
                        }
                    }

                    function clearDate() {
                        selectedDateIso = '';
                        hiddenInput.value = '';
                        triggerInput.value = '';
                        closePopover();

                        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));

                        const inlineOnchange = hiddenInput.getAttribute('data-onchange');
                        if (inlineOnchange) {
                            try {
                                const fn = new Function(inlineOnchange);
                                fn.call(hiddenInput);
                            } catch (err) {
                                console.error('Error executing inline datepicker onchange:', err);
                            }
                        }
                    }

                    function openPopover() {
                        // Close all other popovers and select menus
                        document.querySelectorAll('.custom-datepicker-popover').forEach(p => {
                            if (p !== popover) p.classList.add('hidden');
                        });
                        document.querySelectorAll('.custom-select-menu').forEach(m => m.classList.add('hidden'));

                        renderCalendar();
                        popover.classList.remove('hidden');
                        triggerInput.setAttribute('aria-expanded', 'true');
                        triggerInput.classList.add('border-emerald-600', 'ring-2', 'ring-emerald-600/15');
                    }

                    function closePopover() {
                        popover.classList.add('hidden');
                        triggerInput.setAttribute('aria-expanded', 'false');
                        triggerInput.classList.remove('border-emerald-600', 'ring-2', 'ring-emerald-600/15');
                    }

                    triggerInput.addEventListener('click', function (e) {
                        e.stopPropagation();
                        if (hiddenInput.disabled) return;
                        if (popover.classList.contains('hidden')) {
                            openPopover();
                        } else {
                            closePopover();
                        }
                    });

                    if (prevBtn) {
                        prevBtn.addEventListener('click', function (e) {
                            e.stopPropagation();
                            currentViewDate.setMonth(currentViewDate.getMonth() - 1);
                            renderCalendar();
                        });
                    }

                    if (nextBtn) {
                        nextBtn.addEventListener('click', function (e) {
                            e.stopPropagation();
                            currentViewDate.setMonth(currentViewDate.getMonth() + 1);
                            renderCalendar();
                        });
                    }

                    if (clearBtn) {
                        clearBtn.addEventListener('click', function (e) {
                            e.stopPropagation();
                            clearDate();
                        });
                    }

                    if (todayBtn) {
                        todayBtn.addEventListener('click', function (e) {
                            e.stopPropagation();
                            const now = new Date();
                            selectDate(now.getFullYear(), now.getMonth(), now.getDate());
                        });
                    }

                    triggerInput.addEventListener('keydown', function (e) {
                        if (hiddenInput.disabled) return;
                        if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            if (popover.classList.contains('hidden')) {
                                openPopover();
                            }
                        } else if (e.key === 'Escape') {
                            closePopover();
                        }
                    });
                });
            }

            initCustomDatepickers();

            // Click outside handler
            document.addEventListener('click', function () {
                dropdownWrappers.forEach(w => {
                    const p = w.querySelector('.nav-dropdown-panel');
                    const c = w.querySelector('.dropdown-chevron');
                    if (p) p.classList.add('hidden');
                    if (c) c.classList.remove('rotate-180');
                });

                document.querySelectorAll('.custom-select-wrapper').forEach(wrapper => {
                    const menu = wrapper.querySelector('.custom-select-menu');
                    const trigger = wrapper.querySelector('.custom-select-trigger');
                    const chevron = wrapper.querySelector('.custom-select-chevron');
                    if (menu && !menu.classList.contains('hidden')) {
                        menu.classList.add('hidden');
                        if (trigger) trigger.setAttribute('aria-expanded', 'false');
                        if (chevron) chevron.classList.remove('rotate-180');
                        if (trigger) trigger.classList.remove('border-emerald-600', 'ring-2', 'ring-emerald-600/15');
                    }
                });

                document.querySelectorAll('.custom-datepicker-wrapper').forEach(wrapper => {
                    const popover = wrapper.querySelector('.custom-datepicker-popover');
                    const triggerInput = wrapper.querySelector('.custom-datepicker-trigger');
                    if (popover && !popover.classList.contains('hidden')) {
                        popover.classList.add('hidden');
                        if (triggerInput) {
                            triggerInput.setAttribute('aria-expanded', 'false');
                            triggerInput.classList.remove('border-emerald-600', 'ring-2', 'ring-emerald-600/15');
                        }
                    }
                });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.custom-select-wrapper').forEach(wrapper => {
                        const menu = wrapper.querySelector('.custom-select-menu');
                        const trigger = wrapper.querySelector('.custom-select-trigger');
                        const chevron = wrapper.querySelector('.custom-select-chevron');
                        if (menu && !menu.classList.contains('hidden')) {
                            menu.classList.add('hidden');
                            if (trigger) trigger.setAttribute('aria-expanded', 'false');
                            if (chevron) chevron.classList.remove('rotate-180');
                            if (trigger) trigger.classList.remove('border-emerald-600', 'ring-2', 'ring-emerald-600/15');
                        }
                    });

                    document.querySelectorAll('.custom-datepicker-wrapper').forEach(wrapper => {
                        const popover = wrapper.querySelector('.custom-datepicker-popover');
                        const triggerInput = wrapper.querySelector('.custom-datepicker-trigger');
                        if (popover && !popover.classList.contains('hidden')) {
                            popover.classList.add('hidden');
                            if (triggerInput) {
                                triggerInput.setAttribute('aria-expanded', 'false');
                                triggerInput.classList.remove('border-emerald-600', 'ring-2', 'ring-emerald-600/15');
                            }
                        }
                    });
                }
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
    @stack('scripts')
</body>
</html>

