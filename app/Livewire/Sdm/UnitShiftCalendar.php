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

    public function saveQuickEdit()
    {
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
            // Delete existing assignments that overlap with this range for this user
            EmployeeShiftAssignment::where('user_id', $userId)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereDate('start_date', '<=', $endDate)
                        ->where(function ($q2) use ($startDate) {
                            $q2->whereNull('end_date')
                                ->orWhereDate('end_date', '>=', $startDate);
                        });
                })
                ->delete();

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
