<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fakultas extends Model
{
    use HasFactory;

    protected $table = 'fakultas';

    protected $fillable = [
        'nama',
        'kode',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function programStudi(): HasMany
    {
        return $this->hasMany(ProgramStudi::class, 'fakultas_id')->orderBy('sort_order', 'asc');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
