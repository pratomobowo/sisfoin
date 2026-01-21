<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'employee_type',
        'employee_id',
        'fingerprint_pin',
        'fingerprint_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'nip', 'employee_type', 'employee_id', 'fingerprint_pin', 'fingerprint_enabled'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the employee associated with the user.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the dosen associated with the user.
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'employee_id');
    }

    /**
     * Get the employee or dosen data based on employee_type
     */
    public function getEmployeeDataAttribute()
    {
        if ($this->employee_type === 'employee' && $this->employee) {
            return $this->employee;
        } elseif ($this->employee_type === 'dosen' && $this->dosen) {
            return $this->dosen;
        }

        return null;
    }

    /**
     * Get the full name with title from employee or dosen data
     */
    public function getFullNameWithTitleAttribute()
    {
        if ($this->employeeData) {
            if ($this->employee_type === 'employee') {
                return $this->employee->nama_lengkap_with_gelar;
            } elseif ($this->employee_type === 'dosen') {
                return $this->dosen->nama_lengkap_with_gelar;
            }
        }

        return $this->name;
    }

    /**
     * Get the attendance logs for this user
     */
    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    /**
     * Scope a query to only include users with fingerprint enabled
     */
    public function scopeFingerprintEnabled($query)
    {
        return $query->where('fingerprint_enabled', true);
    }

    /**
     * Scope a query to only include users with fingerprint PIN set
     */
    public function scopeWithFingerprintPin($query)
    {
        return $query->whereNotNull('fingerprint_pin');
    }

    /**
     * Scope a query to find user by fingerprint PIN
     */
    public function scopeByFingerprintPin($query, $pin)
    {
        return $query->where('fingerprint_pin', $pin);
    }

    /**
     * Check if user has fingerprint access
     */
    public function hasFingerprintAccess()
    {
        return $this->fingerprint_enabled && ! empty($this->fingerprint_pin);
    }

    /**
     * Enable fingerprint access for this user
     */
    public function enableFingerprintAccess($pin = null)
    {
        return $this->update([
            'fingerprint_enabled' => true,
            'fingerprint_pin' => $pin ?? $this->fingerprint_pin,
        ]);
    }

    /**
     * Disable fingerprint access for this user
     */
    public function disableFingerprintAccess()
    {
        return $this->update([
            'fingerprint_enabled' => false,
        ]);
    }

    /**
     * Set fingerprint PIN for this user
     */
    public function setFingerprintPin($pin)
    {
        return $this->update([
            'fingerprint_pin' => $pin,
        ]);
    }

    /**
     * Get fingerprint status label
     */
    public function getFingerprintStatusLabelAttribute()
    {
        if (! $this->fingerprint_enabled) {
            return 'Tidak Aktif';
        }

        if (empty($this->fingerprint_pin)) {
            return 'Aktif (PIN belum diatur)';
        }

        return 'Aktif';
    }

    /**
     * Get fingerprint status color
     */
    public function getFingerprintStatusColorAttribute()
    {
        if (! $this->fingerprint_enabled) {
            return 'gray';
        }

        if (empty($this->fingerprint_pin)) {
            return 'yellow';
        }

        return 'green';
    }
}
