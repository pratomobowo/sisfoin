<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemServiceExecutionLog extends Model
{
    protected $fillable = [
        'system_service_id',
        'service_key',
        'service_name',
        'command',
        'triggered_by',
        'triggered_by_user_id',
        'started_at',
        'finished_at',
        'status',
        'exit_code',
        'message',
        'output',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(SystemService::class, 'system_service_id');
    }

    public function triggerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }
}
