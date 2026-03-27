<?php

namespace App\Services;

use App\Models\AttendanceSetting;
use App\Models\DailyWorkSchedule;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AttendanceSettingsService
{
    /**
     * Get work start time (global fallback)
     */
    public function getWorkStartTime(): string
    {
        return AttendanceSetting::getValue('work_start_time', '08:00');
    }

    /**
     * Get work end time (global fallback)
     */
    public function getWorkEndTime(): string
    {
        return AttendanceSetting::getValue('work_end_time', '14:00');
    }

    /**
     * Get early arrival threshold (global fallback)
     */
    public function getEarlyArrivalThreshold(): string
    {
        return AttendanceSetting::getValue('early_arrival_threshold', '07:40');
    }

    /**
     * Get late tolerance in minutes (global fallback)
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
     * Get the daily work schedule for a specific day of week.
     * Returns the DailyWorkSchedule model or null.
     */
    public function getScheduleForDay(int $dayOfWeek): ?DailyWorkSchedule
    {
        return DailyWorkSchedule::getScheduleForDay($dayOfWeek);
    }

    /**
     * Get work start time for a specific day.
     * Uses DailyWorkSchedule if available, otherwise falls back to global setting.
     */
    public function getWorkStartTimeForDay(int $dayOfWeek): string
    {
        $schedule = $this->getScheduleForDay($dayOfWeek);
        if ($schedule && $schedule->is_active) {
            return substr($schedule->start_time, 0, 5);
        }
        return $this->getWorkStartTime();
    }

    /**
     * Get work end time for a specific day.
     * Uses DailyWorkSchedule if available, otherwise falls back to global setting.
     */
    public function getWorkEndTimeForDay(int $dayOfWeek): string
    {
        $schedule = $this->getScheduleForDay($dayOfWeek);
        if ($schedule && $schedule->is_active) {
            return substr($schedule->end_time, 0, 5);
        }
        return $this->getWorkEndTime();
    }

    /**
     * Get early arrival threshold for a specific day.
     */
    public function getEarlyArrivalThresholdForDay(int $dayOfWeek): string
    {
        $schedule = $this->getScheduleForDay($dayOfWeek);
        if ($schedule && $schedule->is_active) {
            return substr($schedule->early_arrival_threshold, 0, 5);
        }
        return $this->getEarlyArrivalThreshold();
    }

    /**
     * Get late tolerance for a specific day.
     */
    public function getLateToleranceForDay(int $dayOfWeek): int
    {
        $schedule = $this->getScheduleForDay($dayOfWeek);
        if ($schedule && $schedule->is_active) {
            return $schedule->late_tolerance_minutes;
        }
        return $this->getLateTolerance();
    }

    /**
     * Calculate late threshold for a specific day.
     */
    public function getLateThresholdForDay(int $dayOfWeek): string
    {
        $workStart = Carbon::parse($this->getWorkStartTimeForDay($dayOfWeek));
        $tolerance = $this->getLateToleranceForDay($dayOfWeek);
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
