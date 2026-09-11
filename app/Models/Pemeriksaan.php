<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemeriksaan extends Model
{
    protected $attributes = [
        'tahun_masuk' => 2026,
    ];

    protected $fillable = [
        'nomor_surat', 'tahun_masuk', 'nama', 'email', 'nik', 'umur', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
        'agama', 'fakultas', 'pekerjaan', 'alamat',
        'golongan_darah', 'tinggi_badan', 'berat_badan', 'tekanan_darah',
        'buta_warna', 'riwayat_penyakit_kronis', 'riwayat_penggunaan_obat', 'riwayat_alergi',
        'kesimpulan', 'keperluan', 'dokter_nama', 'dokter_nip',
        'status_pengiriman', 'waktu_pengiriman'
    ];
}
