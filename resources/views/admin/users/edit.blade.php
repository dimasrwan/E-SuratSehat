@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 tracking-tight">Edit Data Pengguna</h2>
            <p class="text-sm text-gray-500">Perbarui data atau status pengguna: <span class="font-bold text-emerald-800">{{ $user->name }}</span></p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg transition">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white shadow-md rounded-xl p-6 border border-gray-100">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Alamat Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-amber-50 border border-amber-200 p-4 rounded-lg">
                    <p class="text-xs text-amber-800 font-semibold mb-3">Kosongkan password jika tidak ingin mengubah password user.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Password Baru (Opsional)</label>
                            <input type="password" name="password" id="password"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('password') border-red-500 @enderror">
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="role" class="block text-sm font-bold text-gray-700 mb-1">Role Pengguna</label>
                        <select name="role" id="role" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('role') border-red-500 @enderror">
                            <option value="operator" {{ old('role', $user->role) === 'operator' ? 'selected' : '' }}>Operator</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="is_active" class="block text-sm font-bold text-gray-700 mb-1">Status Akun</label>
                        <select name="is_active" id="is_active" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('is_active') border-red-500 @enderror">
                            <option value="1" {{ old('is_active', $user->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('is_active', $user->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('is_active')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-gray-100">
                    <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-sm transition">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-lg text-sm transition shadow-sm">
                        Perbarui Pengguna
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
