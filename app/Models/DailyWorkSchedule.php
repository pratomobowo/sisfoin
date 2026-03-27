<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class DailyWorkSchedule extends Model
{
    protected $fillable = [
        'day_of_week',
        'day_name',
        'start_time',
        'end_time',
        'early_arrival_threshold',
        'late_tolerance_minutes',
        'work_hours',
        'is_active',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'late_tolerance_minutes' => 'integer',
        'work_hours' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the schedule for a specific day of week (1=Monday, 6=Saturday)
     */
    public static function getScheduleForDay(int $dayOfWeek): ?self
    {
        return Cache::remember("daily_work_schedule_{$dayOfWeek}", 3600, function () use ($dayOfWeek) {
            return static::where('day_of_week', $dayOfWeek)->first();
        });
    }

    /**
     * Get all schedules ordered by day
     */
    public static function getAllSchedules()
    {
        return static::orderBy('day_of_week')->get();
    }

    /**
     * Check if a day is an active work day
     */
    public static function isWorkDay(int $dayOfWeek): bool
    {
        $schedule = static::getScheduleForDay($dayOfWeek);
        return $schedule ? $schedule->is_active : false;
    }

    /**
     * Clear all cached schedules
     */
    public static function clearCache(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            Cache::forget("daily_work_schedule_{$i}");
        }
    }
}
