<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | E-Surat Sehat Klinik UIN Ar-Raniry</title>
    <link rel="icon" type="image/png" href="{{ asset('logo-uin.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center font-sans antialiased py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-xl border border-gray-100">
        <div class="text-center">
            <a href="{{ route('landing') }}" class="inline-block">
                <img class="mx-auto h-20 w-20 object-contain bg-emerald-50 p-2 rounded-full border-2 border-emerald-500 shadow-sm hover:scale-105 transition-transform" src="{{ asset('logo-uin.png') }}" alt="Logo UIN Ar-Raniry">
            </a>
            <h2 class="mt-4 text-3xl font-extrabold text-gray-900 tracking-tight">E-Surat Sehat</h2>
            <p class="mt-1 text-sm font-medium text-emerald-700">Klinik UIN Ar-Raniry Banda Aceh</p>
            <p class="mt-4 text-xs text-gray-500 uppercase tracking-widest font-semibold border-t border-b border-gray-100 py-2">Portal Login E-Surat Sehat</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl text-sm text-red-700 shadow-sm">
                <div class="font-bold mb-1">Gagal Masuk:</div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="mt-6 space-y-6" action="{{ route('login') }}" method="POST">
            @csrf
            <div class="rounded-md space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" placeholder="user@klinik.uin.ac.id" class="appearance-none rounded-xl relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="••••••••" class="appearance-none rounded-xl relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">Ingat Saya</label>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-emerald-800 hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 shadow-md transition duration-150">
                    Masuk ke Sistem
                </button>
            </div>
        </form>
    </div>
</body>
</html>
