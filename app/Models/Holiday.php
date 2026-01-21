<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'type',
        'is_recurring',
        'description',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the user who created the holiday
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for national holidays
     */
    public function scopeNational($query)
    {
        return $query->where('type', 'national');
    }

    /**
     * Scope for company holidays
     */
    public function scopeCompany($query)
    {
        return $query->where('type', 'company');
    }

    /**
     * Check if a given date is a holiday
     */
    public static function isHoliday(Carbon $date): bool
    {
        // Check exact date match
        $exists = static::where('date', $date->format('Y-m-d'))->exists();
        if ($exists) {
            return true;
        }

        // Check recurring holidays (same month and day, different year)
        return static::where('is_recurring', true)
            ->whereMonth('date', $date->month)
            ->whereDay('date', $date->day)
            ->exists();
    }

    /**
     * Get holiday info for a date
     */
    public static function getHolidayInfo(Carbon $date): ?self
    {
        // Check exact date match first
        $holiday = static::where('date', $date->format('Y-m-d'))->first();
        if ($holiday) {
            return $holiday;
        }

        // Check recurring holidays
        return static::where('is_recurring', true)
            ->whereMonth('date', $date->month)
            ->whereDay('date', $date->day)
            ->first();
    }

    /**
     * Get holidays for a month/year
     */
    public static function getForMonth(int $year, int $month)
    {
        return static::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orWhere(function ($query) use ($month) {
                $query->where('is_recurring', true)
                    ->whereMonth('date', $month);
            })
            ->orderBy('date')
            ->get();
    }
}
