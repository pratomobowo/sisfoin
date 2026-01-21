<?php

namespace App\Models\Employee;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'employee_payrolls';

    protected $fillable = [
        'user_id',
        'period_month',
        'period_year',
        'basic_salary',
        'allowances',
        'overtime_hours',
        'overtime_rate',
        'overtime_pay',
        'deductions',
        'tax_deduction',
        'insurance_deduction',
        'other_deductions',
        'gross_salary',
        'net_salary',
        'status',
        'paid_at',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'deductions' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'insurance_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the employee that owns the payroll.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who created this payroll record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this payroll record.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Calculate gross salary.
     */
    public function calculateGrossSalary(): float
    {
        return $this->basic_salary + $this->allowances + $this->overtime_pay;
    }

    /**
     * Calculate total deductions.
     */
    public function calculateTotalDeductions(): float
    {
        return $this->tax_deduction + $this->insurance_deduction + $this->other_deductions;
    }

    /**
     * Calculate net salary.
     */
    public function calculateNetSalary(): float
    {
        return $this->calculateGrossSalary() - $this->calculateTotalDeductions();
    }

    /**
     * Get formatted period.
     */
    public function getFormattedPeriodAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $months[$this->period_month].' '.$this->period_year;
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'pending' => 'warning',
            'approved' => 'success',
            'paid' => 'primary',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'paid' => 'Sudah Dibayar',
            'rejected' => 'Ditolak',
            default => 'Unknown'
        };
    }

    /**
     * Scope for filtering by period.
     */
    public function scopeByPeriod($query, $month, $year)
    {
        return $query->where('period_month', $month)
            ->where('period_year', $year);
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
        return $query->where('period_month', now()->month)
            ->where('period_year', now()->year);
    }
}
