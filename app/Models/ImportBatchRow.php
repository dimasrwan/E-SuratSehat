<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatchRow extends Model
{
    protected $table = 'import_batch_rows';

    protected $fillable = [
        'import_batch_id',
        'row_number',
        'classification',
        'nama_biro',
        'program_studi_biro',
        'tanggal_jadwal',
        'sesi_jadwal',
        'waktu_jadwal',
        'existing_maba_data_id',
        'error_messages',
        'selected_action',
    ];

    protected $casts = [
        'row_number' => 'integer',
        'tanggal_jadwal' => 'date',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function existingMabaData(): BelongsTo
    {
        return $this->belongsTo(MabaData::class, 'existing_maba_data_id');
    }
}
