<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biodata Berhasil Disimpan - E-Surat Sehat</title>
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
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-xl mx-auto px-4 py-12 sm:py-16 w-full flex-grow flex items-center justify-center">
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-200/80 p-8 text-center space-y-6 w-full">
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-3xl shadow-lg shadow-emerald-100">
                ✓
            </div>

            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Data Berhasil Disimpan!</h2>
                <p class="text-slate-600 text-sm leading-relaxed">
                    Data pribadi Anda telah tersimpan di sistem dan sedang <span class="font-bold text-amber-600">MENUNGGU VERIFIKASI</span> oleh petugas Klinik UIN Ar-Raniry.
                </p>
            </div>

            @if(isset($mabaData) && $mabaData)
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 text-left text-xs space-y-2">
                <div class="flex justify-between border-b border-slate-200 pb-1.5">
                    <span class="text-slate-500">Nama:</span>
                    <span class="font-bold text-slate-900">{{ $mabaData->nama_lengkap }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-200 pb-1.5">
                    <span class="text-slate-500">Program Studi:</span>
                    <span class="font-bold text-emerald-700">{{ $mabaData->program_studi_biro }}</span>
                </div>
                @if($mabaData->tanggal_jadwal)
                <div class="flex justify-between">
                    <span class="text-slate-500">Jadwal Pemeriksaan:</span>
                    <span class="font-bold text-slate-900">{{ $mabaData->tanggal_jadwal }} (Sesi {{ $mabaData->sesi_jadwal ?? '-' }})</span>
                </div>
                @endif
            </div>
            @endif

            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-left flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="text-xs text-amber-900 leading-relaxed font-medium">
                    <strong>Langkah Selanjutnya:</strong> Silakan datang ke Klinik UIN Ar-Raniry sesuai jadwal pendaftaran Anda dengan membawa kartu identitas resmi (KTP/KK). Operator klinik akan memverifikasi fisik identitas Anda sebelum pemeriksaan kesehatan.
                </div>
            </div>

            <div class="pt-2">
                <a href="{{ route('portal.index') }}" class="inline-block px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-md transition duration-150">
                    Kembali ke Halaman Utama
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Klinik UIN Ar-Raniry Banda Aceh. Seluruh Hak Cipta Dilindungi.</p>
    </footer>
</body>
</html>
