@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex text-xs text-slate-500 mb-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li><a href="/" class="hover:text-emerald-700">Dashboard</a></li>
                    <li><span class="text-slate-400 mx-1">/</span></li>
                    <li><a href="{{ route('admin.import.index') }}" class="hover:text-emerald-700">Import Data Biro</a></li>
                    <li><span class="text-slate-400 mx-1">/</span></li>
                    <li class="font-medium text-slate-700">Preview Batch #{{ $batch->id }}</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Preview & Review Import Data Biro</h1>
            <p class="text-xs text-slate-500 mt-1">
                Target Angkatan: <span class="font-semibold text-slate-700">Maba {{ $batch->tahunMaba->tahun }}</span> &bull; 
                File: <span class="font-semibold text-slate-700">{{ $batch->original_filename }}</span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('admin.import.cancel', $batch->id) }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin membatalkan proses import ini?')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-300 transition">
                    Batal Import
                </button>
            </form>
            <button type="button" onclick="document.getElementById('confirmModal').classList.remove('hidden')" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Konfirmasi Import
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <!-- Summary Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-500 font-medium">Total Baris</div>
            <div class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($batch->total_rows) }}</div>
        </div>
        <div class="bg-emerald-50/60 p-4 rounded-2xl border border-emerald-200/80 shadow-sm">
            <div class="text-xs text-emerald-700 font-medium">Data Baru</div>
            <div class="text-2xl font-bold text-emerald-800 mt-1">{{ number_format($batch->new_rows) }}</div>
        </div>
        <div class="bg-amber-50/60 p-4 rounded-2xl border border-amber-200/80 shadow-sm">
            <div class="text-xs text-amber-700 font-medium">Possible Duplicate</div>
            <div class="text-2xl font-bold text-amber-800 mt-1">{{ number_format($batch->possible_duplicate_rows) }}</div>
        </div>
        <div class="bg-slate-100/70 p-4 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-xs text-slate-600 font-medium">Exact Duplicate (Skip)</div>
            <div class="text-2xl font-bold text-slate-700 mt-1">{{ number_format($batch->exact_duplicate_rows) }}</div>
        </div>
        <div class="bg-rose-50/60 p-4 rounded-2xl border border-rose-200/80 shadow-sm">
            <div class="text-xs text-rose-700 font-medium">Error Baris</div>
            <div class="text-2xl font-bold text-rose-800 mt-1">{{ number_format($batch->error_rows) }}</div>
        </div>
    </div>

    <!-- Main Form for Preview & Possible Duplicate Decisions -->
    <form id="confirmImportForm" action="{{ route('admin.import.confirm', $batch->id) }}" method="POST">
        @csrf

        <!-- Tabs Container -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden" x-data="{ tab: 'new' }">
            <!-- Tab Navigation Header -->
            <div class="flex border-b border-slate-200 bg-slate-50/60 overflow-x-auto">
                <button type="button" @click="tab = 'new'" :class="tab === 'new' ? 'border-emerald-600 text-emerald-800 bg-white font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'" class="px-5 py-3.5 text-xs border-b-2 transition whitespace-nowrap">
                    Data Baru ({{ number_format($batch->new_rows) }})
                </button>
                <button type="button" @click="tab = 'possible'" :class="tab === 'possible' ? 'border-amber-600 text-amber-800 bg-white font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'" class="px-5 py-3.5 text-xs border-b-2 transition whitespace-nowrap flex items-center gap-1.5">
                    <span>Possible Duplicate ({{ number_format($batch->possible_duplicate_rows) }})</span>
                    @if($batch->possible_duplicate_rows > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    @endif
                </button>
                <button type="button" @click="tab = 'exact'" :class="tab === 'exact' ? 'border-slate-600 text-slate-800 bg-white font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'" class="px-5 py-3.5 text-xs border-b-2 transition whitespace-nowrap">
                    Exact Duplicate ({{ number_format($batch->exact_duplicate_rows) }})
                </button>
                <button type="button" @click="tab = 'error'" :class="tab === 'error' ? 'border-rose-600 text-rose-800 bg-white font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'" class="px-5 py-3.5 text-xs border-b-2 transition whitespace-nowrap">
                    Error Baris ({{ number_format($batch->error_rows) }})
                </button>
            </div>

            <!-- Tab 1: Data Baru -->
            <div x-show="tab === 'new'" class="p-6">
                <div class="mb-4 text-xs text-slate-500">
                    Daftar data Maba baru yang belum pernah terdaftar pada angkatan <strong>Maba {{ $batch->tahunMaba->tahun }}</strong>. Data ini akan dimasukkan sebagai record `maba_datas` baru.
                </div>
                @if($newRows->count() > 0)
                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3">Baris</th>
                                <th class="px-4 py-3">Nama Maba (Biro)</th>
                                <th class="px-4 py-3">Program Studi</th>
                                <th class="px-4 py-3">Tanggal Jadwal</th>
                                <th class="px-4 py-3">Sesi</th>
                                <th class="px-4 py-3">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($newRows as $row)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-2.5 font-mono text-slate-400">#{{ $row->row_number }}</td>
                                <td class="px-4 py-2.5 font-medium text-slate-800">{{ $row->nama_biro }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $row->program_studi_biro }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $row->tanggal_jadwal ? $row->tanggal_jadwal->format('d/m/Y') : '-' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $row->sesi_jadwal ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $row->waktu_jadwal ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $newRows->appends(request()->except('page_new'))->links() }}</div>
                @else
                <p class="text-xs text-slate-400 italic py-4 text-center">Tidak ada baris data baru.</p>
                @endif
            </div>

            <!-- Tab 2: Possible Duplicate -->
            <div x-show="tab === 'possible'" class="p-6">
                <div class="mb-4 bg-amber-50 border border-amber-200/80 rounded-xl p-3.5 text-xs text-amber-800">
                    <span class="font-semibold">Tinjauan Possible Duplicate:</span> Ditemukan Nama & Program Studi yang sama dengan data existing di angkatan Maba {{ $batch->tahunMaba->tahun }}, namun memiliki jadwal berbeda. Silakan tentukan tindakan per baris:
                </div>
                @if($possibleDuplicates->count() > 0)
                <div class="space-y-4">
                    @foreach($possibleDuplicates as $row)
                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/30 text-xs space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <span class="font-mono text-slate-400 font-semibold">Baris File #{{ $row->row_number }}</span>
                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded font-semibold text-[10px]">POSSIBLE DUPLICATE</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Data File Biro -->
                            <div class="bg-white p-3 rounded-lg border border-slate-200 space-y-1">
                                <div class="font-semibold text-slate-700 text-[11px] uppercase tracking-wide">Data File Biro</div>
                                <div class="font-bold text-slate-800 text-sm">{{ $row->nama_biro }}</div>
                                <div class="text-slate-600">{{ $row->program_studi_biro }}</div>
                                <div class="text-slate-500 text-[11px]">Jadwal: {{ $row->tanggal_jadwal ? $row->tanggal_jadwal->format('d/m/Y') : '-' }} | {{ $row->sesi_jadwal ?? '-' }} | {{ $row->waktu_jadwal ?? '-' }}</div>
                            </div>
                            <!-- Data Existing -->
                            <div class="bg-slate-100/80 p-3 rounded-lg border border-slate-200 space-y-1">
                                <div class="font-semibold text-slate-500 text-[11px] uppercase tracking-wide">Data Existing di Database</div>
                                <div class="font-bold text-slate-800 text-sm">{{ $row->existingMabaData->nama_biro ?? '-' }}</div>
                                <div class="text-slate-600">{{ $row->existingMabaData->program_studi_biro ?? '-' }}</div>
                                <div class="text-slate-500 text-[11px]">Jadwal Existing: {{ $row->existingMabaData->tanggal_jadwal ? $row->existingMabaData->tanggal_jadwal->format('d/m/Y') : '-' }} | {{ $row->existingMabaData->sesi_jadwal ?? '-' }}</div>
                                <div class="text-[10px] text-emerald-700 font-medium">Status Biodata: {{ $row->existingMabaData->status_biodata ?? '-' }}</div>
                            </div>
                        </div>
                        <!-- Decision Radio Options -->
                        <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-6">
                            <span class="font-medium text-slate-700">Pilih Tindakan:</span>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="actions[{{ $row->id }}]" value="UPDATE_JADWAL" {{ ($row->selected_action ?? 'UPDATE_JADWAL') === 'UPDATE_JADWAL' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                                <span class="text-slate-700 font-medium">Perbarui Jadwal Existing</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="actions[{{ $row->id }}]" value="CREATE_NEW" {{ ($row->selected_action) === 'CREATE_NEW' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                                <span class="text-slate-700 font-medium">Buat Sebagai Data Maba Baru</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="actions[{{ $row->id }}]" value="SKIP" {{ ($row->selected_action) === 'SKIP' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                                <span class="text-slate-500">Abaikan (Skip)</span>
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $possibleDuplicates->appends(request()->except('page_possible'))->links() }}</div>
                @else
                <p class="text-xs text-slate-400 italic py-4 text-center">Tidak ada baris possible duplicate.</p>
                @endif
            </div>

            <!-- Tab 3: Exact Duplicate -->
            <div x-show="tab === 'exact'" class="p-6">
                <div class="mb-4 text-xs text-slate-500">
                    Daftar baris yang 100% identik (Nama, Prodi, dan Jadwal) dengan data yang sudah ada di angkatan ini. Baris ini secara otomatis akan <strong>DILEWATI (SKIP)</strong> untuk mencegah duplikasi.
                </div>
                @if($exactDuplicates->count() > 0)
                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3">Baris</th>
                                <th class="px-4 py-3">Nama Maba</th>
                                <th class="px-4 py-3">Program Studi</th>
                                <th class="px-4 py-3">Jadwal</th>
                                <th class="px-4 py-3">Status Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($exactDuplicates as $row)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-2.5 font-mono text-slate-400">#{{ $row->row_number }}</td>
                                <td class="px-4 py-2.5 font-medium text-slate-800">{{ $row->nama_biro }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $row->program_studi_biro }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $row->tanggal_jadwal ? $row->tanggal_jadwal->format('d/m/Y') : '-' }} {{ $row->sesi_jadwal }}</td>
                                <td class="px-4 py-2.5"><span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-medium">SKIP (Duplikat Persis)</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $exactDuplicates->appends(request()->except('page_exact'))->links() }}</div>
                @else
                <p class="text-xs text-slate-400 italic py-4 text-center">Tidak ada baris exact duplicate.</p>
                @endif
            </div>

            <!-- Tab 4: Error Baris -->
            <div x-show="tab === 'error'" class="p-6">
                <div class="mb-4 text-xs text-slate-500">
                    Daftar baris yang gagal validasi (misal: Nama/Prodi kosong). Baris-baris ini akan diabaikan dan tidak akan dimasukkan ke database.
                </div>
                @if($errorRows->count() > 0)
                <div class="overflow-x-auto border border-rose-100 rounded-xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-rose-50/60 text-rose-800 uppercase tracking-wider font-semibold border-b border-rose-100">
                            <tr>
                                <th class="px-4 py-3">Baris</th>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Prodi</th>
                                <th class="px-4 py-3">Pesan Error Validasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-100/50 bg-rose-50/20">
                            @foreach($errorRows as $row)
                            <tr>
                                <td class="px-4 py-2.5 font-mono text-rose-400 font-semibold">#{{ $row->row_number }}</td>
                                <td class="px-4 py-2.5 text-slate-700">{{ $row->nama_biro ?? '(Kosong)' }}</td>
                                <td class="px-4 py-2.5 text-slate-700">{{ $row->program_studi_biro ?? '(Kosong)' }}</td>
                                <td class="px-4 py-2.5 font-semibold text-rose-700">{{ $row->error_messages }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $errorRows->appends(request()->except('page_error'))->links() }}</div>
                @else
                <p class="text-xs text-slate-400 italic py-4 text-center">Tidak ada baris error validasi.</p>
                @endif
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div id="confirmModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-5 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center gap-3 text-emerald-700">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">Konfirmasi Proses Import</h3>
                        <p class="text-xs text-slate-500">Angkatan Target: Maba {{ $batch->tahunMaba->tahun }}</p>
                    </div>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 text-xs text-slate-700 space-y-1.5">
                    <div class="flex justify-between"><span>Data Maba Baru Ditambahkan:</span> <strong class="text-emerald-700">{{ number_format($batch->new_rows) }}</strong></div>
                    <div class="flex justify-between"><span>Possible Duplicate Ditingkatkan:</span> <strong class="text-amber-700">{{ number_format($batch->possible_duplicate_rows) }}</strong></div>
                    <div class="flex justify-between"><span>Exact Duplicate Dilewati (Skip):</span> <strong class="text-slate-600">{{ number_format($batch->exact_duplicate_rows) }}</strong></div>
                    <div class="flex justify-between"><span>Baris Error Diabaikan:</span> <strong class="text-rose-600">{{ number_format($batch->error_rows) }}</strong></div>
                </div>

                <div class="text-xs text-slate-600 bg-amber-50 border border-amber-200/80 p-3 rounded-xl space-y-1">
                    <div class="font-semibold text-amber-900">Perhatian Perlindungan Data:</div>
                    <p>Proses ini HANYA mengelola data sumber Biro. Biodata pribadi Maba yang sudah diisi secara online TIDAK akan tertimpa dan tidak ada pemeriksaan medis/nomor surat yang dibuat saat import ini.</p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('confirmModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        Ya, Jalankan Import Now
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
