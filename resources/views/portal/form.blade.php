<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isi Biodata Maba - E-Surat Sehat Klinik UIN Ar-Raniry</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col justify-between" x-data="{ showModal: false }">
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
            <div class="flex items-center gap-2 bg-emerald-50 text-emerald-800 text-xs font-bold px-3 py-1.5 rounded-full border border-emerald-200/60">
                Sesi Terklaim (30 Menit)
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-3xl mx-auto px-4 py-8 sm:py-12 w-full flex-grow">
        <div class="mb-8">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-2">
                Pengisian Biodata Mandiri Mahasiswa
            </h2>
            <p class="text-slate-600 text-sm">
                Pastikan data yang Anda masukkan lengkap dan valid sesuai dokumen resmi KTP/KK.
            </p>
        </div>

        @if($claimedMaba->status_biodata === 'PERLU_PERBAIKAN' && !empty($claimedMaba->catatan_perbaikan))
        <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-sm font-semibold flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <strong class="font-extrabold text-amber-950 block mb-0.5">Catatan Perbaikan dari Operator Klinik:</strong>
                <p class="text-xs text-amber-900 leading-relaxed">{{ $claimedMaba->catatan_perbaikan }}</p>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold">
            <div class="font-bold mb-1">Terdapat kesalahan pengisian form:</div>
            <ul class="list-disc list-inside space-y-1 text-xs text-rose-700">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('portal.biodata.submit') }}" method="POST" id="biodataForm">
            @csrf
            
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-200/80 p-6 sm:p-8 space-y-6">
                <!-- 1. Nama Lengkap -->
                <div>
                    <label for="nama_lengkap" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">1. Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" value="{{ old('nama_lengkap', $claimedMaba->nama_biro) }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:bg-white transition duration-150">
                    <p class="mt-1 text-[11px] text-slate-400">Dapat dikoreksi jika terdapat perbedaan penulisan dengan KTP.</p>
                </div>

                <!-- 2. NIK -->
                <div>
                    <label for="nik" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">2. NIK (16 Digit) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nik" id="nik" maxlength="16" value="{{ old('nik', $claimedMaba->nik) }}" required placeholder="Contoh: 1171012304990001" class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-mono font-semibold focus:ring-2 focus:ring-emerald-500 focus:bg-white transition duration-150">
                </div>

                <!-- 3. Email Mahasiswa -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">3. Email Mahasiswa <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $claimedMaba->email) }}" required placeholder="email@mahasiswa.ac.id" class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:bg-white transition duration-150">
                    <p class="mt-1 text-[11px] text-slate-400">Surat Sehat PDF akan dikirimkan ke alamat email ini setelah selesai pemeriksaan.</p>
                </div>

                <!-- 4 & 5. Tempat & Tanggal Lahir -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tempat_lahir" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">4. Tempat Lahir <span class="text-rose-500">*</span></label>
                        <input type="text" name="tempat_lahir" id="tempat_lahir" value="{{ old('tempat_lahir', $claimedMaba->tempat_lahir) }}" required placeholder="Kota Kelahiran" class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:bg-white transition duration-150">
                    </div>
                    <div>
                        <label for="tanggal_lahir" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">5. Tanggal Lahir <span class="text-rose-500">*</span></label>
                        <x-form-datepicker name="tanggal_lahir" id="tanggal_lahir" :value="old('tanggal_lahir', $claimedMaba->tanggal_lahir ? $claimedMaba->tanggal_lahir->format('Y-m-d') : '')" onchange="calculateAgePreview()" required />
                    </div>
                </div>

                <!-- 6. Umur Preview -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">6. Estimasi Umur (Server-Derived)</label>
                    <input type="text" id="umur_preview" readonly value="{{ $claimedMaba->umur ? $claimedMaba->umur . ' Tahun' : '-' }}" class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 text-sm font-semibold cursor-not-allowed">
                </div>

                <!-- 7 & 8. Jenis Kelamin & Agama -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="jenis_kelamin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">7. Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <x-form-select name="jenis_kelamin" id="jenis_kelamin" :value="old('jenis_kelamin', $claimedMaba->jenis_kelamin)" placeholder="-- Pilih Jenis Kelamin --" :options="[
                            'Laki-laki' => 'Laki-laki',
                            'Perempuan' => 'Perempuan'
                        ]" required />
                    </div>
                    <div>
                        <label for="agama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">8. Agama <span class="text-rose-500">*</span></label>
                        <input type="text" name="agama" id="agama" value="{{ old('agama', $claimedMaba->agama ?? 'Islam') }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:bg-white transition duration-150">
                    </div>
                </div>

                <!-- 9. Fakultas -->
                <div>
                    <label for="fakultas" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">9. Fakultas <span class="text-rose-500">*</span></label>
                    @php
                        $fakultasList = [
                            'Fakultas Tarbiyah dan Keguruan' => 'Fakultas Tarbiyah dan Keguruan',
                            'Fakultas Syariah dan Hukum' => 'Fakultas Syariah dan Hukum',
                            'Fakultas Dakwah dan Komunikasi' => 'Fakultas Dakwah dan Komunikasi',
                            'Fakultas Ushuluddin dan Filsafat' => 'Fakultas Ushuluddin dan Filsafat',
                            'Fakultas Adab dan Humaniora' => 'Fakultas Adab dan Humaniora',
                            'Fakultas Ekonomi dan Bisnis Islam' => 'Fakultas Ekonomi dan Bisnis Islam',
                            'Fakultas Sains dan Teknologi' => 'Fakultas Sains dan Teknologi',
                            'Fakultas Psikologi' => 'Fakultas Psikologi',
                            'Fakultas Ilmu Sosial dan Ilmu Pemerintahan' => 'Fakultas Ilmu Sosial dan Ilmu Pemerintahan'
                        ];
                    @endphp
                    <x-form-select name="fakultas" id="fakultas" :value="old('fakultas', $claimedMaba->fakultas)" placeholder="-- Pilih Fakultas --" :options="$fakultasList" required />
                </div>

                <!-- 10. Program Studi (READ ONLY) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">10. Program Studi (Resmi dari Biro) <span class="text-emerald-600 text-[10px] font-normal uppercase">(Terunci)</span></label>
                    <input type="text" readonly value="{{ $claimedMaba->program_studi_biro }}" class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-emerald-800 font-bold text-sm cursor-not-allowed">
                    <p class="mt-1 text-[11px] text-slate-400">Program studi berasal dari pendaftaran Biro Akademik dan tidak dapat diubah.</p>
                </div>

                <!-- 11. Pekerjaan -->
                <div>
                    <label for="pekerjaan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">11. Pekerjaan <span class="text-rose-500">*</span></label>
                    <input type="text" name="pekerjaan" id="pekerjaan" value="{{ old('pekerjaan', $claimedMaba->pekerjaan ?? 'Mahasiswa') }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:bg-white transition duration-150">
                </div>

                <!-- 12. Alamat Lengkap -->
                <div>
                    <label for="alamat" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">12. Alamat Lengkap Tempat Tinggal <span class="text-rose-500">*</span></label>
                    <textarea name="alamat" id="alamat" rows="3" required placeholder="Jalan, RT/RW, Gampong/Kecamatan, Kabupaten/Kota" class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:bg-white transition duration-150">{{ old('alamat', $claimedMaba->alamat) }}</textarea>
                </div>

                <!-- Submit Button / Open Confirmation Modal -->
                <div class="pt-4 flex justify-end">
                    <button type="button" @click="showModal = true" class="w-full sm:w-auto px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/20 transition duration-150 flex items-center justify-center gap-2">
                        <span>Lanjutkan & Periksa Data</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
                <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 space-y-6">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900">Konfirmasi Biodata</h3>
                        <p class="text-xs text-slate-500 mt-1">Periksa kembali ringkasan data Anda sebelum dikirim untuk verifikasi Operator.</p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2 text-xs">
                        <div class="flex justify-between border-b border-slate-200 pb-1.5">
                            <span class="text-slate-500">Nama:</span>
                            <span class="font-bold text-slate-900" id="summary_nama"></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-200 pb-1.5">
                            <span class="text-slate-500">NIK:</span>
                            <span class="font-bold text-slate-900" id="summary_nik"></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-200 pb-1.5">
                            <span class="text-slate-500">Email:</span>
                            <span class="font-bold text-slate-900" id="summary_email"></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-200 pb-1.5">
                            <span class="text-slate-500">Program Studi:</span>
                            <span class="font-bold text-emerald-700">{{ $claimedMaba->program_studi_biro }}</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="konfirmasi" value="1" required class="mt-1 w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                            <span class="text-xs text-slate-700 font-semibold leading-relaxed">
                                Saya memastikan data yang saya isi sudah benar dan sesuai dengan identitas resmi saya.
                            </span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition duration-150">
                            Kembali Edit
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition duration-150">
                            Kirim Biodata
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Klinik UIN Ar-Raniry Banda Aceh. Seluruh Hak Cipta Dilindungi.</p>
    </footer>

    <script>
        function calculateAgePreview() {
            const dob = document.getElementById('tanggal_lahir').value;
            if (dob) {
                const birthDate = new Date(dob);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                document.getElementById('umur_preview').value = age + ' Tahun';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const updateSummary = () => {
                document.getElementById('summary_nama').textContent = document.getElementById('nama_lengkap').value || '-';
                document.getElementById('summary_nik').textContent = document.getElementById('nik').value || '-';
                document.getElementById('summary_email').textContent = document.getElementById('email').value || '-';
            };
            document.getElementById('nama_lengkap').addEventListener('input', updateSummary);
            document.getElementById('nik').addEventListener('input', updateSummary);
            document.getElementById('email').addEventListener('input', updateSummary);
            updateSummary();
        });
    </script>
</body>
</html>
