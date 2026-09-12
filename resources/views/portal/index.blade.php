<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Data Kesehatan Maba - E-Surat Sehat Klinik UIN Ar-Raniry</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col justify-between">
    <!-- Header Navigation -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <a href="{{ route('portal.index') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-extrabold text-xl shadow-md shadow-emerald-600/20">
                    E
                </div>
                <div>
                    <h1 class="font-extrabold text-slate-800 text-lg leading-none">E-Surat Sehat</h1>
                    <p class="text-xs font-semibold text-emerald-600 mt-0.5">Klinik UIN Ar-Raniry Banda Aceh</p>
                </div>
            </a>
            @if(isset($activeYear) && $activeYear)
            <div class="hidden sm:flex items-center gap-2 bg-emerald-50 text-emerald-800 text-xs font-bold px-3 py-1.5 rounded-full border border-emerald-200/60">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Tahun Aktif Pendaftaran: Maba {{ $activeYear->tahun }}
            </div>
            @endif
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-4xl mx-auto px-4 py-8 sm:py-12 w-full flex-grow">
        <!-- Hero Title Section -->
        <div class="text-center mb-8 sm:mb-12">
            @if(isset($activeYear) && $activeYear)
            <span class="inline-block bg-emerald-100 text-emerald-800 text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-wider mb-3">
                Maba {{ $activeYear->tahun }}
            </span>
            @endif
            <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-3">
                Portal Data Kesehatan Maba
            </h2>
            <p class="text-slate-600 text-sm sm:text-base max-w-xl mx-auto">
                Lengkapi data pribadi Anda secara mandiri sebelum melakukan pemeriksaan fisik di Klinik UIN Ar-Raniry.
            </p>
        </div>

        @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>{{ session('error') }}</div>
        </div>
        @endif

        <!-- Search Card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-200/80 p-6 sm:p-8 mb-8">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Cari Data Pendaftaran Mahasiswa Baru
            </h3>
            
            <form action="{{ route('portal.search') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" id="nama" value="{{ old('nama', $nama ?? '') }}" required placeholder="Ketik nama lengkap Anda..." class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:bg-white transition duration-150">
                    </div>
                    <div>
                        <label for="program_studi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Program Studi</label>
                        <input type="text" name="program_studi" id="program_studi" value="{{ old('program_studi', $prodi ?? '') }}" placeholder="Contoh: Teknik Informatika" class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:bg-white transition duration-150">
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/20 transition duration-150 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Cari Data Saya
                    </button>
                </div>
            </form>
        </div>

        <!-- Search Results Section -->
        @if(isset($results))
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-200/80 p-6 sm:p-8">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center justify-between">
                <span>Hasil Pencarian</span>
                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">{{ count($results) }} Hasil Ditemukan</span>
            </h3>

            @if(count($results) === 0)
            <div class="text-center py-8 text-slate-500">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="font-bold text-slate-700 text-base mb-1">Data tidak ditemukan</p>
                <p class="text-xs text-slate-500 max-w-md mx-auto">Pastikan ejaan nama atau program studi sesuai dengan data pendaftaran dari Biro Akademik.</p>
            </div>
            @else
            <div class="space-y-4">
                @foreach($results as $maba)
                <div class="border border-slate-200/80 rounded-2xl p-4 sm:p-5 hover:border-emerald-300 hover:bg-emerald-50/20 transition duration-150 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h4 class="font-extrabold text-slate-900 text-base sm:text-lg">{{ $maba->nama_biro }}</h4>
                        <p class="text-sm font-semibold text-emerald-700">{{ $maba->program_studi_biro }}</p>
                        
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 pt-1">
                            @if($maba->tanggal_jadwal)
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Jadwal: {{ $maba->tanggal_jadwal }}
                            </span>
                            @endif

                            @if($maba->sesi_jadwal || $maba->waktu_jadwal)
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Sesi {{ $maba->sesi_jadwal ?? '-' }} ({{ $maba->waktu_jadwal ?? '-' }})
                            </span>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('portal.claim') }}" method="POST" class="sm:flex-shrink-0">
                        @csrf
                        <input type="hidden" name="maba_id" value="{{ $maba->id }}">
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-emerald-600/20 transition duration-150">
                            Ini Data Saya
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Klinik UIN Ar-Raniry Banda Aceh. Seluruh Hak Cipta Dilindungi.</p>
    </footer>
</body>
</html>
