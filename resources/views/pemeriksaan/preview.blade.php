@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header & Action Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 py-1">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Preview Surat Keterangan Sehat</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">Pratinjau surat sebelum dikirim atau diunduh.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Kembali -->
            <a href="{{ route('pemeriksaan.show', $pemeriksaan->id) }}" class="px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold text-xs rounded-lg shadow-2xs transition duration-150 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>

            <!-- Kirim ke Email -->
            <form action="{{ route('pemeriksaan.sendEmail', $pemeriksaan->id) }}" method="POST" class="inline" onsubmit="return showLoading(this)">
                @csrf
                <button type="submit" id="btn-kirim" class="px-3.5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs rounded-lg shadow-2xs transition duration-150 flex items-center gap-1.5">
                    <svg id="icon-normal" class="w-3.5 h-3.5 text-sky-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <svg id="icon-loading" class="w-3.5 h-3.5 hidden animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-100" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span id="text-kirim">Kirim ke Email</span>
                </button>
            </form>

            <!-- Download PDF -->
            <a href="{{ route('pemeriksaan.download', $pemeriksaan->id) }}" target="_blank" class="px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded-lg shadow-2xs transition duration-150 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download PDF
            </a>
        </div>
    </div>

    <!-- Paper Container (Clean Modern Document Viewer Frame) -->
    <div class="bg-slate-200/70 p-4 sm:p-8 rounded-xl border border-slate-300/80 shadow-2xs flex justify-center overflow-x-auto">
        <div class="bg-white shadow-xl rounded-xs" style="width: 210mm; min-height: 297mm; padding: 10px 40px; position: relative; box-sizing: border-box; overflow: hidden;">
            {{-- === EXACT COPY OF PDF CSS === --}}
            <style scoped>
                .pdf-body {
                    font-family: 'Times New Roman', Times, serif;
                    font-size: 11.5pt;
                    line-height: 1.4;
                    color: #000;
                }
                .pdf-body .header {
                    text-align: center;
                    padding-bottom: 2px;
                    margin-bottom: 2px;
                    position: relative;
                }
                .pdf-body .header h3 {
                    margin: 0;
                    font-size: 13pt;
                    font-weight: bold;
                }
                .pdf-body .header h2 {
                    margin: 0;
                    font-size: 13pt;
                    font-weight: bold;
                }
                .pdf-body .header h4 {
                    margin: 0;
                    font-size: 12pt;
                    font-weight: bold;
                }
                .pdf-body .header p {
                    margin: 0;
                    font-size: 10pt;
                }
                .pdf-body .title {
                    text-align: center;
                    margin-bottom: 15px;
                }
                .pdf-body .title h3 {
                    margin: 0;
                    font-size: 12pt;
                    font-weight: bold;
                    text-decoration: underline;
                }
                .pdf-body .title p {
                    margin: 0;
                    font-size: 11.5pt;
                    font-weight: bold;
                }
                .pdf-body .content {
                    text-align: justify;
                }
                .pdf-body table.info-table {
                    width: 100%;
                    margin-bottom: 5px;
                }
                .pdf-body table.info-table td {
                    vertical-align: top;
                    padding: 1px 0;
                }
                .pdf-body table.info-table td.label {
                    width: 170px;
                }
                .pdf-body table.info-table td.colon {
                    width: 15px;
                }
                .pdf-body .indent-table td.label {
                    width: 150px;
                    padding-left: 20px;
                }
                .pdf-body .result-list {
                    margin-top: 5px;
                    margin-bottom: 5px;
                    padding-left: 20px;
                }
                .pdf-body .result-list div {
                    margin-bottom: 2px;
                }
                .pdf-body .conclusion {
                    text-align: center;
                    font-weight: bold;
                    margin: 15px 0;
                    font-size: 11.5pt;
                }
                .pdf-body .footer-sig {
                    margin-top: 20px;
                    float: right;
                    text-align: left;
                    width: 280px;
                }
                .pdf-body .footer-sig p {
                    margin: 0;
                }
            </style>

            {{-- === EXACT COPY OF PDF BODY === --}}
            <div class="pdf-body">

                <table class="header" style="width: 100%; border-collapse: collapse; border-spacing: 0;">
                    <tr>
                        <td style="width: 15%; text-align: left; vertical-align: middle; padding: 0 0 5px 0;">
                            <img src="{{ asset('images/logo_uin.png') }}" style="width: 3.5cm; height: auto;" alt="Logo UIN">
                        </td>
                        <td style="width: 70%; text-align: center; vertical-align: middle; white-space: nowrap; padding: 0 0 5px 0;">
                                <h2 style="font-size: 13pt; margin: 0; line-height: 1;">KEMENTERIAN AGAMA REPUBLIK INDONESIA</h2>
                                <h1 style="font-size: 14pt; margin: 2px 0; line-height: 1;">UNIVERSITAS ISLAM NEGERI AR-RANIRY BANDA ACEH</h1>
                                <h2 style="font-size: 13pt; margin: 0 0 2px 0; line-height: 1;">PUSAT PELAYANAN KESEHATAN</h2>
                                <p style="font-size: 10pt; margin: 0; line-height: 1;">Jln. Syeikh Abdul Rauf Kopelma Darussalam Banda Aceh</p>
                                <p style="font-size: 10pt; margin: 2px 0 0 0; line-height: 1;">Email : ppkes.uin@ar-raniry.ac.id</p>
                        </td>
                        <td style="width: 15%; padding: 0 0 5px 0;"></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding: 0;">
                            <div style="border-top: 3px solid #000; width: 100%;"></div>
                            <div style="border-top: 1px solid #000; width: 100%; margin-top: 2px; margin-bottom: 15px;"></div>
                        </td>
                    </tr>
                </table>

                <div class="title">
                    <h3>SURAT KETERANGAN SEHAT</h3>
                    <p>No: {{ $pemeriksaan->nomor_surat ?? '..................................' }}</p>
                </div>

                <div class="content">
                    <p style="margin-bottom: 5px;">Yang bertanda tangan di bawah ini :</p>
                    <table class="info-table indent-table" style="margin-left: 42px;">
                        <tr>
                            <td class="label">Nama</td>
                            <td class="colon">:</td>
                            <td><strong>{{ $pemeriksaan->dokter_nama ?? 'dr. Desminawati' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">SIP</td>
                            <td class="colon">:</td>
                            <td>No.446/136.DU/2019</td>
                        </tr>
                    </table>

                    <p style="margin-bottom: 5px; margin-top: 5px;">Dokter pada Klinik Universitas Islam Negeri Ar-Raniry (UIN) Darussalam Banda Aceh dengan ini menerangkan bahwa:</p>
                    
                    <table class="info-table indent-table" style="margin-left: 42px;">
                        <tr>
                            <td class="label">Nama</td>
                            <td class="colon">:</td>
                            <td>{{ $pemeriksaan->nama }}</td>
                        </tr>
                        <tr>
                            <td class="label">Tempat / Tgl. Lahir</td>
                            <td class="colon">:</td>
                            <td>{{ $pemeriksaan->tempat_lahir ?? '-' }}, {{ $pemeriksaan->tanggal_lahir ? \Carbon\Carbon::parse($pemeriksaan->tanggal_lahir)->locale('id')->translatedFormat('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Jenis kelamin</td>
                            <td class="colon">:</td>
                            <td>{{ $pemeriksaan->jenis_kelamin ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Agama</td>
                            <td class="colon">:</td>
                            <td>{{ $pemeriksaan->agama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Pekerjaan / Fakultas</td>
                            <td class="colon">:</td>
                            <td>{{ $pemeriksaan->pekerjaan ?? '-' }} / {{ $pemeriksaan->fakultas ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Alamat</td>
                            <td class="colon">:</td>
                            <td>{{ $pemeriksaan->alamat ?? '-' }}</td>
                        </tr>
                    </table>

                    <p style="margin-bottom: 5px; margin-top: 5px;">Pada hari ini telah diperiksa dengan hasil sebagai berikut :</p>
                    <div class="result-list" style="margin-left: 42px;">
                        <div>- Riwayat Penyakit Dahulu : {{ $pemeriksaan->riwayat_penyakit_kronis ?? 'Disangkal' }}</div>
                        <div>- Riwayat Penggunaan Obat : {{ $pemeriksaan->riwayat_penggunaan_obat ?? 'Disangkal' }}</div>
                        <div>- Pemeriksaan Fisik : dbn</div>
                        <div style="margin-top: 5px;">- Tinggi Badan : {{ $pemeriksaan->tinggi_badan ?? '...' }} Cm &nbsp;&nbsp;&nbsp; BB : {{ $pemeriksaan->berat_badan ?? '...' }} Kg &nbsp;&nbsp;&nbsp; Gol Darah : ({{ $pemeriksaan->golongan_darah ?? '-' }}) &nbsp;&nbsp;&nbsp; TD : {{ $pemeriksaan->tekanan_darah ?? '...' }} Mmhg</div>
                    </div>

                    <p style="margin-bottom: 5px;">Dan Dinyatakan :</p>
                    <div class="conclusion" style="line-height: 1;">
                        ==" {{ strtoupper($pemeriksaan->kesimpulan ?? 'SEHAT DAN TIDAK BUTA WARNA') }} "==<br>
                        == {{ strtoupper($pemeriksaan->keperluan ?? 'SYARAT ADMINISTRASI MASUK KULIAH') }} ==
                    </div>
                    
                    <p style="margin-top: 15px;">Demikianlah surat keterangan ini dibuat dengan sebenarnya agar dapat di pergunakan seperlunya.</p>
                </div>

                <div class="footer-sig" style="text-align: left;">
                    <p style="margin-top: 0; margin-bottom: 0; line-height: 1;">
                        Banda Aceh, {{ \Carbon\Carbon::parse($pemeriksaan->created_at)->locale('id')->translatedFormat('d F Y') }}<br>
                        Dokter Pusat Pelayanan Kesehatan,<br>
                        UIN Ar-Raniry
                    </p>
                    <div style="height: 60px; position: relative;">
                        @if(($pemeriksaan->dokter_nama ?? '') == 'dr. Nadia Fajri, M.K.M')
                        <img src="{{ asset('images/ttd_nadia.png') }}" style="height: 70px; position: absolute; left: 10px; top: -5px;">
                        @elseif(($pemeriksaan->dokter_nama ?? 'dr. Desminawati') == 'dr. Desminawati')
                        <img src="{{ asset('images/ttd_desminawati.png') }}" style="height: 70px; position: absolute; left: 10px; top: -5px;">
                        @endif
                    </div>
                    <p style="margin-bottom: 0; margin-top: 0; line-height: 1;">
                        <span style="text-decoration: underline; font-weight: bold;">{{ $pemeriksaan->dokter_nama ?? 'dr. Desminawati' }}</span><br>
                        NIP. {{ $pemeriksaan->dokter_nip ?? '198002062010012007' }}
                    </p>
                </div>

                <div style="clear: both; padding-top: 20px;">
                    <p style="color: #00a2e8; font-style: italic; font-family: Cambria, Georgia, serif; font-size: 16px; margin: 0;">Energi Kebangsaan, Sinergi Membangun Negeri</p>
                </div>
                <div style="text-align: right; margin-top: 5px;">
                    <img src="{{ asset('images/blu.png') }}" style="height: 32px; margin-left: 15px; vertical-align: middle;">
                    <img src="{{ asset('images/unggul.png') }}" style="height: 32px; margin-left: 15px; vertical-align: middle;">
                    <img src="{{ asset('images/pusaka.png') }}" style="height: 32px; margin-left: 15px; vertical-align: middle;">
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showLoading(form) {
        if (event) event.preventDefault();
        const btn = document.getElementById('btn-kirim');
        const iconNormal = document.getElementById('icon-normal');
        const iconLoading = document.getElementById('icon-loading');
        const textKirim = document.getElementById('text-kirim');
        
        if (btn.disabled || btn.classList.contains('cursor-not-allowed')) return false;

        btn.disabled = true;
        btn.style.pointerEvents = 'none';
        btn.classList.add('cursor-not-allowed');
        btn.style.filter = 'brightness(0.85)';
        iconNormal.classList.add('hidden');
        iconLoading.classList.remove('hidden');
        textKirim.innerText = 'Mengirim...';

        const formData = new FormData(form);
        fetch(form.action, {
            method: form.method,
            headers: { 'Accept': 'application/json' },
            body: formData,
            keepalive: true
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            window.location.reload();
        })
        .catch(err => {
            alert('Terjadi kesalahan. Silakan coba lagi.');
            window.location.reload();
        });

        return false;
    }
</script>
@endpush

