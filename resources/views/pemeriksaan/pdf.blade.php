<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Sehat - {{ $pemeriksaan->nama }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11.5pt;
            line-height: 1.4;
            margin: 0;
            padding: 10px 40px;
            color: #000;
        }
        .header {
            text-align: center;
            padding-bottom: 2px;
            margin-bottom: 2px;
            position: relative;
        }
        .header-bottom-line {
            border-bottom: 1px solid #000;
            margin-bottom: 15px;
        }
        .logo {
            position: absolute;
            left: 10px;
            top: 5px;
            width: 80px;
            height: auto;
        }
        .header-text {
            margin-left: 0;
        }
        .header h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
        }
        .header h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
        }
        .header h4 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            font-size: 10pt;
        }
        .title {
            text-align: center;
            margin-bottom: 15px;
        }
        .title h3 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
        }
        .title p {
            margin: 0;
            font-size: 11.5pt;
            font-weight: bold;
        }
        .content {
            text-align: justify;
        }
        table.info-table {
            width: 100%;
            margin-bottom: 5px;
        }
        table.info-table td {
            vertical-align: top;
            padding: 1px 0;
        }
        table.info-table td.label {
            width: 170px;
        }
        table.info-table td.colon {
            width: 15px;
        }
        .indent-table td.label {
            width: 150px;
            padding-left: 20px;
        }
        .result-list {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 20px;
        }
        .result-list div {
            margin-bottom: 2px;
        }
        .result-inline {
            margin-left: 20px;
            margin-bottom: 10px;
        }
        .conclusion {
            text-align: center;
            font-weight: bold;
            margin: 15px 0;
            font-size: 11.5pt;
        }
        .footer-sig {
            margin-top: 20px;
            float: right;
            text-align: center;
            width: 280px;
        }
        .footer-sig p {
            margin: 0;
        }
        .footer-sig .signature-space {
            height: 80px;
            position: relative;
        }
        .footer-sig .signature-space img {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 120px;
            opacity: 0.8;
        }
        .page-footer {
            position: absolute;
            bottom: 30px;
            left: 40px;
            width: calc(100% - 80px);
            font-style: italic;
            color: #2b6cb0;
            font-size: 11pt;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .footer-logos img {
            height: 35px;
            margin-left: 10px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

    <table class="header" style="width: 100%; border-collapse: collapse; border-spacing: 0;">
        <tr>
            <td style="width: 15%; text-align: left; vertical-align: middle; padding: 0 0 5px 0;">
                <img src="{{ public_path('images/logo_uin.png') }}" style="width: 3.5cm; height: auto;" alt="Logo UIN">
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
            <img src="{{ public_path('images/ttd_nadia.png') }}" style="height: 70px; position: absolute; left: 10px; top: -5px;">
            @elseif(($pemeriksaan->dokter_nama ?? 'dr. Desminawati') == 'dr. Desminawati')
            <img src="{{ public_path('images/ttd_desminawati.png') }}" style="height: 70px; position: absolute; left: 10px; top: -5px;">
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
        <img src="{{ public_path('images/blu.png') }}" style="height: 32px; margin-left: 15px; vertical-align: middle;">
        <img src="{{ public_path('images/unggul.png') }}" style="height: 32px; margin-left: 15px; vertical-align: middle;">
        <img src="{{ public_path('images/pusaka.png') }}" style="height: 32px; margin-left: 15px; vertical-align: middle;">
    </div>

    <div class="page-footer">
        <table style="width: 100%;">
            <tr>
                <td></td>
                <td style="text-align: right;" class="footer-logos">
                    <!-- Right logos can be added here -->
                    <!-- <img src="{{ public_path('images/blu.png') }}">
                    <img src="{{ public_path('images/unggul.png') }}">
                    <img src="{{ public_path('images/pusaka.png') }}"> -->
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
