<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlipGajiImportPreviewRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'preview_id',
        'row_number',
        'nip',
        'nama',
        'net_amount',
        'gross_amount',
        'deduction_amount',
        'data_json',
        'validation_status',
        'validation_errors_json',
    ];

    protected $casts = [
        'net_amount' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'data_json' => 'array',
        'validation_errors_json' => 'array',
    ];

    public function preview(): BelongsTo
    {
        return $this->belongsTo(SlipGajiImportPreview::class, 'preview_id');
    }
}
