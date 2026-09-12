@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Top Nav & Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Detail Mahasiswa Baru</h1>
            <p class="text-xs text-slate-500 mt-0.5">Informasi lengkap pendaftaran, biodata, verifikasi, dan status pemeriksaan medis.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('maba.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition duration-150">
                Kembali ke Daftar
            </a>
            <a href="{{ route('maba.edit', $mabaData->id) }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-md transition duration-150 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Data
            </a>
            @if($mabaData->status_biodata === 'TERVERIFIKASI')
            <a href="{{ route('pemeriksaan.create', ['maba_id' => $mabaData->id]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition duration-150">
                + Input Pemeriksaan Medis
            </a>
            @endif
        </div>
    </div>

    <!-- Status Timeline Bar -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Status Lifecycle Mahasiswa</h3>
        <div class="grid grid-cols-5 gap-2 text-center text-xs font-bold">
            <!-- Step 1: Data Biro -->
            <div class="space-y-1">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto text-sm">✓</div>
                <div class="text-emerald-800 text-[11px]">1. Data Biro</div>
            </div>

            <!-- Step 2: Biodata -->
            <div class="space-y-1">
                @if(in_array($mabaData->status_biodata, ['MENUNGGU_VERIFIKASI', 'PERLU_PERBAIKAN', 'TERVERIFIKASI', 'PEMERIKSAAN_SELESAI']))
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto text-sm">✓</div>
                <div class="text-emerald-800 text-[11px]">2. Biodata</div>
                @else
                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-sm">○</div>
                <div class="text-slate-400 text-[11px]">2. Biodata</div>
                @endif
            </div>

            <!-- Step 3: Verifikasi -->
            <div class="space-y-1">
                @if(in_array($mabaData->status_biodata, ['TERVERIFIKASI', 'PEMERIKSAAN_SELESAI']))
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto text-sm">✓</div>
                <div class="text-emerald-800 text-[11px]">3. Verifikasi</div>
                @elseif($mabaData->status_biodata === 'MENUNGGU_VERIFIKASI')
                <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center mx-auto text-sm">●</div>
                <div class="text-amber-700 text-[11px]">3. Menunggu</div>
                @elseif($mabaData->status_biodata === 'PERLU_PERBAIKAN')
                <div class="w-8 h-8 rounded-full bg-rose-500 text-white flex items-center justify-center mx-auto text-sm">!</div>
                <div class="text-rose-700 text-[11px]">3. Perlu Perbaikan</div>
                @else
                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-sm">○</div>
                <div class="text-slate-400 text-[11px]">3. Verifikasi</div>
                @endif
            </div>

            <!-- Step 4: Pemeriksaan Medis -->
            <div class="space-y-1">
                @if($mabaData->status_biodata === 'PEMERIKSAAN_SELESAI')
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto text-sm">✓</div>
                <div class="text-emerald-800 text-[11px]">4. Pemeriksaan</div>
                @else
                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-sm">○</div>
                <div class="text-slate-400 text-[11px]">4. Pemeriksaan</div>
                @endif
            </div>

            <!-- Step 5: Surat Dikirim -->
            <div class="space-y-1">
                @if($mabaData->pemeriksaan && $mabaData->pemeriksaan->status_pengiriman === 'Terkirim')
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto text-sm">✓</div>
                <div class="text-emerald-800 text-[11px]">5. Email Terkirim</div>
                @else
                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-sm">○</div>
                <div class="text-slate-400 text-[11px]">5. Pengiriman</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Catatan Perbaikan Alert (if any) -->
    @if($mabaData->catatan_perbaikan)
    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs font-semibold flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
            <strong class="font-extrabold text-amber-950 block mb-0.5">Catatan Perbaikan Biodata:</strong>
            <p class="text-amber-900 leading-relaxed">{{ $mabaData->catatan_perbaikan }}</p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left 2 Cols: Biodata & Biro Data -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Biodata Pribadi -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-4">
                <h3 class="font-extrabold text-slate-800 text-sm border-b border-slate-100 pb-3 flex items-center justify-between">
                    <span>Biodata Pribadi Maba</span>
                    <span class="text-xs text-slate-400 font-medium">Tahun Maba {{ $mabaData->tahunMaba->tahun ?? '-' }}</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block font-medium">Nama Lengkap:</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $mabaData->nama_lengkap ?? '-' }}</span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block font-medium">NIK:</span>
                        <span class="font-bold font-mono text-slate-900 text-sm">{{ $mabaData->nik ?? '-' }}</span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block font-medium">Email Mahasiswa:</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $mabaData->email ?? '-' }}</span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block font-medium">Tempat, Tanggal Lahir:</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $mabaData->tempat_lahir ?? '-' }}, {{ $mabaData->tanggal_lahir ? $mabaData->tanggal_lahir->format('d M Y') : '-' }} ({{ $mabaData->umur ? $mabaData->umur . ' Thn' : '-' }})</span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block font-medium">Jenis Kelamin / Agama:</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $mabaData->jenis_kelamin ?? '-' }} / {{ $mabaData->agama ?? '-' }}</span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block font-medium">Fakultas:</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $mabaData->fakultas ?? '-' }}</span>
                    </div>
                    <div class="sm:col-span-2 bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block font-medium">Alamat Lengkap:</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $mabaData->alamat ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Data Biro -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-4">
                <h3 class="font-extrabold text-slate-800 text-sm border-b border-slate-100 pb-3">
                    Data Pendaftaran Biro Akademik
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block font-medium">Nama Biro:</span>
                        <span class="font-bold text-slate-900">{{ $mabaData->nama_biro }}</span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block font-medium">Program Studi Biro:</span>
                        <span class="font-bold text-emerald-700">{{ $mabaData->program_studi_biro }}</span>
                    </div>
                    <div class="sm:col-span-2 bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-500 block font-medium">Jadwal Pemeriksaan:</span>
                        <span class="font-bold text-slate-900">{{ $mabaData->tanggal_jadwal ? $mabaData->tanggal_jadwal->format('d M Y') : '-' }} (Sesi {{ $mabaData->sesi_jadwal ?? '-' }} / {{ $mabaData->waktu_jadwal ?? '-' }})</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Col: Audit & Pemeriksaan Medis -->
        <div class="space-y-6">
            <!-- Audit Verifikasi -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-3 text-xs">
                <h3 class="font-extrabold text-slate-800 text-sm border-b border-slate-100 pb-2">Status Audit Verifikasi</h3>
                
                <div class="flex justify-between border-b border-slate-100 pb-1.5">
                    <span class="text-slate-500">Status Biodata:</span>
                    <span class="font-bold text-slate-900">{{ $mabaData->status_biodata }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-1.5">
                    <span class="text-slate-500">Diverifikasi Oleh:</span>
                    <span class="font-bold text-slate-900">{{ $mabaData->verifier->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Waktu Verifikasi:</span>
                    <span class="font-bold text-slate-900">{{ $mabaData->verified_at ? $mabaData->verified_at->format('d M Y H:i') : '-' }}</span>
                </div>
            </div>

            <!-- Data Pemeriksaan Medis -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-4 text-xs">
                <h3 class="font-extrabold text-slate-800 text-sm border-b border-slate-100 pb-3 flex items-center justify-between">
                    <span>Pemeriksaan Medis</span>
                    @if($mabaData->pemeriksaan)
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-md">SELESAI</span>
                    @else
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 font-bold rounded-md">BELUM</span>
                    @endif
                </h3>

                @if($mabaData->pemeriksaan)
                <div class="space-y-2">
                    <div>
                        <span class="text-slate-500 block">Nomor Surat:</span>
                        <span class="font-mono font-bold text-slate-900 text-sm">{{ $mabaData->pemeriksaan->nomor_surat }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Kesimpulan Dokter:</span>
                        <span class="font-bold text-emerald-700">{{ $mabaData->pemeriksaan->kesimpulan }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Dokter Pemeriksa:</span>
                        <span class="font-semibold text-slate-800">{{ $mabaData->pemeriksaan->dokter_nama ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Status Pengiriman Email:</span>
                        <span class="font-bold text-blue-700">{{ $mabaData->pemeriksaan->status_pengiriman ?? 'Menunggu' }}</span>
                    </div>
                    
                    <div class="pt-3 flex gap-2">
                        <a href="{{ route('pemeriksaan.show', $mabaData->pemeriksaan->id) }}" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-center rounded-xl transition duration-150">
                            Lihat Detail Medis
                        </a>
                    </div>
                </div>
                @else
                <div class="text-slate-400 text-center py-4">
                    Belum ada data pemeriksaan medis.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
