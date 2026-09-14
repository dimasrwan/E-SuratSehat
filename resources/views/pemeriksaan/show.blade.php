@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Detail Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 py-1">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Detail Data Pemeriksaan Medis</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">Informasi lengkap hasil pemeriksaan kesehatan mahasiswa baru.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Kembali -->
            <a href="{{ route('pemeriksaan.index') }}" class="px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold text-xs rounded-lg shadow-2xs transition duration-150 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>

            <!-- Edit Data -->
            <a href="{{ route('pemeriksaan.edit', $pemeriksaan->id) }}" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-lg shadow-2xs transition duration-150 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Data
            </a>

            <!-- Preview Surat -->
            <a href="{{ route('pemeriksaan.preview', $pemeriksaan->id) }}" class="px-3.5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs rounded-lg shadow-2xs transition duration-150 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-sky-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                Preview Surat
            </a>

            <!-- Download PDF -->
            <a href="{{ route('pemeriksaan.download', $pemeriksaan->id) }}" target="_blank" class="px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-2xs transition duration-150 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download PDF
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200/80 p-4 rounded-xl shadow-2xs" role="alert">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-emerald-900">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- SECTION 1: DATA MABA -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200/80 bg-slate-50/60 flex justify-between items-center">
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">DATA MABA</h3>
                <p class="mt-0.5 text-xs text-slate-500">Informasi identitas maba yang diperiksa.</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-medium text-slate-500">Tahun Maba: <strong class="font-semibold text-slate-800">{{ $pemeriksaan->tahun_masuk }}</strong></span>
            </div>
        </div>
        <div>
            <dl class="divide-y divide-slate-200/70 text-xs">
                <div class="px-5 py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-slate-50/80 transition duration-150">
                    <dt class="font-medium text-slate-500">Nama Lengkap</dt>
                    <dd class="mt-1 font-bold text-slate-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->nama }}</dd>
                </div>
                <div class="px-5 py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-slate-50/80 transition duration-150">
                    <dt class="font-medium text-slate-500">NIK / Email</dt>
                    <dd class="mt-1 text-slate-800 font-medium sm:mt-0 sm:col-span-2">{{ $pemeriksaan->nik ?? '-' }} <span class="text-slate-300 mx-2">|</span> {{ $pemeriksaan->email }}</dd>
                </div>
                <div class="px-5 py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-slate-50/80 transition duration-150">
                    <dt class="font-medium text-slate-500">Fakultas / Program Studi</dt>
                    <dd class="mt-1 font-semibold text-slate-900 sm:mt-0 sm:col-span-2">
                        {{ $pemeriksaan->fakultas ?? '-' }} <span class="text-slate-300 mx-2">|</span> 
                        {{ $pemeriksaan->mabaData ? ($pemeriksaan->mabaData->program_studi_biro ?? $pemeriksaan->mabaData->program_studi) : '-' }}
                    </dd>
                </div>
                <div class="px-5 py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-slate-50/80 transition duration-150">
                    <dt class="font-medium text-slate-500">Jadwal Pemeriksaan</dt>
                    <dd class="mt-1 text-slate-800 font-medium sm:mt-0 sm:col-span-2">
                        @if($pemeriksaan->mabaData && $pemeriksaan->mabaData->tanggal_jadwal)
                            {{ $pemeriksaan->mabaData->tanggal_jadwal->format('d/m/Y') }} (Sesi {{ $pemeriksaan->mabaData->sesi_jadwal ?? '-' }})
                        @else
                            {{ $pemeriksaan->created_at->format('d/m/Y H:i') }}
                        @endif
                    </dd>
                </div>
                <div class="px-5 py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-slate-50/80 transition duration-150">
                    <dt class="font-medium text-slate-500">Tempat, Tanggal Lahir / Umur</dt>
                    <dd class="mt-1 text-slate-800 font-medium sm:mt-0 sm:col-span-2">
                        {{ $pemeriksaan->tempat_lahir ?? '-' }}, {{ $pemeriksaan->tanggal_lahir ? \Carbon\Carbon::parse($pemeriksaan->tanggal_lahir)->locale('id')->translatedFormat('d F Y') : '-' }} 
                        <span class="text-slate-300 mx-2">|</span> {{ $pemeriksaan->umur ?? '-' }} Tahun ({{ $pemeriksaan->jenis_kelamin ?? '-' }})
                    </dd>
                </div>
                <div class="px-5 py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-slate-50/80 transition duration-150">
                    <dt class="font-medium text-slate-500">Alamat</dt>
                    <dd class="mt-1 text-slate-800 font-medium sm:mt-0 sm:col-span-2">{{ $pemeriksaan->alamat ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- SECTION 2: DATA PEMERIKSAAN MEDIS -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 bg-emerald-50/60 border-b border-emerald-200/70 flex justify-between items-center">
            <div>
                <h3 class="text-xs font-bold text-emerald-950 uppercase tracking-wider">DATA PEMERIKSAAN MEDIS</h3>
                <p class="mt-1 text-base font-extrabold text-emerald-700">Dinyatakan: {{ $pemeriksaan->kesimpulan }}</p>
                <p class="mt-0.5 text-xs font-medium text-emerald-800">Keperluan: {{ $pemeriksaan->keperluan ?? '-' }}</p>
            </div>
            <div class="text-right">
                <div class="font-mono text-xs font-bold text-emerald-900 bg-white px-2.5 py-1 rounded-md border border-emerald-200/80 shadow-2xs inline-block">
                    {{ $pemeriksaan->nomor_surat ?? 'Belum Terbit' }}
                </div>
                <p class="text-[11px] text-emerald-800/80 mt-1 font-medium">Dokter: {{ $pemeriksaan->dokter_nama ?? '-' }}</p>
            </div>
        </div>
        <div>
            <dl class="divide-y divide-slate-200/70 text-xs">
                <div class="px-5 py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-slate-50/80 transition duration-150">
                    <dt class="font-medium text-slate-500">Tanggal Pemeriksaan</dt>
                    <dd class="mt-1 font-semibold text-slate-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->created_at->format('d F Y H:i') }}</dd>
                </div>
                <div class="px-5 py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-slate-50/80 transition duration-150">
                    <dt class="font-medium text-slate-500">Tinggi / Berat Badan</dt>
                    <dd class="mt-1 font-semibold text-slate-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->tinggi_badan ?? '-' }} cm <span class="text-slate-300 mx-2">|</span> {{ $pemeriksaan->berat_badan ?? '-' }} kg</dd>
                </div>
                <div class="px-5 py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-slate-50/80 transition duration-150">
                    <dt class="font-medium text-slate-500">Tekanan Darah</dt>
                    <dd class="mt-1 font-semibold text-slate-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->tekanan_darah ?? '-' }} Mm/hg</dd>
                </div>
                <div class="px-5 py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-slate-50/80 transition duration-150">
                    <dt class="font-medium text-slate-500">Golongan Darah / Buta Warna</dt>
                    <dd class="mt-1 font-semibold text-slate-900 sm:mt-0 sm:col-span-2">{{ $pemeriksaan->golongan_darah ?? '-' }} <span class="text-slate-300 mx-2">|</span> {{ $pemeriksaan->buta_warna ?? '-' }}</dd>
                </div>
                <div class="px-5 py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-slate-50/80 transition duration-150">
                    <dt class="font-medium text-slate-500">Riwayat Penyakit Kronis</dt>
                    <dd class="mt-1 text-slate-800 font-medium sm:mt-0 sm:col-span-2">{{ $pemeriksaan->riwayat_penyakit_kronis ?? '-' }}</dd>
                </div>
                <div class="px-5 py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-slate-50/80 transition duration-150">
                    <dt class="font-medium text-slate-500">Riwayat Penggunaan Obat</dt>
                    <dd class="mt-1 text-slate-800 font-medium sm:mt-0 sm:col-span-2">{{ $pemeriksaan->riwayat_penggunaan_obat ?? '-' }}</dd>
                </div>
                <div class="px-5 py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 hover:bg-slate-50/80 transition duration-150">
                    <dt class="font-medium text-slate-500">Riwayat Alergi</dt>
                    <dd class="mt-1 text-slate-800 font-medium sm:mt-0 sm:col-span-2">{{ $pemeriksaan->riwayat_alergi ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- SECTION 3: STATUS PENGIRIMAN EMAIL -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="px-5 py-4 bg-sky-50/60 border-b border-sky-200/70 flex justify-between items-center">
            <div>
                <h3 class="text-xs font-bold text-sky-950 uppercase tracking-wider">PENGIRIMAN EMAIL SURAT SEHAT</h3>
                <p class="text-[11px] text-sky-800/80 mt-0.5 font-medium">Status antrean dan log pengiriman PDF ke email maba.</p>
            </div>
            <div>
                @if($pemeriksaan->status_pengiriman === 'Terkirim')
                    <span class="px-2.5 py-1 inline-flex items-center gap-1 text-xs font-semibold rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        Sudah Terkirim
                    </span>
                @elseif($pemeriksaan->status_pengiriman === 'Gagal')
                    <span class="px-2.5 py-1 inline-flex items-center gap-1 text-xs font-semibold rounded-md bg-rose-50 text-rose-800 border border-rose-200/80">
                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Gagal
                    </span>
                @elseif(in_array($pemeriksaan->status_pengiriman, ['Dalam antrean', 'Mengirim']))
                    <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-md bg-sky-50 text-sky-800 border border-sky-200/80">
                        {{ $pemeriksaan->status_pengiriman }}
                    </span>
                @else
                    <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-md bg-slate-100 text-slate-600 border border-slate-200">
                        Belum dikirim
                    </span>
                @endif
            </div>
        </div>
        <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
            <div>
                <div class="text-slate-500">Tujuan Email: <span class="font-semibold text-slate-800">{{ $pemeriksaan->email ?? '-' }}</span></div>
                <div class="text-slate-500 mt-1">Waktu Pengiriman: <span class="font-semibold text-slate-800">{{ $pemeriksaan->waktu_pengiriman ? \Carbon\Carbon::parse($pemeriksaan->waktu_pengiriman)->format('d F Y H:i:s') : '-' }}</span></div>
            </div>
            <div>
                @if($pemeriksaan->status_pengiriman === 'Terkirim' || $pemeriksaan->status_pengiriman === 'Gagal')
                    <form action="{{ route('pemeriksaan.sendEmail', $pemeriksaan->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold text-xs rounded-lg shadow-2xs transition duration-150">
                            Kirim Ulang Email
                        </button>
                    </form>
                @elseif(!in_array($pemeriksaan->status_pengiriman, ['Dalam antrean', 'Mengirim']))
                    <form action="{{ route('pemeriksaan.sendEmail', $pemeriksaan->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-2xs transition duration-150">
                            Kirim Email Sekarang
                        </button>
                    </form>
                @else
                    <button disabled class="px-3.5 py-1.5 bg-slate-100 text-slate-400 font-semibold text-xs rounded-lg border border-slate-200 cursor-not-allowed">
                        Dalam Antrean...
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- SECTION 4: DESTRUCTIVE ACTION -->
    <div class="pt-2 flex justify-end pb-8">
        <form action="{{ route('pemeriksaan.destroy', $pemeriksaan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini secara permanen?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline transition duration-150 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Hapus Data Permanen
            </button>
        </form>
    </div>
</div>
@endsection

