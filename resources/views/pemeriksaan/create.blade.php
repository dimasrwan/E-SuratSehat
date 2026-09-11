@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Tambah Data Pemeriksaan</h2>
        <a href="{{ route('pemeriksaan.index') }}" class="inline-flex items-center text-sm font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-4 py-2 rounded-full transition duration-150">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>

    <form action="{{ route('pemeriksaan.store') }}" method="POST">
        @csrf

        <!-- SECTION 1: Menerangkan Bahwa -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 mb-8">
            <div class="border-b border-gray-100 bg-gray-50/50 px-8 py-5">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-emerald-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Informasi Identitas
                </h3>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">Tahun Pendaftaran <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-3 bg-emerald-50/70 border border-emerald-200/80 rounded-xl p-3">
                            <span class="text-emerald-900 font-bold text-base">Maba {{ $tahunContext }}</span>
                            <span class="bg-emerald-600 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase tracking-wider">Tahun Aktif</span>
                        </div>
                        <input type="hidden" name="tahun_masuk" value="{{ $tahunContext }}">
                        <p class="mt-1.5 text-[11px] text-slate-500 italic">*Ditentukan berdasarkan Tahun Aktif yang ditetapkan Admin.</p>
                    </div>

                    <div>
                        <label for="nomor_surat" class="block mb-2 text-sm font-medium text-gray-700">Nomor Surat (Otomatis)</label>
                        <input type="text" name="nomor_surat" id="nomor_surat" value="{{ $estimated_nomor }}" readonly class="bg-gray-100 border border-gray-300 text-gray-500 font-mono text-sm rounded-xl block w-full p-3 cursor-not-allowed">
                        <p class="mt-1 text-xs text-gray-500 italic">*Nomor ini adalah estimasi. Jika ada antrean simpan, nomor bisa menyesuaikan otomatis untuk mencegah bentrok.</p>
                    </div>

                    <div>
                        <label for="dokter_nama" class="block mb-2 text-sm font-medium text-gray-700">Dokter Pemeriksa</label>
                        <x-form-select name="dokter_nama" id="dokter_nama" :value="old('dokter_nama', 'dr. Desminawati')" onchange="updateDokterNip()" placeholder="-- Pilih Dokter --" :options="[
                            'dr. Nadia Fajri, M.K.M' => ['label' => 'dr. Nadia Fajri, M.K.M', 'value' => 'dr. Nadia Fajri, M.K.M', 'extra' => ['nip' => '198006032010012012']],
                            'dr. Desminawati' => ['label' => 'dr. Desminawati', 'value' => 'dr. Desminawati', 'extra' => ['nip' => '198002062010012007']]
                        ]" />
                        <input type="hidden" name="dokter_nip" id="dokter_nip" value="{{ old('dokter_nip', '198002062010012007') }}">
                    </div>

                    <div>
                        <label for="nama" class="block mb-2 text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required placeholder="Masukkan nama lengkap" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>

                    <div>
                        <label for="nik" class="block mb-2 text-sm font-medium text-gray-700">NIK</label>
                        <input type="text" name="nik" id="nik" value="{{ old('nik') }}" placeholder="Nomor Induk Kependudukan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>

                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-700">Email Mahasiswa <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required oninput="this.value = this.value.toLowerCase()" placeholder="email@contoh.com" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <label class="block mb-2 text-sm font-medium text-gray-700">Tempat Lahir</label>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <input type="text" name="tempat_lahir" id="tempat_lahir" value="{{ old('tempat_lahir') }}" placeholder="Kota kelahiran" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full sm:w-1/2 p-3 transition duration-150">
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="{{ old('tanggal_lahir') }}" onchange="calculateAge()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full sm:w-1/2 p-3 transition duration-150">
                        </div>
                    </div>

                    <div>
                        <label for="umur" class="block mb-2 text-sm font-medium text-gray-700">Umur</label>
                        <input type="number" name="umur" id="umur" value="{{ old('umur') }}" placeholder="Tahun" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>
                    
                    <div>
                        <label for="jenis_kelamin" class="block mb-2 text-sm font-medium text-gray-700">Jenis Kelamin</label>
                        <x-form-select name="jenis_kelamin" id="jenis_kelamin" :value="old('jenis_kelamin')" placeholder="Pilih..." :options="[
                            'Laki-Laki' => 'Laki-Laki',
                            'Perempuan' => 'Perempuan'
                        ]" />
                    </div>

                    <div>
                        <label for="agama" class="block mb-2 text-sm font-medium text-gray-700">Agama</label>
                        <input type="text" name="agama" id="agama" value="{{ old('agama', 'Islam') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>
                    
                    <div>
                        <label for="fakultas" class="block mb-2 text-sm font-medium text-gray-700">Fakultas</label>
                        <x-form-select name="fakultas" id="fakultas" :value="old('fakultas')" placeholder="Pilih Fakultas..." :options="[
                            'Fakultas Tarbiyah dan Keguruan' => 'Fakultas Tarbiyah dan Keguruan',
                            'Fakultas Syariah dan Hukum' => 'Fakultas Syariah dan Hukum',
                            'Fakultas Dakwah dan Komunikasi' => 'Fakultas Dakwah dan Komunikasi',
                            'Fakultas Ushuluddin dan Filsafat' => 'Fakultas Ushuluddin dan Filsafat',
                            'Fakultas Adab dan Humaniora' => 'Fakultas Adab dan Humaniora',
                            'Fakultas Ekonomi dan Bisnis Islam' => 'Fakultas Ekonomi dan Bisnis Islam',
                            'Fakultas Sains dan Teknologi' => 'Fakultas Sains dan Teknologi',
                            'Fakultas Psikologi' => 'Fakultas Psikologi',
                            'Fakultas Ilmu Sosial dan Ilmu Pemerintahan' => 'Fakultas Ilmu Sosial dan Ilmu Pemerintahan'
                        ]" />
                    </div>

                    <div>
                        <label for="pekerjaan" class="block mb-2 text-sm font-medium text-gray-700">Pekerjaan</label>
                        <input type="text" name="pekerjaan" id="pekerjaan" value="{{ old('pekerjaan', 'Mahasiswa') }}" placeholder="Contoh: Mahasiswa, Dosen, PNS, dll" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>

                    <div class="col-span-2">
                        <label for="alamat" class="block mb-2 text-sm font-medium text-gray-700">Alamat Lengkap</label>
                        <input type="text" name="alamat" id="alamat" value="{{ old('alamat') }}" placeholder="Jalan, RT/RW, Desa, Kecamatan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Pemeriksaan & Kesimpulan -->
        <div class="bg-emerald-50/50 overflow-hidden shadow-sm sm:rounded-2xl border border-emerald-100 mb-8">
            <div class="border-b border-emerald-100 bg-emerald-100/50 px-8 py-5">
                <h3 class="text-lg font-bold text-emerald-900 flex items-center">
                    <svg class="w-5 h-5 text-emerald-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Pemeriksaan & Kesimpulan
                </h3>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                    <div>
                        <label for="kesimpulan" class="block mb-2 text-sm font-bold text-emerald-900">Dinyatakan <span class="text-red-500">*</span></label>
                        <x-form-select name="kesimpulan" id="kesimpulan" :value="old('kesimpulan', 'SEHAT DAN TIDAK BUTA WARNA')" required :options="[
                            'SEHAT DAN TIDAK BUTA WARNA' => 'SEHAT DAN TIDAK BUTA WARNA',
                            'SEHAT DAN BUTA WARNA' => 'SEHAT DAN BUTA WARNA',
                            'SEHAT DAN BUTA WARNA PARSIAL' => 'SEHAT DAN BUTA WARNA PARSIAL',
                            'SEHAT' => 'SEHAT',
                            'TIDAK SEHAT' => 'TIDAK SEHAT',
                            'TIDAK SEHAT DAN BUTA WARNA PARSIAL' => 'TIDAK SEHAT DAN BUTA WARNA PARSIAL'
                        ]" />
                    </div>
                    <div>
                        <label for="keperluan" class="block mb-2 text-sm font-bold text-emerald-900">Keperluan</label>
                        <input type="text" name="keperluan" id="keperluan" value="{{ old('keperluan', 'SYARAT ADMINISTRASI MASUK KULIAH') }}" class="bg-white border border-emerald-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 shadow-sm transition duration-150">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Data Fisik -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 mb-8">
            <div class="border-b border-gray-100 bg-gray-50/50 px-8 py-5">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-amber-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    Data Fisik
                </h3>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="tinggi_badan" class="block mb-2 text-sm font-medium text-gray-700">Tinggi Badan (cm)</label>
                        <input type="number" step="0.1" name="tinggi_badan" id="tinggi_badan" value="{{ old('tinggi_badan') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>
                    <div>
                        <label for="berat_badan" class="block mb-2 text-sm font-medium text-gray-700">Berat Badan (kg)</label>
                        <input type="number" step="0.1" name="berat_badan" id="berat_badan" value="{{ old('berat_badan') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>
                    <div>
                        <label for="tekanan_darah" class="block mb-2 text-sm font-medium text-gray-700">Tekanan Darah (Mm/hg)</label>
                        <input type="text" name="tekanan_darah" id="tekanan_darah" value="{{ old('tekanan_darah') }}" placeholder="Contoh: 120/80" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>
                    <div>
                        <label for="golongan_darah" class="block mb-2 text-sm font-medium text-gray-700">Golongan Darah</label>
                        <input type="text" name="golongan_darah" id="golongan_darah" value="{{ old('golongan_darah') }}" placeholder="Contoh: B+" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>
                    <div>
                        <label for="buta_warna" class="block mb-2 text-sm font-medium text-gray-700">Buta Warna</label>
                        <x-form-select name="buta_warna" id="buta_warna" :value="old('buta_warna', 'Tidak')" :options="[
                            'Tidak' => 'Tidak',
                            'Parsial' => 'Parsial',
                            'Ya' => 'Ya'
                        ]" />
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 4: Riwayat Medis -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 mb-8">
            <div class="border-b border-gray-100 bg-gray-50/50 px-8 py-5">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    Riwayat Medis
                </h3>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="riwayat_penyakit_kronis" class="block mb-2 text-sm font-medium text-gray-700">Penyakit Kronis</label>
                        <input type="text" name="riwayat_penyakit_kronis" id="riwayat_penyakit_kronis" value="{{ old('riwayat_penyakit_kronis', 'Disangkal') }}" placeholder="Ya/Tidak/Disangkal" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>
                    <div>
                        <label for="riwayat_penggunaan_obat" class="block mb-2 text-sm font-medium text-gray-700">Penggunaan Obat</label>
                        <input type="text" name="riwayat_penggunaan_obat" id="riwayat_penggunaan_obat" value="{{ old('riwayat_penggunaan_obat', 'Disangkal') }}" placeholder="Ya/Tidak/Disangkal" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>
                    <div>
                        <label for="riwayat_alergi" class="block mb-2 text-sm font-medium text-gray-700">Alergi</label>
                        <input type="text" name="riwayat_alergi" id="riwayat_alergi" value="{{ old('riwayat_alergi', 'Disangkal') }}" placeholder="Ketik alergi..." class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition duration-150">
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-4 mb-16">
            <a href="{{ route('pemeriksaan.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 bg-white rounded-xl shadow-sm hover:bg-gray-50 font-bold text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition duration-150">
                Batal
            </a>
            <button type="submit" class="px-8 py-3 bg-emerald-700 text-white rounded-xl shadow-md hover:bg-emerald-800 font-bold text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600 transition duration-150 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Simpan Data
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function updateDokterNip() {
        const input = document.getElementById('dokter_nama');
        const wrapper = input ? input.closest('.custom-select-wrapper') : null;
        const selectedOpt = wrapper ? wrapper.querySelector('.custom-select-option[aria-selected="true"]') : null;
        const nipInput = document.getElementById('dokter_nip');
        if (nipInput) {
            nipInput.value = selectedOpt ? (selectedOpt.getAttribute('data-nip') || '') : '';
        }
    }

    function calculateAge() {
        const dobInput = document.getElementById('tanggal_lahir').value;
        if (dobInput) {
            const dob = new Date(dobInput);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            document.getElementById('umur').value = age;
        } else {
            document.getElementById('umur').value = '';
        }
    }
</script>
@endpush
