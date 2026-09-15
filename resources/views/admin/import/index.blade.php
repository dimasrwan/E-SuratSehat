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
                    <li class="font-medium text-slate-700">Import Data Biro</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Import Data Biro Maba</h1>
            <p class="text-xs text-slate-500 mt-1">Unggah file jadwal dan daftar Maba dari Biro untuk disimpan ke master data angkatan Maba.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.import.template') }}" class="inline-flex items-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-sm font-semibold px-4 py-2.5 rounded-xl border border-emerald-200 transition shadow-xs">
                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download Template Excel
            </a>
            <a href="{{ route('admin.import.history') }}" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium px-4 py-2.5 rounded-xl border border-slate-300 transition shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Riwayat Import
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium flex items-center gap-3">
            <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 bg-sky-50 border border-sky-200 text-sky-800 rounded-xl text-sm font-medium flex items-center gap-3">
            <svg class="w-5 h-5 text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>{{ session('info') }}</div>
        </div>
    @endif

    <!-- Upload Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-base font-semibold text-slate-800">Form Import Data Biro</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pilih target tahun Maba dan unggah berkas Excel/CSV dari Biro.</p>
        </div>

        <form action="{{ route('admin.import.preview.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Target Tahun Maba -->
                <div>
                    <label for="tahun_maba_id" class="block text-sm font-medium text-slate-700 mb-2">
                        Target Tahun Maba <span class="text-rose-500">*</span>
                    </label>
                    @php
                        $tahunImportOptions = [];
                        foreach($tahunMabas as $tahun) {
                            $tahunImportOptions[(string)$tahun->id] = 'Maba ' . $tahun->tahun . ($tahun->is_active ? ' (Aktif)' : '');
                        }
                    @endphp
                    <x-form-select name="tahun_maba_id" id="tahun_maba_id" :value="old('tahun_maba_id', (string)($tahunMabas->firstWhere('is_active', true)->id ?? ''))" placeholder="-- Pilih Tahun Maba Target --" :options="$tahunImportOptions" required />
                    @error('tahun_maba_id')
                        <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- File Input -->
                <div>
                    <label for="file" class="block text-sm font-medium text-slate-700 mb-2">
                        File Data Biro (.xlsx, .xls, .csv, .pdf) <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" name="file" id="file" required accept=".xlsx,.xls,.csv,.pdf" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-300 rounded-xl p-1 bg-slate-50 cursor-pointer">
                    <p class="text-xs text-slate-400 mt-1.5">Maksimal ukuran file: 10 MB. Mendukung Excel (.xlsx, .xls), CSV (.csv), dan PDF Jadwal resmi 2026/2027.</p>
                    @error('file')
                        <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Notice Box -->
            <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-4 text-xs text-amber-800 space-y-1">
                <div class="font-semibold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Informasi Alur Import Dua Tahap (Safe Two-Step Process):
                </div>
                <p>Data dari file tidak langsung dimasukkan ke database. Setelah mengklik tombol di bawah, sistem akan memvalidasi file dan mengklasifikasikan data duplikat ke dalam halaman <strong>Preview</strong> untuk Anda tinjau terlebih dahulu.</p>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-2">
                <button type="submit" class="inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-6 py-3 rounded-xl text-sm shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Upload & Preview File
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Import Batches -->
    @if($recentBatches->count() > 0)
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800">Aktivitas Import Terakhir</h3>
            <a href="{{ route('admin.import.history') }}" class="text-xs text-emerald-700 hover:underline font-medium">Lihat Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">File</th>
                        <th class="px-6 py-3">Target Angkatan</th>
                        <th class="px-6 py-3">Admin</th>
                        <th class="px-6 py-3">Ringkasan Baris</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentBatches as $batch)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-6 py-3.5 text-slate-600 whitespace-nowrap">{{ $batch->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-3.5 font-medium text-slate-800 whitespace-nowrap">{{ $batch->original_filename }}</td>
                        <td class="px-6 py-3.5 whitespace-nowrap font-medium text-slate-700">Maba {{ $batch->tahunMaba->tahun ?? '-' }}</td>
                        <td class="px-6 py-3.5 text-slate-600 whitespace-nowrap">{{ $batch->user->name ?? '-' }}</td>
                        <td class="px-6 py-3.5 text-slate-600 whitespace-nowrap">
                            Total: <strong>{{ number_format($batch->total_rows) }}</strong> 
                            (Baru: {{ number_format($batch->new_rows) }}, Duplicate: {{ number_format($batch->possible_duplicate_rows + $batch->exact_duplicate_rows) }})
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            @if($batch->status === 'COMPLETED')
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-medium text-[11px]">COMPLETED</span>
                            @elseif($batch->status === 'PREVIEW')
                                <a href="{{ route('admin.import.preview', $batch->id) }}" class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full font-medium text-[11px] hover:bg-amber-100">PREVIEW (Lanjutkan)</a>
                            @elseif($batch->status === 'CANCELLED')
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 border border-slate-200 rounded-full font-medium text-[11px]">CANCELLED</span>
                            @else
                                <span class="px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-full font-medium text-[11px]">{{ $batch->status }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
