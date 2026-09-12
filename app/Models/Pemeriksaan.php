<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pemeriksaan extends Model
{
    protected $attributes = [
        'tahun_masuk' => 2026,
    ];

    protected $fillable = [
        'maba_data_id',
        'nomor_surat', 'tahun_masuk', 'nama', 'email', 'nik', 'umur', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
        'agama', 'fakultas', 'pekerjaan', 'alamat',
        'golongan_darah', 'tinggi_badan', 'berat_badan', 'tekanan_darah',
        'buta_warna', 'riwayat_penyakit_kronis', 'riwayat_penggunaan_obat', 'riwayat_alergi',
        'kesimpulan', 'keperluan', 'dokter_nama', 'dokter_nip',
        'status_pengiriman', 'waktu_pengiriman'
    ];

    /**
     * Relationship to MabaData (BelongsTo with Nullable FK for Legacy Compatibility).
     */
    public function mabaData(): BelongsTo
    {
        return $this->belongsTo(MabaData::class, 'maba_data_id');
    }

    // Accessor Fallbacks: Menggunakan data dari MabaData jika terhubung,
    // atau fallback ke kolom legacy pada tabel pemeriksaans jika mabaData NULL.

    public function getNamaAttribute($value)
    {
        return $this->mabaData ? ($this->mabaData->nama_lengkap ?? $value) : $value;
    }

    public function getEmailAttribute($value)
    {
        return $this->mabaData ? ($this->mabaData->email ?? $value) : $value;
    }

    public function getNikAttribute($value)
    {
        return $this->mabaData ? ($this->mabaData->nik ?? $value) : $value;
    }

    public function getTempatLahirAttribute($value)
    {
        return $this->mabaData ? ($this->mabaData->tempat_lahir ?? $value) : $value;
    }

    public function getTanggalLahirAttribute($value)
    {
        return $this->mabaData ? ($this->mabaData->tanggal_lahir ?? $value) : $value;
    }

    public function getJenisKelaminAttribute($value)
    {
        return $this->mabaData ? ($this->mabaData->jenis_kelamin ?? $value) : $value;
    }

    public function getAgamaAttribute($value)
    {
        return $this->mabaData ? ($this->mabaData->agama ?? $value) : $value;
    }

    public function getFakultasAttribute($value)
    {
        return $this->mabaData ? ($this->mabaData->fakultas ?? $value) : $value;
    }

    public function getProgramStudiAttribute($value)
    {
        return $this->mabaData ? ($this->mabaData->program_studi ?? $value) : $value;
    }

    public function getPekerjaanAttribute($value)
    {
        return $this->mabaData ? ($this->mabaData->pekerjaan ?? $value) : $value;
    }

    public function getAlamatAttribute($value)
    {
        return $this->mabaData ? ($this->mabaData->alamat ?? $value) : $value;
    }
}
