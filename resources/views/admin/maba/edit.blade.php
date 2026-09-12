@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Data Maba</h1>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui data biodata mahasiswa dengan informasi yang benar.</p>
        </div>
        <a href="{{ route('maba.show', $mabaData) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition duration-150">
            Batal
        </a>
    </div>

    <!-- Informational Banner -->
    @if(in_array($mabaData->status_biodata, ['TERVERIFIKASI', 'PEMERIKSAAN_SELESAI']))
    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs font-semibold flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
            <strong class="font-extrabold text-amber-950 block mb-0.5">Perhatian Konfirmasi Perubahan Data:</strong>
            <p class="text-amber-900 leading-relaxed">Perubahan data akan membuat status verifikasi perlu diperiksa kembali (dikembalikan ke <strong>Menunggu Verifikasi</strong>). Record pemeriksaan medis dan nomor surat tetap aman dan tidak akan terhapus.</p>
        </div>
    </div>
    @elseif($mabaData->status_biodata === 'PERLU_PERBAIKAN')
    <div class="p-4 rounded-2xl bg-blue-50 border border-blue-300 text-blue-900 text-xs font-semibold flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
            <strong class="font-extrabold text-blue-950 block mb-0.5">Penyelesaian Perbaikan Biodata:</strong>
            <p class="text-blue-900 leading-relaxed">Setelah data diperbaiki oleh petugas, status akan kembali ke <strong>Menunggu Verifikasi</strong> dan catatan perbaikan aktif akan dibersihkan.</p>
        </div>
    </div>
    @endif

    <!-- Validation Errors -->
    @if ($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-xs font-semibold">
        <strong class="font-extrabold block mb-1">Terdapat kesalahan pengisian form:</strong>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Form Edit -->
    <form action="{{ route('maba.update', $mabaData) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- SECTION 1: DATA BIODATA (EDITABLE) -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-4">
            <h3 class="font-extrabold text-slate-800 text-sm border-b border-slate-100 pb-3 flex items-center justify-between">
                <span>DATA BIODATA</span>
                <span class="text-xs text-slate-400 font-medium">Field dapat diperbaiki oleh Admin/Operator</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <!-- Nama Lengkap -->
                <div class="space-y-1 sm:col-span-2">
                    <label for="nama_lengkap" class="block font-bold text-slate-700">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" value="{{ old('nama_lengkap', $mabaData->nama_lengkap ?? $mabaData->nama_biro) }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- NIK -->
                <div class="space-y-1">
                    <label for="nik" class="block font-bold text-slate-700">NIK (16 Digit) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nik" id="nik" maxlength="16" value="{{ old('nik', $mabaData->nik) }}" required font-mono class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Email Mahasiswa -->
                <div class="space-y-1">
                    <label for="email" class="block font-bold text-slate-700">Email Mahasiswa <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $mabaData->email) }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Tempat Lahir -->
                <div class="space-y-1">
                    <label for="tempat_lahir" class="block font-bold text-slate-700">Tempat Lahir <span class="text-rose-500">*</span></label>
                    <input type="text" name="tempat_lahir" id="tempat_lahir" value="{{ old('tempat_lahir', $mabaData->tempat_lahir) }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Tanggal Lahir & Umur -->
                <div class="space-y-1">
                    <div class="flex justify-between items-center">
                        <label for="tanggal_lahir" class="block font-bold text-slate-700">Tanggal Lahir <span class="text-rose-500">*</span></label>
                        @if($mabaData->umur)
                        <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">Umur: {{ $mabaData->umur }} tahun</span>
                        @endif
                    </div>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="{{ old('tanggal_lahir', $mabaData->tanggal_lahir ? $mabaData->tanggal_lahir->format('Y-m-d') : '') }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Jenis Kelamin -->
                <div class="space-y-1">
                    <label for="jenis_kelamin" class="block font-bold text-slate-700">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="jenis_kelamin" id="jenis_kelamin" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        @foreach($jkOptions as $jk)
                            @php
                                $valMapped = ($jk === 'Laki-laki') ? 'L' : 'P';
                                $currentJk = old('jenis_kelamin', $mabaData->jenis_kelamin);
                                $isSelected = ($currentJk === $jk || $currentJk === $valMapped);
                            @endphp
                            <option value="{{ $jk }}" {{ $isSelected ? 'selected' : '' }}>{{ $jk }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Agama -->
                <div class="space-y-1">
                    <label for="agama" class="block font-bold text-slate-700">Agama <span class="text-rose-500">*</span></label>
                    <select name="agama" id="agama" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Agama --</option>
                        @foreach($agamaOptions as $ag)
                            <option value="{{ $ag }}" {{ old('agama', $mabaData->agama) === $ag ? 'selected' : '' }}>{{ $ag }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Fakultas Dropdown -->
                <div class="space-y-1">
                    <label for="fakultas" class="block font-bold text-slate-700">Fakultas <span class="text-rose-500">*</span></label>
                    <select name="fakultas" id="fakultas" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Fakultas --</option>
                        @foreach($fakultasOptions as $fak)
                            <option value="{{ $fak }}" {{ old('fakultas', $mabaData->fakultas) === $fak ? 'selected' : '' }}>{{ $fak }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Program Studi (READONLY) -->
                <div class="space-y-1">
                    <label class="block font-bold text-slate-500">Program Studi (Data Biro - Readonly)</label>
                    <input type="text" value="{{ $mabaData->program_studi_biro }}" readonly class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-100 text-slate-600 cursor-not-allowed font-semibold">
                </div>

                <!-- Pekerjaan -->
                <div class="space-y-1 sm:col-span-2">
                    <label for="pekerjaan" class="block font-bold text-slate-700">Pekerjaan <span class="text-rose-500">*</span></label>
                    <input type="text" name="pekerjaan" id="pekerjaan" value="{{ old('pekerjaan', $mabaData->pekerjaan ?? 'Mahasiswa') }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Alamat Lengkap -->
                <div class="space-y-1 sm:col-span-2">
                    <label for="alamat" class="block font-bold text-slate-700">Alamat Lengkap <span class="text-rose-500">*</span></label>
                    <textarea name="alamat" id="alamat" rows="3" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('alamat', $mabaData->alamat) }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 2: DATA JADWAL & BIRO (READONLY SERVER-OWNED) -->
        <div class="bg-slate-50 p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-4">
            <h3 class="font-extrabold text-slate-600 text-sm border-b border-slate-200 pb-3 flex items-center justify-between">
                <span>DATA JADWAL & BIRO (SERVER-OWNED READONLY)</span>
                <span class="text-xs text-slate-400 font-medium">Field ini tidak dapat diubah dari form edit</span>
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-500 block font-medium">Nama Biro:</span>
                    <span class="font-bold text-slate-800">{{ $mabaData->nama_biro }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block font-medium">Program Studi Biro:</span>
                    <span class="font-bold text-emerald-800">{{ $mabaData->program_studi_biro }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block font-medium">Tanggal & Sesi Jadwal:</span>
                    <span class="font-bold text-slate-800">{{ $mabaData->tanggal_jadwal ? $mabaData->tanggal_jadwal->format('d M Y') : '-' }} (Sesi {{ $mabaData->sesi_jadwal ?? '-' }})</span>
                </div>
                <div>
                    <span class="text-slate-500 block font-medium">Status Biodata Saat Ini:</span>
                    <span class="font-bold text-slate-800">{{ $mabaData->status_biodata }}</span>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('maba.show', $mabaData) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition duration-150">
                Batal
            </a>
            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menyimpan perubahan data biodata ini?');" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition duration-150 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
