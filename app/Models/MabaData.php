<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MabaData extends Model
{
    protected $table = 'maba_datas';

    protected $attributes = [
        'status_biodata' => 'BELUM_MENGISI',
    ];

    protected $fillable = [
        'tahun_maba_id',
        'nama_biro',
        'program_studi_biro',
        'tanggal_jadwal',
        'sesi_jadwal',
        'waktu_jadwal',
        'nama_lengkap',
        'nik',
        'email',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'fakultas',
        'program_studi',
        'pekerjaan',
        'alamat',
        'status_biodata',
        'catatan_perbaikan',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'tanggal_jadwal' => 'date',
        'tanggal_lahir' => 'date',
        'verified_at' => 'datetime',
    ];

    /**
     * Relationship to master Tahun Maba.
     */
    public function tahunMaba(): BelongsTo
    {
        return $this->belongsTo(TahunMaba::class, 'tahun_maba_id');
    }

    /**
     * Relationship to Verifier (User/Operator).
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Relationship to Pemeriksaan (1:0..1).
     */
    public function pemeriksaan(): HasOne
    {
        return $this->hasOne(Pemeriksaan::class, 'maba_data_id');
    }

    /**
     * Server-side calculated age from tanggal_lahir.
     */
    public function getUmurAttribute(): ?int
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->age : null;
    }
}
