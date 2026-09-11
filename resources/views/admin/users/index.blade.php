@extends('layouts.app')

@section('content')
<div>
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 tracking-tight">Manajemen Pengguna</h2>
            <p class="text-sm text-gray-500">Kelola akun administrator dan operator klinik</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-5 py-2.5 bg-emerald-700 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wide shadow-sm hover:bg-emerald-800 transition">
            + Tambah Pengguna
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm font-semibold flex items-center justify-between">
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm font-semibold flex items-center justify-between">
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-md sm:rounded-xl border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-emerald-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-emerald-800 uppercase tracking-wider">Dibuat Pada</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-emerald-800 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($users as $u)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $u->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $u->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($u->role === 'admin')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        Admin
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Operator
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($u->is_active)
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $u->created_at ? $u->created_at->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $u->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold px-2 py-1 bg-indigo-50 rounded">
                                        Edit
                                    </a>
                                    
                                    @if($u->id !== Auth::id())
                                    <form action="{{ route('admin.users.toggleStatus', $u->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="font-semibold px-2 py-1 rounded {{ $u->is_active ? 'text-amber-700 bg-amber-50 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">
                                            {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-semibold px-2 py-1 bg-red-50 rounded">
                                            Hapus
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 whitespace-nowrap text-sm text-center text-gray-500 italic">
                                Belum ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
