<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunMaba extends Model
{
    protected $table = 'tahun_mabas';

    protected $fillable = [
        'tahun',
        'nama',
        'is_active',
    ];

    protected $casts = [
        'tahun' => 'integer',
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
}
