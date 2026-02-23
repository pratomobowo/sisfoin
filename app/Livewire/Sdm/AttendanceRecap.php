<?php

namespace App\Livewire\Sdm;

use App\Exports\AttendanceRecapExport;
use App\Models\Employee;
use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.app')]
class AttendanceRecap extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filters
    public $month;

    public $year;

    public $dateFrom = '';

    public $dateTo = '';

    public $useCustomRange = false;

    public $unitKerja = '';

    public $search = '';

    public $perPage = 10;

    public $showFilters = true;

    protected $queryString = [
        'month' => ['except' => ''],
        'year' => ['except' => ''],
        'unitKerja' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->month = now()->month;
        $this->year = now()->year;

        // Set default custom range (21st of last month to 20th of current month)
        $this->dateFrom = now()->subMonth()->day(21)->format('Y-m-d');
        $this->dateTo = now()->day(20)->format('Y-m-d');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedUnitKerja()
    {
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function getUnitKerjaListProperty()
    {
        return Employee::whereNotNull('satuan_kerja')
            ->distinct()
            ->pluck('satuan_kerja')
            ->filter()
            ->sort()
            ->values();
    }

    public function getDaysInRangeProperty()
    {
        if ($this->useCustomRange && $this->dateFrom && $this->dateTo) {
            $startDate = Carbon::parse($this->dateFrom);
            $endDate = Carbon::parse($this->dateTo);
        } else {
            $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();
        }

        $days = [];
        $current = $startDate->copy();

        // Get working days setting (1=Mon, 2=Tue, ..., 7=Sun)
        $workingDays = \App\Models\AttendanceSetting::getValue('working_days', '1,2,3,4,5,6');
        $workingDaysArray = is_array($workingDays) ? $workingDays : explode(',', $workingDays);

        while ($current->lte($endDate)) {
            $isHoliday = \App\Models\Holiday::isHoliday($current);
            $holidayInfo = $isHoliday ? \App\Models\Holiday::getHolidayInfo($current) : null;
            $isWorkingDay = in_array($current->dayOfWeekIso, $workingDaysArray);

            $days[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->day,
                'weekday' => $current->locale('id')->isoFormat('ddd'),
                'is_weekend' => ! $isWorkingDay,
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayInfo?->name,
            ];

            $current->addDay();
        }

        return $days;
    }

    // Keep old property for backward compatibility
    public function getDaysInMonthProperty()
    {
        return $this->daysInRange;
    }

    public function getRecapData()
    {
        if ($this->useCustomRange && $this->dateFrom && $this->dateTo) {
            $startDate = Carbon::parse($this->dateFrom);
            $endDate = Carbon::parse($this->dateTo);
        } else {
            $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();
        }

        $employees = Employee::query()
            ->active()
            ->when($this->search, function ($query) {
                $term = trim($this->search);

                $query->where(function ($q) use ($term) {
                    $q->where('nama', 'like', '%'.$term.'%')
                        ->orWhere('nip', 'like', '%'.$term.'%');
                });
            })
            ->when($this->unitKerja, fn ($query) => $query->where('satuan_kerja', $this->unitKerja))
            ->orderBy('nama')
            ->get(['id', 'id_pegawai', 'nama', 'nip', 'satuan_kerja']);

        [$employeeToUserId, $userIds] = $this->buildEmployeeUserMap($employees);

        $attendances = EmployeeAttendance::query()
            ->whereIn('user_id', $userIds)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy('user_id');

        // Transform data into a matrix structure
        $attendanceMatrix = [];
        // Use daysInRange property which is robust
        $daysInRange = $this->daysInRange;

        foreach ($employees as $employee) {
            $userId = $employeeToUserId[$employee->id] ?? null;
            $employeeAttendances = $userId ? $attendances->get($userId, collect()) : collect();
            $dailyStatus = [];

            foreach ($daysInRange as $day) {
                $date = $day['date'];
                $record = $employeeAttendances->first(function ($item) use ($date) {
                    return $item->date->format('Y-m-d') === $date;
                });

                if ($record) {
                    $dailyStatus[$day['date']] = [
                        'status' => $record->status,
                        'badge' => $record->status_badge,
                        'short_label' => $this->getShortStatusLabel($record->status),
                        'check_in' => $record->formatted_check_in,
                        'check_out' => $record->formatted_check_out,
                    ];
                } else {
                    // Synthesis: If no record, check if it's a past working day
                    $isPastDay = Carbon::parse($day['date'])->isPast() && ! Carbon::parse($day['date'])->isToday();
                    $isWorkingDay = ! $day['is_weekend'] && ! $day['is_holiday'];

                    if ($isPastDay && $isWorkingDay) {
                        $dailyStatus[$day['date']] = [
                            'status' => 'absent',
                            'badge' => 'red',
                            'short_label' => 'A',
                            'check_in' => null,
                            'check_out' => null,
                        ];
                    } else {
                        $dailyStatus[$day['date']] = null;
                    }
                }
            }

            $attendanceMatrix[$employee->id] = $dailyStatus;
        }

        return [
            'employees' => $employees,
            'attendanceMatrix' => $attendanceMatrix,
            'days' => $daysInRange,
        ];
    }

    private function buildEmployeeUserMap($employees): array
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
            ->mapWithKeys(fn (User $user) => [$this->normalizeNip($user->nip) => $user->id])
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

    private function normalizeNip(?string $nip): ?string
    {
        if ($nip === null || trim($nip) === '') {
            return null;
        }

        return rtrim(trim($nip), '_');
    }

    public function render()
    {
        $data = $this->getRecapData();

        return view('livewire.sdm.attendance-recap', [
            'employees' => $data['employees'],
            'attendanceMatrix' => $data['attendanceMatrix'],
            'days' => $data['days'],
            'lastAttendanceOperation' => $this->lastAttendanceOperation,
        ]);
    }

    public function getLastAttendanceOperationProperty(): ?Activity
    {
        return Activity::query()
            ->where('log_name', 'attendance_operations')
            ->latest('created_at')
            ->first();
    }

    public function export()
    {
        $data = $this->getRecapData();

        $fileName = 'Rekap_Absensi_'.Carbon::create()->month($this->month)->year($this->year)->format('F_Y').'.xlsx';
        if ($this->useCustomRange) {
            $fileName = 'Rekap_Absensi_Custom_'.date('Ymd').'.xlsx';
        }

        return Excel::download(new AttendanceRecapExport(
            $data['employees'],
            $data['attendanceMatrix'],
            $data['days']
        ), $fileName);
    }

    private function getShortStatusLabel($status)
    {
        return match ($status) {
            'present' => 'H', // Hadir
            'on_time' => 'H', // Hadir (Tepat Waktu)
            'early_arrival' => 'H', // Hadir (Awal)
            'late' => 'T', // Terlambat
            'absent' => 'A', // Alpha
            'half_day' => 'HD', // Half Day
            'sick' => 'S', // Sakit
            'leave' => 'C', // Cuti
            'incomplete' => '?', // Absen Tidak Lengkap
            default => '?'
        };
    }
}
