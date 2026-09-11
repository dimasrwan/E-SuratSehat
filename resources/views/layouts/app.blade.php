<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Surat Sehat | Klinik UIN Ar-Raniry</title>
    <link rel="icon" type="image/png" href="{{ asset('logo-uin.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">
    <!-- Navbar -->
    <nav class="bg-emerald-800 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo / Brand -->
                    <div class="flex-shrink-0 flex items-center">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('logo-uin.png') }}" alt="Logo UIN" class="w-11 h-11 bg-white rounded-full p-0.5 shadow-inner object-contain">
                            <div>
                                <div class="text-xl font-bold text-white tracking-tight">E-Surat Sehat</div>
                                <div class="text-xs text-emerald-200 font-medium">UIN Ar-Raniry Banda Aceh</div>
                            </div>
                        </div>
                    </div>
                    <!-- Navigation Links -->
                    <div class="hidden sm:ml-10 sm:flex sm:space-x-8">
                        <a href="/" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('/') ? 'border-amber-400 text-white' : 'border-transparent text-emerald-100 hover:border-emerald-300 hover:text-white' }} text-sm font-medium transition">
                            Dashboard
                        </a>
                        <a href="/pemeriksaan" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('pemeriksaan*') ? 'border-amber-400 text-white' : 'border-transparent text-emerald-100 hover:border-emerald-300 hover:text-white' }} text-sm font-medium transition">
                            Data Pemeriksaan
                        </a>
                        <a href="/pengiriman" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('pengiriman*') ? 'border-amber-400 text-white' : 'border-transparent text-emerald-100 hover:border-emerald-300 hover:text-white' }} text-sm font-medium transition">
                            Pengiriman
                        </a>
                        @if(Auth::check() && Auth::user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('admin/users*') ? 'border-amber-400 text-white' : 'border-transparent text-emerald-100 hover:border-emerald-300 hover:text-white' }} text-sm font-medium transition">
                            Manajemen Pengguna
                        </a>
                        <a href="{{ route('admin.tahun-maba.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('admin/tahun-maba*') ? 'border-amber-400 text-white' : 'border-transparent text-emerald-100 hover:border-emerald-300 hover:text-white' }} text-sm font-medium transition">
                            Manajemen Tahun Maba
                        </a>
                        @endif
                    </div>
                </div>

                @auth
                <div class="flex items-center gap-4">
                    <div class="inline-flex items-center gap-1.5 bg-emerald-900/40 border border-emerald-600/50 px-3 py-1 rounded-full text-xs shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span class="text-emerald-200/90 font-normal hidden sm:inline">Tahun Aktif</span>
                        <span class="text-emerald-300 font-medium hidden sm:inline">·</span>
                        <span class="text-white font-semibold tracking-wide">Maba {{ \App\Services\TahunMabaService::getActiveYearInt() }}</span>
                    </div>
                    <div class="text-right text-xs">
                        <div class="font-bold text-white">{{ Auth::user()->name }}</div>
                        <div class="text-emerald-200 capitalize">{{ Auth::user()->role ?? 'Operator' }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs bg-emerald-900 hover:bg-emerald-950 text-emerald-100 font-semibold px-3 py-1.5 rounded-lg border border-emerald-700 transition">
                            Keluar
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
