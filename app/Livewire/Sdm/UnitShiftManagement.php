<?php

namespace App\Livewire\Sdm;

use App\Models\Employee;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class UnitShiftManagement extends Component
{
    public $search = '';
    public $selectedUnitToAdd = '';

    public function getUnitsProperty()
    {
        // Get units that HAVE at least one assignment
        $managedUnits = \App\Models\EmployeeShiftAssignment::join('users', 'employee_shift_assignments.user_id', '=', 'users.id')
            ->join('employees', function($join) {
                $join->on('users.nip', '=', 'employees.nip')
                     ->orOn('users.nip', '=', \DB::raw("TRIM(TRAILING '_' FROM employees.nip)"));
            })
            ->select('employees.satuan_kerja')
            ->distinct()
            ->pluck('employees.satuan_kerja')
            ->toArray();

        // Count active employees per managed unit
        $unitData = [];
        foreach ($managedUnits as $unit) {
            $employeeCount = \App\Models\EmployeeShiftAssignment::join('users', 'employee_shift_assignments.user_id', '=', 'users.id')
                ->join('employees', function($join) {
                    $join->on('users.nip', '=', 'employees.nip')
                         ->orOn('users.nip', '=', \DB::raw("TRIM(TRAILING '_' FROM employees.nip)"));
                })
                ->where('employees.satuan_kerja', $unit)
                ->distinct('employee_shift_assignments.user_id')
                ->count('employee_shift_assignments.user_id');

            if ($this->search && !str_contains(strtolower($unit), strtolower($this->search))) {
                continue;
            }

            $unitData[] = [
                'name' => $unit,
                'employee_count' => $employeeCount,
                'slug' => urlencode($unit),
            ];
        }

        return collect($unitData);
    }

    public function getAllAvailableUnitsProperty()
    {
        return Employee::select('satuan_kerja')
            ->distinct()
            ->whereNotNull('satuan_kerja')
            ->where('status_aktif', 'Aktif')
            ->orderBy('satuan_kerja')
            ->pluck('satuan_kerja');
    }

    public function addUnit()
    {
        if ($this->selectedUnitToAdd) {
            return redirect()->route('sdm.absensi.unit-detail', ['unit' => urlencode($this->selectedUnitToAdd)]);
        }
    }

    public function render()
    {
        return view('livewire.sdm.unit-shift-management');
    }
}
