<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EmployeeShiftAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'work_shift_id',
        'start_date',
        'end_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the work shift
     */
    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class);
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if assignment is active on a given date
     */
    public function isActiveOn(Carbon $date): bool
    {
        if ($date->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $date->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Scope for active assignments on a date
     */
    public function scopeActiveOn($query, Carbon $date)
    {
        return $query->where('start_date', '<=', $date->format('Y-m-d'))
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $date->format('Y-m-d'));
            });
    }

    /**
     * Get shift for a user on a specific date
     */
    public static function getShiftForDate(int $userId, Carbon $date): ?WorkShift
    {
        $assignment = static::where('user_id', $userId)
            ->activeOn($date)
            ->with('workShift')
            ->orderBy('start_date', 'desc') // Most recent assignment takes priority
            ->first();
            
        return $assignment?->workShift;
    }

    /**
     * Get all assignments for a user
     */
    public static function getForUser(int $userId)
    {
        return static::where('user_id', $userId)
            ->with('workShift')
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Check if assignment overlaps with existing assignments
     */
    public static function hasOverlap(int $userId, Carbon $startDate, ?Carbon $endDate, ?int $excludeId = null): bool
    {
        $query = static::where('user_id', $userId)
            ->where(function ($q) use ($startDate, $endDate) {
                // Check for overlap
                $q->where(function ($q2) use ($startDate, $endDate) {
                    $q2->where('start_date', '<=', $endDate ?? '9999-12-31')
                       ->where(function ($q3) use ($startDate) {
                           $q3->whereNull('end_date')
                              ->orWhere('end_date', '>=', $startDate);
                       });
                });
            });
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Get status label
     */
    public function getStatusAttribute(): string
    {
        $today = Carbon::today();
        
        if ($this->start_date->gt($today)) {
            return 'upcoming';
        }
        
        if ($this->end_date && $this->end_date->lt($today)) {
            return 'expired';
        }
        
        return 'active';
    }
}
