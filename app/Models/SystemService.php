<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemService extends Model
{
    public const SCHEDULE_PRESETS = [
        'disabled' => 'Manual (Tidak Dijadwalkan)',
        'every_5_minutes' => 'Setiap 5 Menit',
        'every_10_minutes' => 'Setiap 10 Menit',
        'every_15_minutes' => 'Setiap 15 Menit',
        'every_30_minutes' => 'Setiap 30 Menit',
        'hourly' => 'Setiap 1 Jam',
        'daily_00_00' => 'Harian 00:00',
        'daily_01_00' => 'Harian 01:00',
        'daily_02_00' => 'Harian 02:00',
        'daily_03_00' => 'Harian 03:00',
    ];

    protected $fillable = [
        'key',
        'name',
        'description',
        'is_active',
        'status',
        'schedule_preset',
        'last_run_at',
        'last_run_started_at',
        'last_run_finished_at',
        'last_run_result',
        'last_run_message',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'last_run_started_at' => 'datetime',
        'last_run_finished_at' => 'datetime',
    ];

    public function getSchedulePresetLabelAttribute(): string
    {
        return self::SCHEDULE_PRESETS[$this->schedule_preset] ?? 'Custom';
    }
}
