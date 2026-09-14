@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-2xs">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Master Fakultas & Program Studi</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola daftar fakultas dan program studi yang digunakan dalam sistem.</p>
        </div>
        <div>
            <button type="button" onclick="openAddFakultasModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-2xs transition duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Fakultas
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 rounded-lg text-xs sm:text-sm font-medium flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3 rounded-lg text-xs sm:text-sm font-medium flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3 rounded-lg text-xs sm:text-sm font-medium space-y-1 shadow-2xs">
            <div class="font-bold text-rose-950">Terjadi kesalahan input:</div>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-800">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Search & Filter Card -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <form method="GET" action="{{ route('admin.fakultas-prodi.index') }}" class="flex flex-col sm:flex-row gap-3 items-center">
            <div class="relative flex-grow w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari fakultas atau program studi..." class="block w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
            </div>

            <div class="w-full sm:w-48">
                <x-form-select 
                    name="status" 
                    :options="[
                        'all' => 'Semua Status',
                        'active' => 'Aktif',
                        'inactive' => 'Nonaktif'
                    ]" 
                    :value="$status" 
                    onchange="this.form.submit()" 
                />
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-lg shadow-2xs transition">
                    Filter
                </button>

                @if(!empty($search) || $status !== 'all')
                    <a href="{{ route('admin.fakultas-prodi.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Table Container -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-[11px] font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Fakultas</th>
                        <th class="py-3 px-4">Kode</th>
                        <th class="py-3 px-4">Program Studi</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($fakultas as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $item->nama }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-600">
                                <span class="px-2 py-0.5 bg-slate-100 border border-slate-200 rounded font-mono text-[11px]">
                                    {{ $item->kode }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">
                                {{ $item->programStudi->count() }} Prodi
                            </td>
                            <td class="py-3.5 px-4">
                                @if($item->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" onclick="toggleProdiSection({{ $item->id }})" class="px-2.5 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 rounded-md font-semibold text-xs transition">
                                        Kelola
                                    </button>
                                    <button type="button" onclick="openEditFakultasModal({{ json_encode($item) }})" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md font-semibold text-xs transition">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.fakultas-prodi.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Fakultas {{ $item->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-md font-semibold text-xs transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Expanded Accordion Row for Program Studi -->
                        <tr id="prodi-section-{{ $item->id }}" class="hidden bg-slate-50/50">
                            <td colspan="5" class="p-4 sm:p-5 border-t border-b border-slate-200">
                                <div class="bg-white p-4 rounded-lg border border-slate-200 space-y-4 shadow-2xs">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                                        <div>
                                            <h3 class="text-sm font-bold text-slate-900">Program Studi — {{ $item->nama }} ({{ $item->kode }})</h3>
                                            <p class="text-[11px] text-slate-500">Daftar seluruh program studi di bawah fakultas ini.</p>
                                        </div>
                                        <div>
                                            <button type="button" onclick="openAddProdiModal({{ $item->id }}, '{{ addslashes($item->nama) }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-md shadow-2xs transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                Tambah Program Studi
                                            </button>
                                        </div>
                                    </div>

                                    @if($item->programStudi->isEmpty())
                                        <div class="text-center py-6 text-xs text-slate-400 italic">
                                            Belum ada program studi pada fakultas ini.
                                        </div>
                                    @else
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left border-collapse">
                                                <thead>
                                                    <tr class="text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
                                                        <th class="py-2 px-3">Nama Program Studi</th>
                                                        <th class="py-2 px-3">Status</th>
                                                        <th class="py-2 px-3 text-right">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 text-xs">
                                                    @foreach($item->programStudi as $prodi)
                                                        <tr class="hover:bg-slate-50/80 transition">
                                                            <td class="py-2.5 px-3 font-semibold text-slate-800">
                                                                {{ $prodi->nama }}
                                                            </td>
                                                            <td class="py-2.5 px-3">
                                                                @if($prodi->is_active)
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                                        Aktif
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                                                        Nonaktif
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="py-2.5 px-3 text-right">
                                                                <div class="inline-flex items-center gap-1.5">
                                                                    <button type="button" onclick="openEditProdiModal({{ json_encode($prodi) }})" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded font-semibold text-[11px] transition">
                                                                        Edit
                                                                    </button>
                                                                    <form action="{{ route('admin.program-studi.destroy', $prodi) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Program Studi {{ $prodi->nama }}?')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="px-2 py-0.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded font-semibold text-[11px] transition">
                                                                            Hapus
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-xs text-slate-400">
                                Tidak ada data fakultas yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Fakultas -->
<div id="addFakultasModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tambah Fakultas Master</h3>
            <button type="button" onclick="closeAddFakultasModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="{{ route('admin.fakultas-prodi.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Fakultas <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" required placeholder="Contoh: Fakultas Kedokteran" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kode Fakultas <span class="text-rose-500">*</span></label>
                <input type="text" name="kode" required placeholder="Contoh: FK" class="w-full px-3 py-2 text-xs uppercase bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="add_f_active" value="1" checked class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500">
                <label for="add_f_active" class="text-xs font-semibold text-slate-700 cursor-pointer">Status Aktif</label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeAddFakultasModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-2xs transition">Simpan Fakultas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Fakultas -->
<div id="editFakultasModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Edit Fakultas Master</h3>
            <button type="button" onclick="closeEditFakultasModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="editFakultasForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Fakultas <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_f_nama" name="nama" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kode Fakultas <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_f_kode" name="kode" required class="w-full px-3 py-2 text-xs uppercase bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="edit_f_active" value="1" class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500">
                <label for="edit_f_active" class="text-xs font-semibold text-slate-700 cursor-pointer">Status Aktif</label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditFakultasModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-2xs transition">Perbarui Fakultas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Program Studi -->
<div id="addProdiModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-slate-900">Tambah Program Studi</h3>
                <p id="add_p_fakultas_name" class="text-xs font-semibold text-emerald-700 mt-0.5"></p>
            </div>
            <button type="button" onclick="closeAddProdiModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="addProdiForm" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Program Studi <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" required placeholder="Contoh: Pendidikan Agama Islam" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="add_p_active" value="1" checked class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500">
                <label for="add_p_active" class="text-xs font-semibold text-slate-700 cursor-pointer">Status Aktif</label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeAddProdiModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-2xs transition">Simpan Program Studi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Program Studi -->
<div id="editProdiModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Edit Program Studi</h3>
            <button type="button" onclick="closeEditProdiModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="editProdiForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Fakultas <span class="text-rose-500">*</span></label>
                <select id="edit_p_fakultas_id" name="fakultas_id" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
                    @foreach($fakultas as $f)
                        <option value="{{ $f->id }}">{{ $f->nama }} ({{ $f->kode }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Program Studi <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_p_nama" name="nama" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="edit_p_active" value="1" class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500">
                <label for="edit_p_active" class="text-xs font-semibold text-slate-700 cursor-pointer">Status Aktif</label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditProdiModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-2xs transition">Perbarui Program Studi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleProdiSection(fakultasId) {
        const el = document.getElementById('prodi-section-' + fakultasId);
        if (el) {
            el.classList.toggle('hidden');
        }
    }

    // Modal Fakultas Handlers
    function openAddFakultasModal() {
        document.getElementById('addFakultasModal').classList.remove('hidden');
    }
    function closeAddFakultasModal() {
        document.getElementById('addFakultasModal').classList.add('hidden');
    }

    function openEditFakultasModal(fakultas) {
        const form = document.getElementById('editFakultasForm');
        form.action = '/admin/fakultas-prodi/' + fakultas.id;
        document.getElementById('edit_f_nama').value = fakultas.nama;
        document.getElementById('edit_f_kode').value = fakultas.kode;
        document.getElementById('edit_f_active').checked = Boolean(fakultas.is_active);
        document.getElementById('editFakultasModal').classList.remove('hidden');
    }
    function closeEditFakultasModal() {
        document.getElementById('editFakultasModal').classList.add('hidden');
    }

    // Modal Program Studi Handlers
    function openAddProdiModal(fakultasId, fakultasNama) {
        const form = document.getElementById('addProdiForm');
        form.action = '/admin/fakultas-prodi/' + fakultasId + '/program-studi';
        document.getElementById('add_p_fakultas_name').textContent = 'Fakultas: ' + fakultasNama;
        document.getElementById('addProdiModal').classList.remove('hidden');
    }
    function closeAddProdiModal() {
        document.getElementById('addProdiModal').classList.add('hidden');
    }

    function openEditProdiModal(prodi) {
        const form = document.getElementById('editProdiForm');
        form.action = '/admin/program-studi/' + prodi.id;
        document.getElementById('edit_p_fakultas_id').value = prodi.fakultas_id;
        document.getElementById('edit_p_nama').value = prodi.nama;
        document.getElementById('edit_p_active').checked = Boolean(prodi.is_active);
        document.getElementById('editProdiModal').classList.remove('hidden');
    }
    function closeEditProdiModal() {
        document.getElementById('editProdiModal').classList.add('hidden');
    }
</script>
@endsection
