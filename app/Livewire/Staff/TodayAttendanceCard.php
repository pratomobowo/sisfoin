<?php

namespace App\Livewire\Staff;

use App\Models\Employee\Attendance;
use App\Models\WorkShift;
use App\Models\EmployeeShiftAssignment;
use Carbon\Carbon;
use Livewire\Component;

class TodayAttendanceCard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $today = today();
        
        // Fetch today's attendance
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // Get effective shift for today
        $shift = EmployeeShiftAssignment::getShiftForDate($user->id, $today) ?? WorkShift::getDefault();

        return view('livewire.staff.today-attendance-card', [
            'attendance' => $attendance,
            'shift' => $shift,
            'currentTime' => now()->format('H:i'),
        ]);
    }
}
