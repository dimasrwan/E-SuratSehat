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
                        <a href="/pemeriksaan" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('pemeriksaan') ? 'border-amber-400 text-white' : 'border-transparent text-emerald-100 hover:border-emerald-300 hover:text-white' }} text-sm font-medium transition">
                            Data Pemeriksaan
                        </a>
                        <a href="/pengiriman" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->is('pengiriman') ? 'border-amber-400 text-white' : 'border-transparent text-emerald-100 hover:border-emerald-300 hover:text-white' }} text-sm font-medium transition">
                            Pengiriman
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
