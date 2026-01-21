<?php

namespace App\Services;

use App\Models\WorkShift;
use App\Models\EmployeeShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ShiftResolverService
{
    /**
     * Resolve the applicable shift for a user on a given date
     * Priority: Employee Shift Assignment (with date range) > Default Shift
     */
    public function resolveShiftForUser(User $user, Carbon $date): WorkShift
    {
        // 1. Check for employee shift assignment (primary source with date range)
        $assignedShift = EmployeeShiftAssignment::getShiftForDate($user->id, $date);
        if ($assignedShift) {
            return $assignedShift;
        }

        // 2. Fall back to default shift
        return $this->getDefaultShift();
    }

    /**
     * Resolve shift by user ID
     */
    public function resolveShiftForUserId(int $userId, Carbon $date): WorkShift
    {
        $user = User::with(['employee', 'dosen'])->find($userId);
        if (!$user) {
            return $this->getDefaultShift();
        }
        return $this->resolveShiftForUser($user, $date);
    }

    /**
     * Get user's satuan_kerja from employee or dosen data
     */
    private function getUserSatuanKerja(User $user): ?string
    {
        if ($user->employee_type === 'employee' && $user->employee) {
            return $user->employee->satuan_kerja;
        }
        
        if ($user->employee_type === 'dosen' && $user->dosen) {
            // Dosen may have home_base or similar field
            return $user->dosen->home_base ?? null;
        }

        return null;
    }

    /**
     * Get the default shift (cached)
     */
    public function getDefaultShift(): WorkShift
    {
        return Cache::remember('default_work_shift', 3600, function () {
            $default = WorkShift::getDefault();
            if (!$default) {
                // If no default set, get the first active shift
                $default = WorkShift::active()->first();
            }
            return $default;
        });
    }

    /**
     * Get all active shifts
     */
    public function getActiveShifts()
    {
        return WorkShift::active()->orderBy('name')->get();
    }

    /**
     * Clear cached default shift
     */
    public function clearCache(): void
    {
        Cache::forget('default_work_shift');
    }
}
