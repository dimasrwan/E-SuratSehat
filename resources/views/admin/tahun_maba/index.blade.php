@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- 1. Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex text-[11px] font-medium text-slate-400 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span class="mx-1.5 text-slate-300">/</span>
                <span class="text-slate-700 font-medium">Manajemen Tahun Maba</span>
            </nav>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Tahun Maba</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola master angkatan mahasiswa baru dan tentukan tahun aktif kerja operator.</p>
        </div>
        <div class="flex items-center">
            <button type="button" onclick="document.getElementById('modal-tambah-tahun').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 h-11 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Tahun Maba</span>
            </button>
        </div>
    </div>

    <!-- Alert Success / Error -->
    @if (session('success'))
        <div class="bg-emerald-50/90 border border-emerald-200/80 p-4 rounded-xl shadow-xs flex items-center justify-between" role="alert">
            <div class="flex items-center gap-3">
                <div class="p-1 bg-emerald-500 text-white rounded-lg">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-emerald-950">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50/90 border border-red-200/80 p-4 rounded-xl shadow-xs flex items-center justify-between" role="alert">
            <div class="flex items-center gap-3">
                <div class="p-1 bg-red-500 text-white rounded-lg">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-red-950">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- 2. Card Utama & Information Helper -->
    <div class="bg-white border border-slate-200 shadow-xs rounded-xl overflow-hidden">
        <!-- Header Card -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/40">
            <div>
                <h3 class="text-sm font-bold text-slate-900 tracking-tight">Daftar Angkatan Tahun Maba</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Hanya satu tahun Maba yang dapat berstatus aktif dalam satu waktu.</p>
            </div>
            <div class="flex items-center gap-2 bg-white px-3 py-1.5 border border-slate-200 rounded-lg shadow-2xs">
                <span class="text-[11px] font-medium text-slate-500">Tahun Aktif Operator:</span>
                <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/80">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Maba {{ $activeYearInt }}
                </span>
            </div>
        </div>

        <!-- 3. Tabel Master -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Tahun & Angkatan</th>
                        <th class="py-3.5 px-4">Nomor Surat Mulai</th>
                        <th class="py-3.5 px-4">Kode Unit</th>
                        <th class="py-3.5 px-4">Kode Bagian</th>
                        <th class="py-3.5 px-4">Tahun Surat</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Dibuat Pada</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($tahunMabas as $tm)
                        <!-- 10. Indikator Row Aktif subtle -->
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $tm->is_active ? 'bg-emerald-50/20' : '' }}">
                            <!-- 4. Tahun & Nama -->
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-2">
                                    @if ($tm->is_active)
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0" title="Tahun Aktif"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-slate-200 flex-shrink-0"></span>
                                    @endif
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm leading-tight">{{ $tm->tahun }}</div>
                                        <div class="text-[11px] font-medium text-slate-500 leading-tight mt-0.5">{{ $tm->nama }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 5. Nomor Surat Mulai -->
                            <td class="py-4 px-4 font-mono">
                                <div class="font-bold text-emerald-800 text-sm tracking-tight">{{ sprintf('%04d', $tm->nomor_surat_mulai ?? 172) }}</div>
                                <div class="text-[10px] text-slate-400 font-sans mt-0.5">Nomor Awal</div>
                            </td>

                            <!-- 6. Kode Unit & Kode Bagian -->
                            <td class="py-4 px-4 font-mono">
                                <span class="inline-block px-2 py-1 bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-md text-[11px]">
                                    {{ $tm->kode_unit ?? 'Un.08' }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-mono">
                                <span class="inline-block px-2 py-1 bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-md text-[11px]">
                                    {{ $tm->kode_bagian ?? 'PPKES' }}
                                </span>
                            </td>

                            <!-- Tahun Surat -->
                            <td class="py-4 px-4 font-mono font-semibold text-slate-700">
                                {{ $tm->tahun_surat ?? $tm->tahun }}
                            </td>

                            <!-- 7. Status Badge -->
                            <td class="py-4 px-4">
                                @if ($tm->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        AKTIF
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        NONAKTIF
                                    </span>
                                @endif
                            </td>

                            <!-- 8. Dibuat Pada (2 baris compact) -->
                            <td class="py-4 px-4 text-slate-500 leading-tight text-[11px]">
                                <div>{{ $tm->created_at ? $tm->created_at->format('d M Y') : '-' }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $tm->created_at ? $tm->created_at->format('H:i') : '' }}</div>
                            </td>

                            <!-- 9. Action Area Compact -->
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <button type="button" onclick="openEditFormatModal('{{ $tm->id }}', '{{ $tm->tahun }}', '{{ $tm->nomor_surat_mulai ?? 172 }}', '{{ $tm->kode_unit ?? 'Un.08' }}', '{{ $tm->kode_bagian ?? 'PPKES' }}', '{{ $tm->tahun_surat ?? $tm->tahun }}')" class="h-10 px-3.5 bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-xl text-xs transition-colors border border-slate-200 shadow-2xs inline-flex items-center justify-center gap-1.5 min-w-[105px]">
                                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        <span>Edit Format</span>
                                    </button>

                                    @if ($tm->is_active)
                                        <span class="h-10 px-4 bg-slate-100/80 text-slate-500 font-semibold rounded-xl text-xs border border-slate-200/90 cursor-not-allowed inline-flex items-center justify-center min-w-[95px] select-none">
                                            Tahun Aktif
                                        </span>
                                    @else
                                        <button type="button" onclick="confirmActivation('{{ $tm->id }}', '{{ $tm->tahun }}')" class="h-10 px-4 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-xl text-xs transition-colors shadow-2xs inline-flex items-center justify-center min-w-[95px]">
                                            Aktifkan
                                        </button>
                                        <form id="form-activate-{{ $tm->id }}" action="{{ route('admin.tahun-maba.activate', $tm->id) }}" method="POST" class="hidden">
                                            @csrf
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-6 text-center text-slate-400 italic">
                                Belum ada data Tahun Maba. Silakan klik "+ Tambah Tahun Maba" untuk membuat angkatan baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 13. Modal Edit Format Nomor Surat -->
<div id="modal-edit-nomor" class="hidden fixed inset-0 z-[99999] overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-5 transform transition-all">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
            <div>
                <h3 class="text-base font-bold text-slate-900">Pengaturan Nomor Surat</h3>
                <p class="text-xs text-slate-500 font-medium" id="edit-nomor-subtitle">Maba 2027</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-edit-nomor').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form id="form-edit-nomor" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <!-- Grouping 1: Penomoran -->
            <div class="space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Penomoran</span>
                <div>
                    <label for="edit_nomor_surat_mulai" class="block text-xs font-semibold text-slate-700 mb-1">Nomor Surat Mulai</label>
                    <input type="number" name="nomor_surat_mulai" id="edit_nomor_surat_mulai" required min="1" max="99999" placeholder="5000" oninput="updateEditPreview()" class="w-full h-10 px-3 rounded-xl border border-slate-300 text-sm font-bold text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                    <p class="text-[11px] text-slate-500 mt-1">Batas awal penomoran otomatis untuk angkatan ini.</p>
                </div>
            </div>

            <!-- Grouping 2: Format Surat -->
            <div class="space-y-3 pt-1 border-t border-slate-100">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Format Surat</span>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="edit_kode_unit" class="block text-xs font-semibold text-slate-700 mb-1">Kode Unit</label>
                        <input type="text" name="kode_unit" id="edit_kode_unit" required max="50" placeholder="Un.08" oninput="updateEditPreview()" class="w-full h-10 px-3 rounded-xl border border-slate-300 text-sm font-bold text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                    </div>
                    <div>
                        <label for="edit_kode_bagian" class="block text-xs font-semibold text-slate-700 mb-1">Kode Bagian</label>
                        <input type="text" name="kode_bagian" id="edit_kode_bagian" required max="50" placeholder="PPKES" oninput="updateEditPreview()" class="w-full h-10 px-3 rounded-xl border border-slate-300 text-sm font-bold text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                    </div>
                </div>

                <div>
                    <label for="edit_tahun_surat" class="block text-xs font-semibold text-slate-700 mb-1">Tahun Surat</label>
                    <input type="number" name="tahun_surat" id="edit_tahun_surat" required min="2000" max="2100" placeholder="2027" oninput="updateEditPreview()" class="w-full h-10 px-3 rounded-xl border border-slate-300 text-sm font-bold text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                    <p class="text-[11px] text-slate-500 mt-1">Tahun pada bagian akhir nomor surat.</p>
                </div>
            </div>

            <!-- Grouping 3: Preview Box -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PREVIEW NOMOR SURAT</span>
                <div class="font-mono text-base font-bold text-emerald-800 tracking-tight" id="edit-format-preview">
                    5000/Un.08/PPKES/09/2027
                </div>
                <p class="text-[11px] text-slate-500 italic leading-relaxed">
                    Contoh nomor surat yang akan digunakan sistem.
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-edit-nomor').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-2xs transition-colors">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 12. Modal Tambah Tahun Maba -->
<div id="modal-tambah-tahun" class="hidden fixed inset-0 z-[99999] overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-5 transform transition-all">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
            <h3 class="text-base font-bold text-slate-900">Tambah Tahun Maba Baru</h3>
            <button type="button" onclick="document.getElementById('modal-tambah-tahun').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.tahun-maba.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="tahun" class="block text-xs font-semibold text-slate-700 mb-1">Tahun Maba (Angkatan)</label>
                <input type="number" name="tahun" id="tahun" required min="2000" max="{{ date('Y') + 10 }}" placeholder="Contoh: 2027" oninput="syncTahunSuratDefault(); updateAddPreview();" class="w-full h-10 px-3 rounded-xl border border-slate-300 text-sm font-bold text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                <p class="text-[11px] text-slate-500 mt-1">Masukkan 4 digit tahun (misal: 2027). Status default baru adalah Nonaktif.</p>
            </div>

            <div>
                <label for="nomor_surat_mulai" class="block text-xs font-semibold text-slate-700 mb-1">Nomor Surat Mulai</label>
                <input type="number" name="nomor_surat_mulai" id="nomor_surat_mulai" value="172" required min="1" max="99999" placeholder="5000" oninput="updateAddPreview()" class="w-full h-10 px-3 rounded-xl border border-slate-300 text-sm font-bold text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="add_kode_unit" class="block text-xs font-semibold text-slate-700 mb-1">Kode Unit</label>
                    <input type="text" name="kode_unit" id="add_kode_unit" value="Un.08" required max="50" oninput="updateAddPreview()" class="w-full h-10 px-3 rounded-xl border border-slate-300 text-sm font-bold text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label for="add_kode_bagian" class="block text-xs font-semibold text-slate-700 mb-1">Kode Bagian</label>
                    <input type="text" name="kode_bagian" id="add_kode_bagian" value="PPKES" required max="50" oninput="updateAddPreview()" class="w-full h-10 px-3 rounded-xl border border-slate-300 text-sm font-bold text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                </div>
            </div>

            <div>
                <label for="add_tahun_surat" class="block text-xs font-semibold text-slate-700 mb-1">Tahun Surat</label>
                <input type="number" name="tahun_surat" id="add_tahun_surat" required min="2000" max="2100" placeholder="2027" oninput="updateAddPreview()" class="w-full h-10 px-3 rounded-xl border border-slate-300 text-sm font-bold text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
            </div>

            <!-- Preview Box Modal Tambah -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PREVIEW NOMOR SURAT</span>
                <div class="font-mono text-base font-bold text-emerald-800 tracking-tight" id="add-format-preview">
                    0172/Un.08/PPKES/09/2027
                </div>
                <p class="text-[11px] text-slate-500 italic leading-relaxed">
                    Contoh nomor surat yang akan digunakan sistem.
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-tambah-tahun').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-2xs transition-colors">
                    Simpan Tahun
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Confirmation Activation -->
<div id="modal-confirm-activate" class="hidden fixed inset-0 z-[99999] overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4 transform transition-all">
        <div class="flex items-center gap-3 text-amber-600">
            <div class="p-2 bg-amber-100 rounded-xl">
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
            <button type="button" id="btn-submit-activate" onclick="executeActivation()" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-2xs transition-colors">
                Aktifkan Sekarang
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let pendingFormId = null;

    function openEditFormatModal(id, tahun, nomorMulai, kodeUnit, kodeBagian, tahunSurat) {
        const form = document.getElementById('form-edit-nomor');
        form.action = "{{ url('/admin/tahun-maba') }}/" + id + "/format";
        document.getElementById('edit-nomor-subtitle').innerText = 'Maba ' + tahun;
        document.getElementById('edit_nomor_surat_mulai').value = nomorMulai;
        document.getElementById('edit_kode_unit').value = kodeUnit;
        document.getElementById('edit_kode_bagian').value = kodeBagian;
        document.getElementById('edit_tahun_surat').value = tahunSurat || tahun;
        updateEditPreview();
        document.getElementById('modal-edit-nomor').classList.remove('hidden');
    }

    function updateEditPreview() {
        const numVal = document.getElementById('edit_nomor_surat_mulai').value || '172';
        const paddedNum = String(numVal).padStart(4, '0');
        const unitVal = (document.getElementById('edit_kode_unit').value || 'Un.08').replace(/\//g, '');
        const bagianVal = (document.getElementById('edit_kode_bagian').value || 'PPKES').replace(/\//g, '');
        const yearVal = document.getElementById('edit_tahun_surat').value || new Date().getFullYear();
        const now = new Date();
        const month = String(now.getMonth() + 1).padStart(2, '0');

        document.getElementById('edit-format-preview').innerText = `${paddedNum}/${unitVal}/${bagianVal}/${month}/${yearVal}`;
    }

    function updateAddPreview() {
        const numVal = document.getElementById('nomor_surat_mulai').value || '172';
        const paddedNum = String(numVal).padStart(4, '0');
        const unitVal = (document.getElementById('add_kode_unit').value || 'Un.08').replace(/\//g, '');
        const bagianVal = (document.getElementById('add_kode_bagian').value || 'PPKES').replace(/\//g, '');
        const yearVal = document.getElementById('add_tahun_surat').value || document.getElementById('tahun').value || new Date().getFullYear();
        const now = new Date();
        const month = String(now.getMonth() + 1).padStart(2, '0');

        document.getElementById('add-format-preview').innerText = `${paddedNum}/${unitVal}/${bagianVal}/${month}/${yearVal}`;
    }

    function syncTahunSuratDefault() {
        const val = document.getElementById('tahun').value;
        if (val) {
            document.getElementById('add_tahun_surat').value = val;
        }
    }

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
