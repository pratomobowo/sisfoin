<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class WorkShift extends Model
{
    protected $fillable = [
        'name',
        'code',
        'start_time',
        'end_time',
        'early_arrival_threshold',
        'late_tolerance_minutes',
        'work_hours',
        'color',
        'is_default',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'work_hours' => 'decimal:2',
        'late_tolerance_minutes' => 'integer',
    ];

    /**
     * Unit assignments for this shift
     */
    public function unitAssignments(): HasMany
    {
        return $this->hasMany(UnitShiftAssignment::class);
    }

    /**
     * User overrides for this shift
     */
    public function userOverrides(): HasMany
    {
        return $this->hasMany(UserShiftOverride::class);
    }

    /**
     * Scope for active shifts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the default shift
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Calculate late threshold time
     */
    public function getLateThresholdAttribute(): string
    {
        $startTime = Carbon::parse($this->start_time);
        return $startTime->addMinutes($this->late_tolerance_minutes)->format('H:i:s');
    }

    /**
     * Check if a check-in time is early
     */
    public function isEarlyArrival(Carbon $checkInTime): bool
    {
        $threshold = Carbon::parse($checkInTime->format('Y-m-d') . ' ' . $this->early_arrival_threshold);
        return $checkInTime->lte($threshold);
    }

    /**
     * Check if a check-in time is late
     */
    public function isLate(Carbon $checkInTime): bool
    {
        $lateThreshold = Carbon::parse($checkInTime->format('Y-m-d') . ' ' . $this->late_threshold);
        return $checkInTime->gt($lateThreshold);
    }

    /**
     * Determine attendance status based on check-in time
     */
    public function determineStatus(Carbon $checkInTime): string
    {
        if ($this->isEarlyArrival($checkInTime)) {
            return 'early_arrival';
        } elseif ($this->isLate($checkInTime)) {
            return 'late';
        }
        return 'on_time';
    }
}
