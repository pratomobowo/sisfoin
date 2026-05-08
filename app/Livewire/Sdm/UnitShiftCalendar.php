<?php

namespace App\Livewire\Sdm;

use App\Livewire\Concerns\InteractsWithToast;
use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\User;
use App\Models\WorkShift;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class UnitShiftCalendar extends Component
{
    use InteractsWithToast, WithPagination;

    public $unitName;

    public $unitSlug;

    public $year;

    public $month;

    public int $perPage = 10;

    // Quick edit state
    public $selectedCell = null; // Format: "userId_date"

    public $quickEditShiftId = null;

    public $quickEditEndDate = null;

    // Assignment modal state
    public $showModal = false;

    public $editingId = null;

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
        $this->year = now()->year;
        $this->month = now()->month;
    }

    public function getShiftsProperty()
    {
        return WorkShift::active()->orderBy('name')->get();
    }

    public function getEmployeesQueryProperty()
    {
        $employees = Employee::where('satuan_kerja', $this->unitName)
            ->where('status_aktif', 'Aktif')
            ->orderBy('nama')
            ->get();

        if ($employees->isEmpty()) {
            return collect();
        }

        $candidateNips = $employees
            ->flatMap(function ($emp) {
                $trimmed = rtrim((string) $emp->nip, '_');

                return array_values(array_filter([(string) $emp->nip, $trimmed], fn ($nip) => $nip !== ''));
            })
            ->unique()
            ->values();

        if ($candidateNips->isEmpty()) {
            return collect();
        }

        $usersByNip = User::query()
            ->whereIn('nip', $candidateNips)
            ->get(['id', 'nip'])
            ->keyBy('nip');

        $result = [];
        foreach ($employees as $emp) {
            $user = $usersByNip->get((string) $emp->nip)
                ?? $usersByNip->get(rtrim((string) $emp->nip, '_'));

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

    public function getEmployeesProperty()
    {
        return $this->employeesQuery
            ->forPage($this->getPage(), $this->perPage)
            ->values();
    }

    public function getEmployeesPaginatedProperty()
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $this->employees,
            $this->employeesQuery->count(),
            $this->perPage,
            $this->getPage(),
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    public function getCalendarDatesProperty()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return collect(CarbonPeriod::create($startDate, $endDate))
            ->map(fn ($date) => $date->copy());
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
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate)
                    ->where(function ($q2) use ($startDate) {
                        $q2->whereNull('end_date')
                            ->orWhere('end_date', '>=', $startDate);
                    });
            })
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function getEmployeeShiftForDate($userId, $date)
    {
        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);

        $assignment = $this->assignmentsCache
            ->where('user_id', $userId)
            ->first(function ($a) use ($carbonDate) {
                return $a->isActiveOn($carbonDate);
            });

        return $assignment?->workShift;
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->resetPage();
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->resetPage();
    }

    public function openQuickEdit($userId, $date)
    {
        $this->selectedCell = "{$userId}_{$date}";
        $this->quickEditEndDate = $date;
        $shift = $this->getEmployeeShiftForDate($userId, $date);
        $this->quickEditShiftId = $shift?->id ?? '';
    }

    public function selectWeek()
    {
        if (! $this->selectedCell) {
            return;
        }

        [$userId, $dateStr] = explode('_', $this->selectedCell);
        $startDate = Carbon::parse($dateStr);
        $this->quickEditEndDate = $startDate->copy()->addDays(6)->toDateString();
    }

    public function closeQuickEdit()
    {
        $this->selectedCell = null;
        $this->quickEditShiftId = null;
        $this->quickEditEndDate = null;
    }

    public function openModal($userId = null)
    {
        $this->resetValidation();

        $this->reset(['editingId', 'work_shift_id', 'notes', 'end_date']);
        $this->user_id = $userId ?? '';
        $this->start_date = date('Y-m-d');
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingId = null;
    }

    public function saveAssignment()
    {
        abort_unless(auth()->user()?->can('employee.attendance.edit'), 403);

        $this->validate();

        if (! $this->isUserInCurrentUnit((int) $this->user_id)) {
            $this->addError('user_id', 'User tidak termasuk unit ini.');

            return;
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
            'created_by' => Auth::id(),
        ];

        if ($this->editingId) {
            EmployeeShiftAssignment::find($this->editingId)?->update($data);
        } else {
            EmployeeShiftAssignment::create($data);
        }

        $this->closeModal();
        $this->toastSuccess('Assignment shift berhasil disimpan!');
    }

    public function deleteAssignment($id)
    {
        abort_unless(auth()->user()?->can('employee.attendance.edit'), 403);

        $assignment = EmployeeShiftAssignment::find($id);

        if (! $assignment || ! $this->isUserInCurrentUnit((int) $assignment->user_id)) {
            $this->toastError('Akses ditolak: assignment bukan milik unit ini.');

            return;
        }

        $assignment->delete();
        $this->toastSuccess('Assignment shift berhasil dihapus!');
    }

    public function saveQuickEdit()
    {
        abort_unless(auth()->user()?->can('employee.attendance.edit'), 403);

        if (! $this->selectedCell) {
            return;
        }

        [$userId, $startDate] = explode('_', $this->selectedCell);
        $userId = (int) $userId;

        if (! $this->isUserInCurrentUnit($userId)) {
            $this->toastError('Akses ditolak: user bukan milik unit ini.');
            $this->closeQuickEdit();

            return;
        }

        $validatedShiftId = null;
        if ($this->quickEditShiftId !== '') {
            $candidateShiftId = (int) $this->quickEditShiftId;
            $shift = WorkShift::query()
                ->whereKey($candidateShiftId)
                ->where('is_active', true)
                ->first();

            if (! $shift) {
                $this->toastError('Shift tidak valid atau tidak aktif.');

                return;
            }

            $validatedShiftId = (int) $shift->getKey();
        }

        $endDate = $this->quickEditEndDate ?: $startDate;

        // Validation: End date cannot be before start date
        if (Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            $endDate = $startDate;
        }

        $createdBy = Auth::id();

        DB::transaction(function () use ($userId, $startDate, $endDate, $validatedShiftId, $createdBy) {
            $this->removeOverlappingAssignments($userId, Carbon::parse($startDate), Carbon::parse($endDate));

            if ($validatedShiftId !== null) {
                EmployeeShiftAssignment::create([
                    'user_id' => $userId,
                    'work_shift_id' => $validatedShiftId,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'Aktif',
                    'created_by' => $createdBy,
                ]);
            }
        });

        $this->closeQuickEdit();
        $this->toastSuccess('Shift berhasil di-update!');
    }

    private function removeOverlappingAssignments(int $userId, Carbon $startDate, Carbon $endDate): void
    {
        $assignments = EmployeeShiftAssignment::where('user_id', $userId)
            ->whereDate('start_date', '<=', $endDate->toDateString())
            ->where(function ($query) use ($startDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $startDate->toDateString());
            })
            ->get();

        foreach ($assignments as $assignment) {
            $assignmentStart = $assignment->start_date->copy();
            $assignmentEnd = $assignment->end_date?->copy();
            $originalEnd = $assignmentEnd?->copy();

            if ($assignmentStart->lt($startDate)) {
                $assignment->update(['end_date' => $startDate->copy()->subDay()->toDateString()]);
            } else {
                $assignment->delete();
            }

            if ($originalEnd === null || $originalEnd->gt($endDate)) {
                EmployeeShiftAssignment::create([
                    'user_id' => $assignment->user_id,
                    'work_shift_id' => $assignment->work_shift_id,
                    'start_date' => $endDate->copy()->addDay()->toDateString(),
                    'end_date' => $originalEnd?->toDateString(),
                    'notes' => $assignment->notes,
                    'created_by' => $assignment->created_by,
                ]);
            }
        }
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
        return view('livewire.sdm.unit-shift-calendar', [
            'currentMonthName' => Carbon::create($this->year, $this->month, 1)->locale('id')->isoFormat('MMMM YYYY'),
        ]);
    }
}
