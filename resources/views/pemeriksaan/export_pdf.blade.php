<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Export Data Pemeriksaan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>Rekapitulasi Data Pemeriksaan</h2>
    
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 3%;">No</th>
                <th style="width: 12%;">Nomor Surat</th>
                <th style="width: 15%;">Nama Lengkap</th>
                <th style="width: 10%;">NIK</th>
                <th style="width: 8%;">Umur / JK</th>
                <th style="width: 12%;">Fakultas / Pekerjaan</th>
                <th style="width: 10%;">Kesimpulan</th>
                <th style="width: 15%;">Keperluan</th>
                <th style="width: 7%;">Status</th>
                <th style="width: 8%;">Tgl Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pemeriksaans as $index => $pemeriksaan)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $pemeriksaan->nomor_surat }}</td>
                <td>{{ $pemeriksaan->nama }}</td>
                <td>{{ $pemeriksaan->nik }}</td>
                <td>{{ $pemeriksaan->umur }} th / {{ $pemeriksaan->jenis_kelamin == 'Laki-Laki' ? 'L' : ($pemeriksaan->jenis_kelamin == 'Perempuan' ? 'P' : '-') }}</td>
                <td>{{ $pemeriksaan->fakultas }} / {{ $pemeriksaan->pekerjaan }}</td>
                <td><strong>{{ $pemeriksaan->kesimpulan }}</strong></td>
                <td>{{ $pemeriksaan->keperluan }}</td>
                <td>{{ $pemeriksaan->status_pengiriman }}</td>
                <td>{{ $pemeriksaan->created_at ? $pemeriksaan->created_at->format('d/m/Y') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
