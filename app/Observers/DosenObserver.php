<?php

namespace App\Observers;

use App\Models\Dosen;
use App\Models\User;

class DosenObserver
{
    /**
     * Handle the Dosen "created" event.
     */
    public function created(Dosen $dosen): void
    {
        //
    }

    /**
     * Handle the Dosen "updated" event.
     */
    public function updated(Dosen $dosen): void
    {
        //
    }

    /**
     * Handle the Dosen "deleted" event.
     */
    public function deleted(Dosen $dosen): void
    {
        // Deactivate associated user account
        User::where('employee_type', 'dosen')
            ->where('employee_id', $dosen->id)
            ->update(['is_active' => false]);
    }

    /**
     * Handle the Dosen "restored" event.
     */
    public function restored(Dosen $dosen): void
    {
        // Reactivate associated user account
        User::where('employee_type', 'dosen')
            ->where('employee_id', $dosen->id)
            ->update(['is_active' => true]);
    }

    /**
     * Handle the Dosen "force deleted" event.
     */
    public function forceDeleted(Dosen $dosen): void
    {
        // Delete associated user account
        User::where('employee_type', 'dosen')
            ->where('employee_id', $dosen->id)
            ->delete();
    }
}
