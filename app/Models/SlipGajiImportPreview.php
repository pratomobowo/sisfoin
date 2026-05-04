<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlipGajiImportPreview extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'user_id',
        'periode',
        'mode',
        'file_original',
        'status',
        'summary_json',
        'row_count',
        'error_count',
        'expires_at',
    ];

    protected $casts = [
        'summary_json' => 'array',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(SlipGajiImportPreviewRow::class, 'preview_id');
    }
}
