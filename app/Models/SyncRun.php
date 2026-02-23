<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyncRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'mode',
        'status',
        'triggered_by',
        'idempotency_key',
        'fetched_count',
        'processed_count',
        'inserted_count',
        'updated_count',
        'skipped_count',
        'failed_count',
        'error_summary',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'error_summary' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SyncRunItem::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
