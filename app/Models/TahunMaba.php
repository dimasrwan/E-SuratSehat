<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunMaba extends Model
{
    protected $table = 'tahun_mabas';

    protected $fillable = [
        'tahun',
        'nama',
        'nomor_surat_mulai',
        'kode_unit',
        'kode_bagian',
        'tahun_surat',
        'is_active',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'nomor_surat_mulai' => 'integer',
        'tahun_surat' => 'integer',
        'is_active' => 'boolean',
    ];

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Relationship to MabaData records under this batch.
     */
    public function mabaDatas(): HasMany
    {
        return $this->hasMany(MabaData::class, 'tahun_maba_id');
    }
}
