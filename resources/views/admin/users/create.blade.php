@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Page Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex text-xs font-medium text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('admin.users.index') }}" class="hover:text-emerald-700 transition-colors">Manajemen Pengguna</a>
                <span class="mx-2 text-slate-300">/</span>
                <span class="text-slate-800 font-semibold">Tambah Pengguna</span>
            </nav>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Tambah Pengguna Baru</h2>
            <p class="text-xs text-slate-500 mt-0.5">Buat akun administrator atau operator Klinik UIN Ar-Raniry.</p>
        </div>
        <div>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-lg shadow-sm transition-colors">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Form Container Card -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6 sm:p-8">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-7">
            @csrf

            <!-- Form Header -->
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">Informasi Akun</h3>
                <p class="text-xs text-slate-500 mt-0.5">Isi informasi berikut untuk membuat akun pengguna baru.</p>
            </div>

            <!-- Group 1: Informasi Pengguna -->
            <div class="space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-800">Informasi Pengguna</h4>
                
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        placeholder="Masukkan nama lengkap pengguna"
                        class="w-full h-11 px-3.5 rounded-lg border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 transition-colors @error('name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('name')
                        <p class="text-red-600 text-xs font-medium mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        placeholder="contoh: user@klinik.uin.ac.id"
                        class="w-full h-11 px-3.5 rounded-lg border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 transition-colors @error('email') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('email')
                        <p class="text-red-600 text-xs font-medium mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Group 2: Keamanan Akun -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-800">Keamanan Akun</h4>
                    <span class="text-[11px] text-slate-500">Gunakan password yang kuat dan tidak mudah ditebak.</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">Password</label>
                        <input type="password" name="password" id="password" required
                            placeholder="••••••••"
                            class="w-full h-11 px-3.5 rounded-lg border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 transition-colors @error('password') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('password')
                            <p class="text-red-600 text-xs font-medium mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            placeholder="••••••••"
                            class="w-full h-11 px-3.5 rounded-lg border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 transition-colors">
                    </div>
                </div>
            </div>

            <!-- Group 3: Hak Akses -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-800">Hak Akses</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="role" class="block text-xs font-semibold text-slate-700 mb-1.5">Role Pengguna</label>
                        <x-form-select name="role" id="role" :value="old('role', 'operator')" required :options="[
                            'operator' => 'Operator',
                            'admin' => 'Administrator'
                        ]" />
                        <p class="text-[11px] text-slate-500 mt-1.5">Role menentukan hak akses pengguna di dalam sistem.</p>
                        @error('role')
                            <p class="text-red-600 text-xs font-medium mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="is_active" class="block text-xs font-semibold text-slate-700 mb-1.5">Status Akun</label>
                        <x-form-select name="is_active" id="is_active" :value="old('is_active', '1')" required :options="[
                            '1' => 'Aktif',
                            '0' => 'Nonaktif'
                        ]" />
                        <p class="text-[11px] text-slate-500 mt-1.5">Akun nonaktif tidak dapat digunakan untuk login.</p>
                        @error('is_active')
                            <p class="text-red-600 text-xs font-medium mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Info Box: Catatan -->
            <div class="bg-emerald-50/70 border border-emerald-200/80 p-4 rounded-xl text-xs text-emerald-950 space-y-1">
                <div class="font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Catatan</span>
                </div>
                <p class="text-emerald-900 leading-relaxed">
                    Akun hanya dapat dibuat oleh administrator. Pengguna dengan status nonaktif tidak dapat masuk ke sistem.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 flex items-center justify-between border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition-colors">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-1.5 px-6 py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white font-semibold text-xs rounded-lg shadow-sm transition-colors">
                    <span>Simpan Pengguna</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
