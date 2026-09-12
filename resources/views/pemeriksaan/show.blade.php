@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-3xl font-bold text-gray-800 tracking-tight">Detail Data Pemeriksaan Medis</h2>
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

    <!-- SECTION 1: DATA MABA -->
    <div class="bg-white shadow-md sm:rounded-xl mb-8 border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-white flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-gray-900">DATA MABA</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Informasi identitas maba yang diperiksa.</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-bold text-emerald-800">Tahun Maba: {{ $pemeriksaan->tahun_masuk }}</p>
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
                    <dt class="text-sm font-semibold text-gray-600">Fakultas / Program Studi</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $pemeriksaan->fakultas ?? '-' }} <span class="text-gray-400 mx-2">|</span> 
                        {{ $pemeriksaan->mabaData ? ($pemeriksaan->mabaData->program_studi_biro ?? $pemeriksaan->mabaData->program_studi) : '-' }}
                    </dd>
                </div>
                <div class="bg-white px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-emerald-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Jadwal Pemeriksaan</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        @if($pemeriksaan->mabaData && $pemeriksaan->mabaData->tanggal_jadwal)
                            {{ $pemeriksaan->mabaData->tanggal_jadwal->format('d/m/Y') }} (Sesi {{ $pemeriksaan->mabaData->sesi_jadwal ?? '-' }})
                        @else
                            {{ $pemeriksaan->created_at->format('d/m/Y H:i') }}
                        @endif
                    </dd>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-emerald-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Tempat, Tanggal Lahir / Umur</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $pemeriksaan->tempat_lahir ?? '-' }}, {{ $pemeriksaan->tanggal_lahir ? \Carbon\Carbon::parse($pemeriksaan->tanggal_lahir)->locale('id')->translatedFormat('d F Y') : '-' }} 
                        <span class="text-gray-400 mx-2">|</span> {{ $pemeriksaan->umur ?? '-' }} Tahun ({{ $pemeriksaan->jenis_kelamin ?? '-' }})
                    </dd>
                </div>
                <div class="bg-white px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-emerald-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Alamat</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->alamat ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- SECTION 2: DATA PEMERIKSAAN MEDIS -->
    <div class="bg-white shadow-md sm:rounded-xl mb-8 border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 bg-emerald-50 border-b border-emerald-100 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-emerald-900">DATA PEMERIKSAAN MEDIS</h3>
                <p class="mt-1 max-w-2xl text-lg font-extrabold text-emerald-700">Dinyatakan: {{ $pemeriksaan->kesimpulan }}</p>
                <p class="mt-0.5 max-w-2xl text-xs font-semibold text-emerald-800">Keperluan: {{ $pemeriksaan->keperluan ?? '-' }}</p>
            </div>
            <div class="text-right">
                <div class="font-mono text-sm font-bold text-emerald-900 bg-white px-3 py-1 rounded border border-emerald-200 shadow-sm">
                    {{ $pemeriksaan->nomor_surat ?? 'Belum Terbit' }}
                </div>
                <p class="text-xs text-emerald-800 mt-1">Dokter: {{ $pemeriksaan->dokter_nama ?? '-' }}</p>
            </div>
        </div>
        <div>
            <dl class="divide-y divide-gray-100">
                <div class="bg-white px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-gray-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Tanggal Pemeriksaan</dt>
                    <dd class="mt-1 text-sm font-bold text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->created_at->format('d F Y H:i') }}</dd>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-gray-100 transition">
                    <dt class="text-sm font-semibold text-gray-600">Tinggi / Berat Badan</dt>
                    <dd class="mt-1 text-sm font-bold text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->tinggi_badan ?? '-' }} cm <span class="text-gray-300 mx-2">|</span> {{ $pemeriksaan->berat_badan ?? '-' }} kg</dd>
                </div>
                <div class="bg-white px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-gray-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Tekanan Darah</dt>
                    <dd class="mt-1 text-sm font-bold text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->tekanan_darah ?? '-' }} Mm/hg</dd>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-gray-100 transition">
                    <dt class="text-sm font-semibold text-gray-600">Golongan Darah / Buta Warna</dt>
                    <dd class="mt-1 text-sm font-bold text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->golongan_darah ?? '-' }} <span class="text-gray-300 mx-2">|</span> {{ $pemeriksaan->buta_warna ?? '-' }}</dd>
                </div>
                <div class="bg-white px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-gray-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Riwayat Penyakit Kronis</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->riwayat_penyakit_kronis ?? '-' }}</dd>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-gray-100 transition">
                    <dt class="text-sm font-semibold text-gray-600">Riwayat Penggunaan Obat</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->riwayat_penggunaan_obat ?? '-' }}</dd>
                </div>
                <div class="bg-white px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-gray-50 transition">
                    <dt class="text-sm font-semibold text-gray-600">Riwayat Alergi</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->riwayat_alergi ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- SECTION 3: STATUS PENGIRIMAN EMAIL -->
    <div class="bg-white shadow-md sm:rounded-xl mb-8 border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 bg-sky-50 border-b border-sky-100 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-sky-900">PENGIRIMAN EMAIL SURAT SEHAT</h3>
                <p class="text-xs text-sky-700 mt-0.5">Status antrean dan log pengiriman PDF ke email maba.</p>
            </div>
            <div>
                @if($pemeriksaan->status_pengiriman === 'Terkirim')
                    <span class="px-3 py-1.5 inline-flex text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                        ✓ Sudah Terkirim
                    </span>
                @elseif($pemeriksaan->status_pengiriman === 'Gagal')
                    <span class="px-3 py-1.5 inline-flex text-xs font-bold rounded-full bg-rose-100 text-rose-800">
                        ⚠ Gagal
                    </span>
                @elseif(in_array($pemeriksaan->status_pengiriman, ['Dalam antrean', 'Mengirim']))
                    <span class="px-3 py-1.5 inline-flex text-xs font-bold rounded-full bg-sky-100 text-sky-800">
                        {{ $pemeriksaan->status_pengiriman }}
                    </span>
                @else
                    <span class="px-3 py-1.5 inline-flex text-xs font-bold rounded-full bg-gray-100 text-gray-700">
                        Belum dikirim
                    </span>
                @endif
            </div>
        </div>
        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-xs text-gray-500">Tujuan Email: <span class="font-semibold text-gray-800">{{ $pemeriksaan->email ?? '-' }}</span></div>
                <div class="text-xs text-gray-500 mt-1">Waktu Pengiriman: <span class="font-semibold text-gray-800">{{ $pemeriksaan->waktu_pengiriman ? \Carbon\Carbon::parse($pemeriksaan->waktu_pengiriman)->format('d F Y H:i:s') : '-' }}</span></div>
            </div>
            <div>
                @if($pemeriksaan->status_pengiriman === 'Terkirim' || $pemeriksaan->status_pengiriman === 'Gagal')
                    <form action="{{ route('pemeriksaan.sendEmail', $pemeriksaan->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-lg shadow-sm transition">
                            Kirim Ulang Email
                        </button>
                    </form>
                @elseif(!in_array($pemeriksaan->status_pengiriman, ['Dalam antrean', 'Mengirim']))
                    <form action="{{ route('pemeriksaan.sendEmail', $pemeriksaan->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-sm transition">
                            Kirim Email Sekarang
                        </button>
                    </form>
                @else
                    <button disabled class="px-4 py-2 bg-gray-200 text-gray-500 font-bold text-xs rounded-lg cursor-not-allowed">
                        Dalam Antrean...
                    </button>
                @endif
            </div>
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
