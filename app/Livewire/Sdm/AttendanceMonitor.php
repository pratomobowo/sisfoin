<?php

namespace App\Livewire\Sdm;

use App\Livewire\Concerns\InteractsWithToast;
use App\Models\AttendanceSetting;
use App\Models\Employee;
use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Models\Holiday;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.app')]
class AttendanceMonitor extends Component
{
    use InteractsWithToast, WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $mode = 'daily';

    public string $selectedDate = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $unitKerja = '';

    public string $search = '';

    public string $statusFilter = '';

    public int $perPage = 15;

    public ?int $expandedRangeEmployeeId = null;

    protected $queryString = [
        'mode' => ['except' => 'daily'],
        'selectedDate' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'unitKerja' => ['except' => ''],
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function toggleRangeDetails(int $employeeId): void
    {
        $this->expandedRangeEmployeeId = $this->expandedRangeEmployeeId === $employeeId ? null : $employeeId;
    }

    public function reprocessAllAttendance(): void
    {
        try {
            $attendanceService = new AttendanceService;
            $result = $attendanceService->processLogs(null, null, null, true);

            activity('attendance_operations')
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'reprocess_all',
                    'source' => 'attendance_monitor',
                    'processed_count' => $result['processed_count'] ?? 0,
                    'error_count' => $result['error_count'] ?? 0,
                    'execution_time' => $result['execution_time'] ?? null,
                ])
                ->log('Reprocess attendance from monitor');

            $message = ($result['message'] ?? 'Proses ulang selesai.').' (Monitor telah diperbarui)';
            $this->toastSuccess($message);

            $this->resetPage();
        } catch (\Exception $e) {
            $message = 'Gagal memproses ulang data absensi: '.$e->getMessage();
            $this->toastError($message);
        }
    }

    public function mount(): void
    {
        if ($this->selectedDate === '') {
            $this->selectedDate = now()->format('Y-m-d');
        }

        if ($this->dateFrom === '' || $this->dateTo === '') {
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        }
    }

    public function updated($name): void
    {
        if (in_array($name, ['mode', 'selectedDate', 'dateFrom', 'dateTo', 'unitKerja', 'search', 'statusFilter', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function getUnitKerjaListProperty(): Collection
    {
        return Employee::query()
            ->active()
            ->whereNotNull('satuan_kerja')
            ->pluck('satuan_kerja')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    public function render()
    {
        $employees = $this->getEmployeeBaseQuery()->get(['id', 'id_pegawai', 'nama', 'nip', 'satuan_kerja']);
        $lastAttendanceOperation = $this->lastAttendanceOperation;

        if ($this->mode === 'range') {
            $rows = $this->buildRangeRows($employees);

            return view('livewire.sdm.attendance-monitor', [
                'rows' => $this->paginateCollection($rows),
                'isRange' => true,
                'lastAttendanceOperation' => $lastAttendanceOperation,
            ]);
        }

        $rows = $this->buildDailyRows($employees);

        return view('livewire.sdm.attendance-monitor', [
            'rows' => $this->paginateCollection($rows),
            'isRange' => false,
            'lastAttendanceOperation' => $lastAttendanceOperation,
        ]);
    }

    public function getLastAttendanceOperationProperty(): ?Activity
    {
        return Activity::query()
            ->where('log_name', 'attendance_operations')
            ->latest('created_at')
            ->first();
    }

    private function getEmployeeBaseQuery()
    {
        return Employee::query()
            ->active()
            ->when($this->unitKerja !== '', function ($query) {
                $query->where('satuan_kerja', $this->unitKerja);
            })
            ->when($this->search !== '', function ($query) {
                $term = trim($this->search);
                $query->where(function ($q) use ($term) {
                    $q->where('nama', 'like', '%'.$term.'%')
                        ->orWhere('nip', 'like', '%'.$term.'%');
                });
            })
            ->orderBy('nama');
    }

    private function buildDailyRows(Collection $employees): Collection
    {
        $selectedDate = Carbon::parse($this->selectedDate);
        $isWorkingDay = $this->isWorkingDay($selectedDate);

        [$employeeToUserId, $userIds] = $this->buildEmployeeUserMap($employees);

        $attendanceByUser = EmployeeAttendance::query()
            ->whereDate('date', $selectedDate->format('Y-m-d'))
            ->when(! empty($userIds), fn ($query) => $query->whereIn('user_id', $userIds))
            ->get()
            ->keyBy('user_id');

        $rows = $employees->map(function (Employee $employee) use ($employeeToUserId, $attendanceByUser, $isWorkingDay) {
            $userId = $employeeToUserId[$employee->id] ?? null;
            $attendance = $userId ? $attendanceByUser->get($userId) : null;

            $status = 'holiday';
            $statusLabel = 'Libur';
            $source = 'inferred';

            if ($attendance) {
                $status = $attendance->status;
                $statusLabel = $attendance->status_label;
                $source = 'record';
            } elseif ($isWorkingDay) {
                $status = 'absent';
                $statusLabel = 'Tidak Hadir';
            }

            return [
                'employee_id' => $employee->id,
                'name' => $employee->nama ?? '-',
                'nip' => $employee->nip ?? '-',
                'unit_kerja' => $employee->satuan_kerja ?? '-',
                'status' => $status,
                'status_label' => $statusLabel,
                'check_in' => $attendance?->formatted_check_in ?? '-',
                'check_out' => $attendance?->formatted_check_out ?? '-',
                'source' => $source,
            ];
        });

        if ($this->statusFilter !== '') {
            $rows = $rows->where('status', $this->statusFilter)->values();
        }

        return $rows;
    }

    private function buildRangeRows(Collection $employees): Collection
    {
        $start = Carbon::parse($this->dateFrom);
        $end = Carbon::parse($this->dateTo);

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        [$employeeToUserId, $userIds] = $this->buildEmployeeUserMap($employees);

        $attendances = EmployeeAttendance::query()
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->when(! empty($userIds), fn ($query) => $query->whereIn('user_id', $userIds))
            ->get()
            ->groupBy(function (EmployeeAttendance $attendance) {
                return $attendance->user_id.'|'.$attendance->date->format('Y-m-d');
            });

        return $employees->map(function (Employee $employee) use ($employeeToUserId, $attendances, $start, $end) {
            $userId = $employeeToUserId[$employee->id] ?? null;

            $present = 0;
            $late = 0;
            $incomplete = 0;
            $sick = 0;
            $leave = 0;
            $halfDay = 0;
            $absent = 0;
            $workingDays = 0;
            $statusDates = [
                'hadir' => [],
                'terlambat' => [],
                'tidak_hadir' => [],
                'sakit' => [],
                'cuti' => [],
                'tidak_lengkap' => [],
                'setengah_hari' => [],
            ];

            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                if (! $this->isWorkingDay($cursor)) {
                    $cursor->addDay();

                    continue;
                }

                $workingDays++;
                $key = $userId ? ($userId.'|'.$cursor->format('Y-m-d')) : null;
                $attendance = $key ? $attendances->get($key)?->first() : null;

                if (! $attendance) {
                    $absent++;
                    $statusDates['tidak_hadir'][] = $cursor->copy()->format('Y-m-d');
                    $cursor->addDay();

                    continue;
                }

                $status = $attendance->status;

                if (in_array($status, ['present', 'on_time', 'early_arrival'], true)) {
                    $present++;
                    $statusDates['hadir'][] = $cursor->copy()->format('Y-m-d');
                } elseif ($status === 'late') {
                    $late++;
                    $statusDates['terlambat'][] = $cursor->copy()->format('Y-m-d');
                } elseif ($status === 'incomplete') {
                    $incomplete++;
                    $statusDates['tidak_lengkap'][] = $cursor->copy()->format('Y-m-d');
                } elseif ($status === 'sick') {
                    $sick++;
                    $statusDates['sakit'][] = $cursor->copy()->format('Y-m-d');
                } elseif ($status === 'leave') {
                    $leave++;
                    $statusDates['cuti'][] = $cursor->copy()->format('Y-m-d');
                } elseif ($status === 'half_day') {
                    $halfDay++;
                    $statusDates['setengah_hari'][] = $cursor->copy()->format('Y-m-d');
                } elseif ($status === 'absent') {
                    $absent++;
                    $statusDates['tidak_hadir'][] = $cursor->copy()->format('Y-m-d');
                }

                $cursor->addDay();
            }

            $hadir = $present + $late + $incomplete + $halfDay;
            $attendanceRate = $workingDays > 0
                ? round(($hadir / $workingDays) * 100, 2)
                : 0;

            return [
                'employee_id' => $employee->id,
                'name' => $employee->nama ?? '-',
                'nip' => $employee->nip ?? '-',
                'unit_kerja' => $employee->satuan_kerja ?? '-',
                'working_days' => $workingDays,
                'hadir' => $hadir,
                'present' => $present,
                'late' => $late,
                'incomplete' => $incomplete,
                'half_day' => $halfDay,
                'sick' => $sick,
                'leave' => $leave,
                'absent' => $absent,
                'status_dates' => $statusDates,
                'attendance_rate' => $attendanceRate,
            ];
        });
    }

    private function buildEmployeeUserMap(Collection $employees): array
    {
        $employeeIds = $employees->pluck('id')->filter()->values();
        $employeeMasterIds = $employees->pluck('id_pegawai')->filter()->map(fn ($id) => (string) $id)->values();

        $employeeNips = $employees
            ->pluck('nip')
            ->filter()
            ->map(fn ($nip) => (string) $nip)
            ->values();

        $normalizedNips = $employeeNips
            ->map(fn ($nip) => $this->normalizeNip($nip))
            ->filter()
            ->values();

        $nipCandidates = $employeeNips
            ->merge($normalizedNips)
            ->unique()
            ->values();

        $relatedEmployeeRows = $employeeMasterIds->isEmpty()
            ? collect()
            : Employee::withTrashed()
                ->whereIn('id_pegawai', $employeeMasterIds)
                ->get(['id', 'id_pegawai']);

        $relatedEmployeeIds = $relatedEmployeeRows->pluck('id')->unique()->values();

        $users = User::query()
            ->where(function ($query) use ($nipCandidates, $employeeIds, $relatedEmployeeIds) {
                if (! empty($nipCandidates->all())) {
                    $query->where(function ($q) use ($nipCandidates) {
                        $q->whereNotNull('nip')
                            ->whereIn('nip', $nipCandidates);
                    });
                }

                if (! $employeeIds->isEmpty()) {
                    $query->orWhere(function ($q) use ($employeeIds) {
                        $q->where('employee_type', 'employee')
                            ->whereIn('employee_id', $employeeIds);
                    });
                }

                if (! $relatedEmployeeIds->isEmpty()) {
                    $query->orWhere(function ($q) use ($relatedEmployeeIds) {
                        $q->where('employee_type', 'employee')
                            ->whereIn('employee_id', $relatedEmployeeIds);
                    });
                }
            })
            ->get(['id', 'nip', 'employee_id', 'employee_type']);

        $relatedEmployeesById = $relatedEmployeeRows->keyBy('id');

        $usersByNormalizedNip = $users
            ->mapWithKeys(function (User $user) {
                return [$this->normalizeNip($user->nip) => $user->id];
            })
            ->filter();

        $usersByEmployeeId = $users
            ->filter(fn (User $user) => $user->employee_type === 'employee' && ! empty($user->employee_id))
            ->mapWithKeys(fn (User $user) => [(int) $user->employee_id => $user->id]);

        $usersByEmployeeMasterId = $users
            ->filter(fn (User $user) => $user->employee_type === 'employee' && ! empty($user->employee_id))
            ->mapWithKeys(function (User $user) use ($relatedEmployeesById) {
                $employeeRow = $relatedEmployeesById->get((int) $user->employee_id);

                return $employeeRow && ! empty($employeeRow->id_pegawai)
                    ? [(string) $employeeRow->id_pegawai => $user->id]
                    : [];
            });

        $employeeToUserId = [];
        foreach ($employees as $employee) {
            $normNip = $this->normalizeNip($employee->nip);
            $employeeToUserId[$employee->id] = $usersByEmployeeId->get((int) $employee->id)
                ?? ($normNip ? $usersByNormalizedNip->get($normNip) : null)
                ?? (! empty($employee->id_pegawai) ? $usersByEmployeeMasterId->get((string) $employee->id_pegawai) : null);
        }

        $userIds = collect($employeeToUserId)->filter()->unique()->values()->all();

        return [$employeeToUserId, $userIds];
    }

    private function isWorkingDay(Carbon $date): bool
    {
        $workingDays = AttendanceSetting::getValue('working_days', '1,2,3,4,5,6');
        $workingDaysArray = is_array($workingDays)
            ? array_map('intval', $workingDays)
            : array_map('intval', explode(',', (string) $workingDays));

        return in_array($date->dayOfWeekIso, $workingDaysArray, true)
            && ! Holiday::isHoliday($date);
    }

    private function normalizeNip(?string $nip): ?string
    {
        if ($nip === null || trim($nip) === '') {
            return null;
        }

        return rtrim(trim($nip), '_');
    }

    private function paginateCollection(Collection $items): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $slice = $items->forPage($page, $this->perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $items->count(),
            $this->perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}
