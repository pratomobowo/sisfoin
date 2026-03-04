<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\User;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        // Deactivate associated user account
        User::where('employee_type', 'employee')
            ->where('employee_id', $employee->id)
            ->update(['is_active' => false]);
    }

    /**
     * Handle the Employee "restored" event.
     */
    public function restored(Employee $employee): void
    {
        // Reactivate associated user account
        User::where('employee_type', 'employee')
            ->where('employee_id', $employee->id)
            ->update(['is_active' => true]);
    }

    /**
     * Handle the Employee "force deleted" event.
     */
    public function forceDeleted(Employee $employee): void
    {
        // Delete associated user account
        User::where('employee_type', 'employee')
            ->where('employee_id', $employee->id)
            ->delete();
    }
}
