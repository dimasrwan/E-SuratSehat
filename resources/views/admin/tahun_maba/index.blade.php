@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Page Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex text-xs font-medium text-slate-500 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span class="mx-2 text-slate-300">/</span>
                <span class="text-slate-800 font-semibold">Manajemen Tahun Maba</span>
            </nav>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Tahun Maba</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola master angkatan mahasiswa baru dan tentukan tahun aktif kerja operator.</p>
        </div>
        <div>
            <button type="button" onclick="document.getElementById('modal-tambah-tahun').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-sm transition-colors">
                <span>+ Tambah Tahun Maba</span>
            </button>
        </div>
    </div>

    <!-- Alert Success / Error -->
    @if (session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl shadow-sm flex items-center justify-between" role="alert">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs font-semibold text-emerald-900">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl shadow-sm flex items-center justify-between" role="alert">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-red-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs font-semibold text-red-900">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Data Table Card -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Daftar Angkatan Tahun Maba</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Hanya satu tahun Maba yang dapat berstatus aktif dalam satu waktu.</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-semibold text-slate-500">Tahun Aktif Operator:</span>
                <span class="ml-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-lg text-xs font-bold">Maba {{ $activeYearInt }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Tahun</th>
                        <th class="py-3.5 px-6">Nama Angkatan</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6">Dibuat Pada</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($tahunMabas as $tm)
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $tm->is_active ? 'bg-emerald-50/30' : '' }}">
                            <td class="py-4 px-6 font-extrabold text-slate-900 text-sm">
                                {{ $tm->tahun }}
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-700">
                                {{ $tm->nama }}
                            </td>
                            <td class="py-4 px-6">
                                @if ($tm->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        AKTIF
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                        NONAKTIF
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-500">
                                {{ $tm->created_at ? $tm->created_at->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                @if ($tm->is_active)
                                    <button disabled class="px-3.5 py-1.5 bg-slate-100 text-slate-400 font-semibold rounded-lg text-xs cursor-not-allowed border border-slate-200">
                                        Sedang Aktif
                                    </button>
                                @else
                                    <button type="button" onclick="confirmActivation('{{ $tm->id }}', '{{ $tm->tahun }}')" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition-colors shadow-xs">
                                        Aktifkan
                                    </button>
                                    <form id="form-activate-{{ $tm->id }}" action="{{ route('admin.tahun-maba.activate', $tm->id) }}" method="POST" class="hidden">
                                        @csrf
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-6 text-center text-slate-400 italic">
                                Belum ada data Tahun Maba.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Tahun Maba -->
<div id="modal-tambah-tahun" class="hidden fixed inset-0 z-[99999] overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-5 transform transition-all">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tambah Tahun Maba Baru</h3>
            <button type="button" onclick="document.getElementById('modal-tambah-tahun').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.tahun-maba.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="tahun" class="block text-xs font-semibold text-slate-700 mb-1">Tahun Maba (Angkatan)</label>
                <input type="number" name="tahun" id="tahun" required min="2000" max="{{ date('Y') + 10 }}" placeholder="Contoh: 2027" class="w-full h-11 px-3.5 rounded-xl border border-slate-300 text-sm font-bold text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                <p class="text-[11px] text-slate-500 mt-1">Masukkan 4 digit tahun (misal: 2027). Status default baru adalah Nonaktif.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-tambah-tahun').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-xs transition-colors">
                    Simpan Tahun
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Confirmation Activation -->
<div id="modal-confirm-activate" class="hidden fixed inset-0 z-[99999] overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4 transform transition-all">
        <div class="flex items-center gap-3 text-amber-600">
            <div class="p-2.5 bg-amber-100 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Konfirmasi Perubahan Tahun Aktif</h3>
            </div>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed">
            Aktifkan <span id="confirm-year-text" class="font-bold text-emerald-800"></span>?
            <br><br>
            Setelah diaktifkan, seluruh operator akan otomatis bekerja pada data mahasiswa angkatan tersebut. Data angkatan sebelumnya tetap tersimpan aman di database.
        </p>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <button type="button" onclick="document.getElementById('modal-confirm-activate').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors">
                Batal
            </button>
            <button type="button" id="btn-submit-activate" onclick="executeActivation()" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-xs transition-colors">
                Aktifkan Sekarang
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let pendingFormId = null;

    function confirmActivation(id, tahun) {
        pendingFormId = 'form-activate-' + id;
        document.getElementById('confirm-year-text').innerText = 'Maba ' + tahun;
        document.getElementById('modal-confirm-activate').classList.remove('hidden');
    }

    function executeActivation() {
        if (pendingFormId) {
            document.getElementById(pendingFormId).submit();
        }
    }
</script>
@endpush
@endsection
