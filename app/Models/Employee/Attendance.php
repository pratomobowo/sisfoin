<?php

namespace App\Models\Employee;

use App\Models\EmployeeShiftAssignment;
use App\Models\User;
use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'employee_attendances';

    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'break_start_time',
        'break_end_time',
        'total_hours',
        'overtime_hours',
        'status',
        'notes',
        'location_check_in',
        'location_check_out',
        'ip_address',
        'device_info',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'break_start_time' => 'datetime',
        'break_end_time' => 'datetime',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the employee that owns the attendance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who created this attendance record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this attendance record.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the effective shift for this attendance record.
     * Priority: Assigned shift > Default shift
     */
    public function getEffectiveShiftAttribute(): ?WorkShift
    {
        if (! $this->user_id || ! $this->date) {
            return WorkShift::getDefault();
        }

        // Check for assigned shift for this user on this date
        $assignedShift = EmployeeShiftAssignment::getShiftForDate($this->user_id, Carbon::parse($this->date));

        if ($assignedShift) {
            return $assignedShift;
        }

        // Fall back to default shift
        return WorkShift::getDefault();
    }

    /**
     * Calculate total working hours.
     */
    public function calculateTotalHours(): float
    {
        if (! $this->check_in_time || ! $this->check_out_time) {
            return 0;
        }

        $checkIn = Carbon::parse($this->check_in_time);
        $checkOut = Carbon::parse($this->check_out_time);

        $totalMinutes = abs($checkOut->diffInMinutes($checkIn));

        // Subtract break time if exists
        if ($this->break_start_time && $this->break_end_time) {
            $breakStart = Carbon::parse($this->break_start_time);
            $breakEnd = Carbon::parse($this->break_end_time);
            $breakMinutes = abs($breakEnd->diffInMinutes($breakStart));
            $totalMinutes -= $breakMinutes;
        }

        return round(max(0, $totalMinutes) / 60, 2);
    }

    /**
     * Calculate overtime hours.
     */
    public function calculateOvertimeHours(): float
    {
        $totalHours = $this->calculateTotalHours();

        $shift = $this->effective_shift;
        $standardHours = $shift ? (float) $shift->work_hours : 8.0;

        return $totalHours > $standardHours ? $totalHours - $standardHours : 0;
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'early_arrival' => 'green',
            'on_time' => 'green',
            'present' => 'green',
            'late' => 'yellow',
            'absent' => 'red',
            'sick' => 'gray',
            'leave' => 'purple',
            'incomplete' => 'orange',
            default => 'gray'
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'early_arrival' => 'Datang Lebih Awal',
            'on_time' => 'Tepat Waktu',
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'sick' => 'Sakit',
            'leave' => 'Cuti',
            'incomplete' => 'Absen Tidak Lengkap',
            default => 'Unknown'
        };
    }

    /**
     * Get the effective shift for this attendance record
     */
    public function getEffectiveShiftProperty(): ?WorkShift
    {
        // Try to get assigned shift
        $shift = EmployeeShiftAssignment::getShiftForDate($this->user_id, $this->date);

        // Fallback to default shift
        if (! $shift) {
            $shift = WorkShift::getDefault();
        }

        return $shift;
    }

    /**
     * Get formatted check-in time.
     */
    public function getFormattedCheckInAttribute(): string
    {
        return $this->check_in_time ? $this->check_in_time->format('H:i') : '-';
    }

    /**
     * Get formatted check-out time.
     */
    public function getFormattedCheckOutAttribute(): string
    {
        return $this->check_out_time ? $this->check_out_time->format('H:i') : '-';
    }

    /**
     * Get formatted date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('d/m/Y');
    }

    /**
     * Check if employee is late.
     */
    public function isLate(): bool
    {
        if (! $this->check_in_time) {
            return false;
        }

        $shift = $this->effective_shift;
        if ($shift) {
            return $shift->isLate($this->check_in_time);
        }

        // Fallback to hardcoded if no shift found (should not happen with default shift)
        $standardCheckIn = Carbon::parse($this->date->format('Y-m-d').' 08:00:00');

        return $this->check_in_time->gt($standardCheckIn);
    }

    /**
     * Check if employee left early.
     */
    public function isEarlyLeave(): bool
    {
        if (! $this->check_out_time) {
            return false;
        }

        $shift = $this->effective_shift;
        if ($shift && $shift->end_time) {
            $standardCheckOut = Carbon::parse($this->date->format('Y-m-d').' '.$shift->end_time);

            return $this->check_out_time->lt($standardCheckOut);
        }

        $standardCheckOut = Carbon::parse($this->date->format('Y-m-d').' 17:00:00');

        return $this->check_out_time->lt($standardCheckOut);
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for current month.
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);
    }

    /**
     * Scope for today's attendance.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }
}
