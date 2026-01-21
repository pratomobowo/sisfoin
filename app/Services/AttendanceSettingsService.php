<?php

namespace App\Services;

use App\Models\AttendanceSetting;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AttendanceSettingsService
{
    /**
     * Get work start time
     */
    public function getWorkStartTime(): string
    {
        return AttendanceSetting::getValue('work_start_time', '08:00');
    }

    /**
     * Get work end time
     */
    public function getWorkEndTime(): string
    {
        return AttendanceSetting::getValue('work_end_time', '14:00');
    }

    /**
     * Get early arrival threshold
     */
    public function getEarlyArrivalThreshold(): string
    {
        return AttendanceSetting::getValue('early_arrival_threshold', '07:40');
    }

    /**
     * Get late tolerance in minutes
     */
    public function getLateTolerance(): int
    {
        return AttendanceSetting::getValue('late_tolerance_minutes', 5);
    }

    /**
     * Calculate late threshold time (work start + tolerance)
     */
    public function getLateThreshold(): string
    {
        $workStart = Carbon::parse($this->getWorkStartTime());
        $tolerance = $this->getLateTolerance();
        return $workStart->addMinutes($tolerance)->format('H:i');
    }

    /**
     * Get minimum checkout duration in minutes
     */
    public function getMinCheckoutDuration(): int
    {
        return AttendanceSetting::getValue('min_checkout_duration_minutes', 30);
    }

    /**
     * Get standard work hours
     */
    public function getStandardWorkHours(): int
    {
        return AttendanceSetting::getValue('standard_work_hours', 6);
    }

    /**
     * Get working days as array of ISO weekday numbers
     */
    public function getWorkingDays(): array
    {
        return AttendanceSetting::getValue('working_days', [1, 2, 3, 4, 5, 6]);
    }

    /**
     * Check if a date is a working day
     */
    public function isWorkingDay(Carbon $date): bool
    {
        // Check if it's a holiday
        if (Holiday::isHoliday($date)) {
            return false;
        }

        // Check if the day of week is a working day
        $dayOfWeek = $date->dayOfWeekIso; // 1=Monday, 7=Sunday
        return in_array($dayOfWeek, $this->getWorkingDays());
    }

    /**
     * Check if a date is a holiday
     */
    public function isHoliday(Carbon $date): bool
    {
        return Holiday::isHoliday($date);
    }

    /**
     * Get holiday info for a date
     */
    public function getHolidayInfo(Carbon $date): ?Holiday
    {
        return Holiday::getHolidayInfo($date);
    }

    /**
     * Get all settings for admin UI
     */
    public function getAllSettings()
    {
        return AttendanceSetting::getAllGrouped();
    }

    /**
     * Update a setting
     */
    public function updateSetting(string $key, $value): void
    {
        AttendanceSetting::setValue($key, $value);
    }

    /**
     * Clear all cached settings
     */
    public function clearCache(): void
    {
        AttendanceSetting::clearCache();
    }
}
