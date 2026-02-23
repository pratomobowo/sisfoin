<?php

namespace App\Livewire\Sdm;

use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\User;
use App\Models\WorkShift;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class UnitShiftDetail extends Component
{
    use WithPagination;

    public $unitName;

    public $unitSlug;

    public $showModal = false;

    public $editingId = null;

    // Form fields
    public $user_id = '';

    public $work_shift_id = '';

    public $start_date;

    public $end_date = '';

    public $notes = '';

    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'work_shift_id' => 'required|exists:work_shifts,id',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
    ];

    public function mount($unit)
    {
        $this->unitSlug = $unit;
        $this->unitName = urldecode($unit);
        $this->start_date = date('Y-m-d');
    }

    public function getShiftsProperty()
    {
        return WorkShift::active()->orderBy('name')->get();
    }

    public function getEmployeesProperty()
    {
        // Get user IDs who have assignments in this unit using NIP join
        $userIdsWithAssignments = EmployeeShiftAssignment::join('users', 'employee_shift_assignments.user_id', '=', 'users.id')
            ->join('employees', function ($join) {
                $join->on('users.nip', '=', 'employees.nip')
                    ->orOn('users.nip', '=', \DB::raw("TRIM(TRAILING '_' FROM employees.nip)"));
            })
            ->where('employees.satuan_kerja', $this->unitName)
            ->distinct('employee_shift_assignments.user_id')
            ->pluck('employee_shift_assignments.user_id');

        if ($userIdsWithAssignments->isEmpty()) {
            return collect();
        }

        $employeesWithAssignments = Employee::where('satuan_kerja', $this->unitName)
            ->where('status_aktif', 'Aktif')
            ->whereIn('nip', function ($q) use ($userIdsWithAssignments) {
                $q->select('nip')->from('users')->whereIn('id', $userIdsWithAssignments);
            })
            ->orderBy('nama')
            ->get();

        // Map to display objects
        $result = [];
        foreach ($employeesWithAssignments as $emp) {
            $user = User::where('nip', $emp->nip)
                ->orWhere('nip', rtrim($emp->nip, '_'))
                ->first();

            if ($user && $userIdsWithAssignments->contains($user->id)) {
                $result[] = (object) [
                    'id' => $user->id,
                    'employee_id' => $emp->id,
                    'name' => $emp->nama,
                    'nip' => $emp->nip,
                    'satuan_kerja' => $emp->satuan_kerja,
                    'user' => $user,
                    'has_user' => true,
                ];
            }
        }

        return collect($result);
    }

    public function getAllEmployeesInUnitProperty()
    {
        // For the dropdown in modal: list ALL active employees in this unit who have user accounts
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
                    'name' => $emp->nama,
                    'nip' => $emp->nip,
                ];
            }
        }

        return collect($result);
    }

    public function getAssignmentsProperty()
    {
        $userIds = $this->employees->pluck('id')->filter();

        if ($userIds->isEmpty()) {
            return collect();
        }

        return EmployeeShiftAssignment::with(['user', 'workShift'])
            ->whereIn('user_id', $userIds)
            ->orderBy('start_date', 'desc')
            ->get()
            ->groupBy('user_id');
    }

    public function openModal($userId = null, $assignmentId = null)
    {
        $this->resetValidation();

        if ($assignmentId) {
            $assignment = EmployeeShiftAssignment::findOrFail($assignmentId);

            if (! $this->isUserInCurrentUnit((int) $assignment->user_id)) {
                session()->flash('error', 'Akses ditolak: assignment bukan milik unit ini.');

                return;
            }

            $this->editingId = $assignmentId;
            $this->user_id = $assignment->user_id;
            $this->work_shift_id = $assignment->work_shift_id;
            $this->start_date = $assignment->start_date->format('Y-m-d');
            $this->end_date = $assignment->end_date?->format('Y-m-d') ?? '';
            $this->notes = $assignment->notes;
        } else {
            if ($userId && ! $this->isUserInCurrentUnit((int) $userId)) {
                session()->flash('error', 'Akses ditolak: user bukan milik unit ini.');

                return;
            }

            $this->reset(['editingId', 'work_shift_id', 'notes', 'end_date']);
            $this->user_id = $userId ?? '';
            $this->start_date = date('Y-m-d');
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingId = null;
    }

    public function save()
    {
        $this->validate();

        if (! $this->isUserInCurrentUnit((int) $this->user_id)) {
            $this->addError('user_id', 'User tidak termasuk unit ini.');

            return;
        }

        if ($this->editingId) {
            $existing = EmployeeShiftAssignment::find($this->editingId);
            if (! $existing || ! $this->isUserInCurrentUnit((int) $existing->user_id)) {
                session()->flash('error', 'Akses ditolak: assignment bukan milik unit ini.');

                return;
            }
        }

        $start = Carbon::parse($this->start_date);
        $end = $this->end_date ? Carbon::parse($this->end_date) : null;

        if (EmployeeShiftAssignment::hasOverlap((int) $this->user_id, $start, $end, $this->editingId ? (int) $this->editingId : null)) {
            $this->addError('start_date', 'Rentang tanggal overlap dengan assignment shift lain untuk user ini.');

            return;
        }

        $data = [
            'user_id' => $this->user_id,
            'work_shift_id' => $this->work_shift_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date ?: null,
            'notes' => $this->notes,
            'created_by' => auth()->id(),
        ];

        if ($this->editingId) {
            EmployeeShiftAssignment::find($this->editingId)->update($data);
        } else {
            EmployeeShiftAssignment::create($data);
        }

        $this->closeModal();
        session()->flash('success', 'Assignment shift berhasil disimpan!');
    }

    public function delete($id)
    {
        $assignment = EmployeeShiftAssignment::find($id);

        if (! $assignment || ! $this->isUserInCurrentUnit((int) $assignment->user_id)) {
            session()->flash('error', 'Akses ditolak: assignment bukan milik unit ini.');

            return;
        }

        $assignment->delete();
        session()->flash('success', 'Assignment shift berhasil dihapus!');
    }

    private function isUserInCurrentUnit(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $user = User::find($userId);
        if (! $user || ! $user->nip) {
            return false;
        }

        return Employee::query()
            ->where('satuan_kerja', $this->unitName)
            ->where(function ($query) use ($user) {
                $query->where('nip', $user->nip)
                    ->orWhereRaw("TRIM(TRAILING '_' FROM nip) = ?", [$user->nip]);
            })
            ->exists();
    }

    public function render()
    {
        return view('livewire.sdm.unit-shift-detail');
    }
}
