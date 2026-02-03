<?php

namespace App\Livewire\Sdm;

use App\Models\Employee;
use App\Models\User;
use App\Models\WorkShift;
use App\Models\EmployeeShiftAssignment;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

#[Layout('layouts.app')]
class UnitShiftCalendar extends Component
{
    public $unitName;
    public $unitSlug;
    public $year;
    public $month;
    
    // Quick edit state
    public $selectedCell = null; // Format: "userId_date"
    public $quickEditShiftId = null;
    
    // View toggle
    public $viewMode = 'calendar'; // 'calendar' or 'list'

    public function mount($unit)
    {
        $this->unitSlug = $unit;
        $this->unitName = urldecode($unit);
        $this->year = now()->year;
        $this->month = now()->month;
    }

    public function getShiftsProperty()
    {
        return WorkShift::active()->orderBy('name')->get();
    }

    public function getEmployeesProperty()
    {
        // Get all active employees in this unit who have user accounts
        $employees = Employee::where('satuan_kerja', $this->unitName)
            ->where('status_aktif', 'Aktif')
            ->orderBy('nama')
            ->get();
            
        $result = [];
        foreach ($employees as $emp) {
            $user = User::where('nip', $emp->nip)
                ->orWhere('nip', rtrim($emp->nip, '_'))
                ->first();
                
            if ($user) {
                $result[] = (object) [
                    'id' => $user->id,
                    'employee_id' => $emp->id,
                    'name' => $emp->nama,
                    'nip' => $emp->nip,
                    'satuan_kerja' => $emp->satuan_kerja,
                    'user' => $user,
                ];
            }
        }
        
        return collect($result);
    }

    public function getCalendarDatesProperty()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        return collect(CarbonPeriod::create($startDate, $endDate))
            ->map(fn($date) => $date->copy());
    }

    public function getAssignmentsCacheProperty()
    {
        $userIds = $this->employees->pluck('id');
        
        if ($userIds->isEmpty()) {
            return collect();
        }
        
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        return EmployeeShiftAssignment::with('workShift')
            ->whereIn('user_id', $userIds)
            ->where(function($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate)
                  ->where(function($q2) use ($startDate) {
                      $q2->whereNull('end_date')
                         ->orWhere('end_date', '>=', $startDate);
                  });
            })
            ->get();
    }

    public function getEmployeeShiftForDate($userId, $date)
    {
        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        $assignment = $this->assignmentsCache
            ->where('user_id', $userId)
            ->first(function($a) use ($carbonDate) {
                return $a->isActiveOn($carbonDate);
            });
            
        return $assignment?->workShift;
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $date->year;
        $this->month = $date->month;
    }

    public function openQuickEdit($userId, $date)
    {
        $this->selectedCell = "{$userId}_{$date}";
        $shift = $this->getEmployeeShiftForDate($userId, $date);
        $this->quickEditShiftId = $shift?->id ?? '';
    }

    public function closeQuickEdit()
    {
        $this->selectedCell = null;
        $this->quickEditShiftId = null;
    }

    public function saveQuickEdit()
    {
        [$userId, $date] = explode('_', $this->selectedCell);
        
        $carbonDate = Carbon::parse($date);
        
        // Find existing assignment for this user on this date
        $existingAssignment = EmployeeShiftAssignment::where('user_id', $userId)
            ->where('start_date', '<=', $carbonDate)
            ->where(function($q) use ($carbonDate) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $carbonDate);
            })
            ->first();
        
        if ($this->quickEditShiftId === '') {
            // Remove assignment (set to default) - delete existing if it's a single-day assignment
            if ($existingAssignment) {
                // If assignment is only for this date, delete it
                if ($existingAssignment->start_date->isSameDay($carbonDate) && 
                    $existingAssignment->end_date && 
                    $existingAssignment->end_date->isSameDay($carbonDate)) {
                    $existingAssignment->delete();
                } else {
                    // Otherwise, split the assignment
                    if ($existingAssignment->start_date->isSameDay($carbonDate)) {
                        // Move start date forward
                        $existingAssignment->update(['start_date' => $carbonDate->copy()->addDay()]);
                    } elseif ($existingAssignment->end_date && $existingAssignment->end_date->isSameDay($carbonDate)) {
                        // Move end date backward
                        $existingAssignment->update(['end_date' => $carbonDate->copy()->subDay()]);
                    } else {
                        // Split into two assignments
                        $endDate = $existingAssignment->end_date;
                        $existingAssignment->update(['end_date' => $carbonDate->copy()->subDay()]);
                        
                        EmployeeShiftAssignment::create([
                            'user_id' => $userId,
                            'work_shift_id' => $existingAssignment->work_shift_id,
                            'start_date' => $carbonDate->copy()->addDay(),
                            'end_date' => $endDate,
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }
        } else {
            // Create or update assignment for this specific date
            EmployeeShiftAssignment::create([
                'user_id' => $userId,
                'work_shift_id' => $this->quickEditShiftId,
                'start_date' => $carbonDate,
                'end_date' => $carbonDate,
                'created_by' => auth()->id(),
            ]);
        }
        
        $this->closeQuickEdit();
        session()->flash('success', 'Shift berhasil di-update!');
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'calendar' ? 'list' : 'calendar';
    }

    public function render()
    {
        return view('livewire.sdm.unit-shift-calendar', [
            'currentMonthName' => Carbon::create($this->year, $this->month, 1)->locale('id')->isoFormat('MMMM YYYY'),
        ]);
    }
}
