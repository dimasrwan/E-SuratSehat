@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Detail Identitas Maba</h1>
            <p class="text-xs text-slate-500 mt-1">Periksa dokumen fisik Maba (KTP/KK) dan cocokkan dengan data di bawah ini.</p>
        </div>
        <a href="{{ route('admin.maba-verifikasi.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition duration-150">
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $mabaData->nama_lengkap ?? $mabaData->nama_biro }}</h2>
                <p class="text-xs text-emerald-700 font-semibold">{{ $mabaData->program_studi_biro }} ({{ $mabaData->fakultas ?? '-' }})</p>
            </div>
            <div>
                @if($mabaData->status_biodata === 'MENUNGGU_VERIFIKASI')
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full font-bold text-xs uppercase">Menunggu Verifikasi</span>
                @elseif($mabaData->status_biodata === 'TERVERIFIKASI')
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full font-bold text-xs uppercase">Terverifikasi</span>
                @elseif($mabaData->status_biodata === 'PERLU_PERBAIKAN')
                    <span class="px-3 py-1 bg-rose-100 text-rose-800 rounded-full font-bold text-xs uppercase">Perlu Perbaikan</span>
                @elseif($mabaData->status_biodata === 'PEMERIKSAAN_SELESAI')
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-bold text-xs uppercase">Pemeriksaan Selesai</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                <span class="text-slate-500 block mb-1 font-semibold">Nama Lengkap Inputan Maba:</span>
                <span class="font-extrabold text-slate-900 text-sm">{{ $mabaData->nama_lengkap ?? '-' }}</span>
            </div>
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                <span class="text-slate-500 block mb-1 font-semibold">Nama Resmi Biro Akademik:</span>
                <span class="font-extrabold text-slate-900 text-sm">{{ $mabaData->nama_biro }}</span>
            </div>
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                <span class="text-slate-500 block mb-1 font-semibold">NIK (16 Digit):</span>
                <span class="font-mono font-bold text-slate-900 text-sm">{{ $mabaData->nik ?? '-' }}</span>
            </div>
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                <span class="text-slate-500 block mb-1 font-semibold">Email Mahasiswa:</span>
                <span class="font-bold text-slate-900 text-sm">{{ $mabaData->email ?? '-' }}</span>
            </div>
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                <span class="text-slate-500 block mb-1 font-semibold">Tempat, Tanggal Lahir:</span>
                <span class="font-bold text-slate-900 text-sm">{{ $mabaData->tempat_lahir ?? '-' }}, {{ $mabaData->tanggal_lahir ? $mabaData->tanggal_lahir->format('d F Y') : '-' }} ({{ $mabaData->umur ? $mabaData->umur . ' Thn' : '-' }})</span>
            </div>
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                <span class="text-slate-500 block mb-1 font-semibold">Jenis Kelamin & Agama:</span>
                <span class="font-bold text-slate-900 text-sm">{{ $mabaData->jenis_kelamin ?? '-' }} / {{ $mabaData->agama ?? '-' }}</span>
            </div>
            <div class="col-span-1 md:col-span-2 bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                <span class="text-slate-500 block mb-1 font-semibold">Alamat Lengkap:</span>
                <span class="font-bold text-slate-900 text-sm">{{ $mabaData->alamat ?? '-' }}</span>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            @if($mabaData->status_biodata === 'MENUNGGU_VERIFIKASI' || $mabaData->status_biodata === 'PERLU_PERBAIKAN')
            <form action="{{ route('admin.maba-verifikasi.request-correction', $mabaData->id) }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Minta perbaikan biodata kepada mahasiswa?')" class="px-5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-xl text-xs transition duration-150">
                    Minta Perbaikan
                </button>
            </form>
            <form action="{{ route('admin.maba-verifikasi.verify', $mabaData->id) }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Verifikasi identitas fisik mahasiswa ini?')" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md transition duration-150">
                    Setujui Verifikasi
                </button>
            </form>
            @elseif($mabaData->status_biodata === 'TERVERIFIKASI')
            <a href="{{ route('pemeriksaan.create', ['maba_id' => $mabaData->id]) }}" class="px-6 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs shadow-md transition duration-150">
                + Input Pemeriksaan Kesehatan Medis
            </a>
            @endif
        </div>
    </div>
</div>
@endsection
