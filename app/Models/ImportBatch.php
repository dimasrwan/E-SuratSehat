<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    protected $table = 'import_batches';

    protected $fillable = [
        'tahun_maba_id',
        'user_id',
        'original_filename',
        'stored_filename',
        'status',
        'total_rows',
        'new_rows',
        'possible_duplicate_rows',
        'exact_duplicate_rows',
        'error_rows',
        'inserted_rows',
        'updated_rows',
        'skipped_rows',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'new_rows' => 'integer',
        'possible_duplicate_rows' => 'integer',
        'exact_duplicate_rows' => 'integer',
        'error_rows' => 'integer',
        'inserted_rows' => 'integer',
        'updated_rows' => 'integer',
        'skipped_rows' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tahunMaba(): BelongsTo
    {
        return $this->belongsTo(TahunMaba::class, 'tahun_maba_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportBatchRow::class, 'import_batch_id');
    }
}
