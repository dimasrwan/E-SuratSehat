@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-3xl font-bold text-gray-800 tracking-tight">Detail Data Pemeriksaan</h2>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('pemeriksaan.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition shadow-sm">
                Kembali
            </a>
            <a href="{{ route('pemeriksaan.edit', $pemeriksaan->id) }}" class="inline-flex items-center px-4 py-2 bg-amber-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-600 shadow-sm transition">
                Edit Data
            </a>
            <a href="{{ route('pemeriksaan.preview', $pemeriksaan->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-sm transition">
                Preview Surat
            </a>
            <a href="{{ route('pemeriksaan.download', $pemeriksaan->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-emerald-700 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-800 shadow-sm transition">
                Download PDF
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-md shadow-sm" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-md sm:rounded-xl mb-8 border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-white flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Menerangkan Bahwa:</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Informasi personal mahasiswa bersangkutan.</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-gray-700">No. Surat: {{ $pemeriksaan->nomor_surat ?? '-' }}</p>
                <p class="text-sm text-gray-500">Dokter: {{ $pemeriksaan->dokter_nama ?? '-' }}</p>
                <p class="text-xs font-bold text-emerald-800 mt-0.5">Tahun Maba: {{ $pemeriksaan->tahun_masuk }}</p>
            </div>
        </div>
        <div>
            <dl class="divide-y divide-gray-100">
                <div class="bg-gray-50 px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-emerald-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Nama Lengkap</dt>
                    <dd class="mt-1 text-sm font-bold text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->nama }}</dd>
                </div>
                <div class="bg-white px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-emerald-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">NIK / Email</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->nik ?? '-' }} <span class="text-gray-400 mx-2">|</span> {{ $pemeriksaan->email }}</dd>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-emerald-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Umur / Jenis Kelamin</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->umur ?? '-' }} Tahun <span class="text-gray-400 mx-2">|</span> {{ $pemeriksaan->jenis_kelamin ?? '-' }}</dd>
                </div>
                <div class="bg-white px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-emerald-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Tempat, Tanggal Lahir</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->tempat_lahir ?? '-' }}, {{ $pemeriksaan->tanggal_lahir ? \Carbon\Carbon::parse($pemeriksaan->tanggal_lahir)->locale('id')->translatedFormat('d F Y') : '-' }}</dd>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-emerald-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Agama / Pekerjaan</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->agama ?? '-' }} <span class="text-gray-400 mx-2">|</span> {{ $pemeriksaan->fakultas ?? '-' }} <span class="text-gray-400 mx-2">|</span> {{ $pemeriksaan->pekerjaan ?? '-' }}</dd>
                </div>
                <div class="bg-white px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-emerald-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Alamat</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->alamat ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white shadow-md sm:rounded-xl mb-6 border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 bg-emerald-50 border-b border-emerald-100">
            <h3 class="text-xl font-bold text-emerald-900">Kesimpulan</h3>
            <p class="mt-2 max-w-2xl text-lg font-extrabold text-emerald-700">Dinyatakan: {{ $pemeriksaan->kesimpulan }}</p>
            <p class="mt-1 max-w-2xl text-sm font-medium text-emerald-600">Keperluan: {{ $pemeriksaan->keperluan ?? '-' }}</p>
        </div>
        <div>
            <dl class="divide-y divide-gray-100">
                <div class="bg-white px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-gray-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Tinggi / Berat Badan</dt>
                    <dd class="mt-1 text-sm font-bold text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->tinggi_badan ?? '-' }} cm <span class="text-gray-300 mx-2">|</span> {{ $pemeriksaan->berat_badan ?? '-' }} kg</dd>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-gray-100 transition">
                    <dt class="text-sm font-semibold text-gray-600">Tekanan Darah</dt>
                    <dd class="mt-1 text-sm font-bold text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->tekanan_darah ?? '-' }} Mm/hg</dd>
                </div>
                <div class="bg-white px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-gray-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Golongan Darah / Buta Warna</dt>
                    <dd class="mt-1 text-sm font-bold text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->golongan_darah ?? '-' }} <span class="text-gray-300 mx-2">|</span> {{ $pemeriksaan->buta_warna ?? '-' }}</dd>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-gray-100 transition">
                    <dt class="text-sm font-semibold text-gray-600">Riwayat Penyakit Kronis</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->riwayat_penyakit_kronis ?? '-' }}</dd>
                </div>
                <div class="bg-white px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-gray-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Riwayat Penggunaan Obat</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->riwayat_penggunaan_obat ?? '-' }}</dd>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-gray-100 transition">
                    <dt class="text-sm font-semibold text-gray-600">Riwayat Alergi</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->riwayat_alergi ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="mt-6 flex justify-end pb-8">
        <form action="{{ route('pemeriksaan.destroy', $pemeriksaan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini secara permanen?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-500 hover:text-white hover:bg-red-500 border border-transparent hover:border-red-500 px-4 py-2 rounded-lg font-semibold transition">Hapus Data Permanen</button>
        </form>
    </div>
</div>
@endsection
