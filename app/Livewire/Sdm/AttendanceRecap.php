<?php

namespace App\Livewire\Sdm;

use App\Models\User;
use App\Models\Employee;
use App\Models\Employee\Attendance as EmployeeAttendance;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceRecapExport;

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
        $this->showFilters = !$this->showFilters;
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
                'weekday' => $current->isoFormat('ddd'),
                'is_weekend' => !$isWorkingDay,
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

        // Get user IDs that have attendance in this month
        $userIdsWithAttendance = EmployeeAttendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        // Query users who have attendance records
        $employeesQuery = User::whereIn('id', $userIdsWithAttendance)
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('nip', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->unitKerja, function($query) {
                // Join with employees table using NIP (like in UnitShiftManagement)
                $query->whereExists(function($q) {
                    $q->select(\DB::raw(1))
                      ->from('employees')
                      ->where(function($subQ) {
                          $subQ->whereRaw('users.nip = employees.nip')
                               ->orWhereRaw("users.nip = TRIM(TRAILING '_' FROM employees.nip)");
                      })
                      ->where('employees.satuan_kerja', $this->unitKerja);
                });
            })
            ->orderBy('name');

        $employees = $employeesQuery->get();

        // Fetch attendance data for the visible employees in this date range
        $employeeIds = $employees->pluck('id')->toArray();
        
        $attendances = EmployeeAttendance::whereIn('user_id', $employeeIds)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy('user_id');

        // Transform data into a matrix structure
        $attendanceMatrix = [];
        // Use daysInRange property which is robust
        $daysInRange = $this->daysInRange;
        
        foreach ($employees as $employee) {
            $employeeAttendances = $attendances->get($employee->id, collect());
            $dailyStatus = [];
            
            foreach ($daysInRange as $day) {
                $date = $day['date'];
                $record = $employeeAttendances->firstWhere('date.bg', $date) 
                       ?? $employeeAttendances->filter(function($item) use ($date) {
                            return $item->date->format('Y-m-d') === $date;
                       })->first();

                $dailyStatus[$day['day']] = $record ? [
                    'status' => $record->status,
                    'badge' => $record->status_badge,
                    'short_label' => $this->getShortStatusLabel($record->status),
                    'check_in' => $record->formatted_check_in,
                    'check_out' => $record->formatted_check_out,
                ] : null;
            }
            
            $attendanceMatrix[$employee->id] = $dailyStatus;
        }

        return [
            'employees' => $employees,
            'attendanceMatrix' => $attendanceMatrix,
            'days' => $daysInRange,
        ];
    }

    public function render()
    {
        $data = $this->getRecapData();

        return view('livewire.sdm.attendance-recap', [
            'employees' => $data['employees'],
            'attendanceMatrix' => $data['attendanceMatrix'],
            'days' => $data['days'],
        ]);
    }

    public function export()
    {
        $data = $this->getRecapData();
        
        $fileName = 'Rekap_Absensi_' . Carbon::create()->month($this->month)->year($this->year)->format('F_Y') . '.xlsx';
        if ($this->useCustomRange) {
            $fileName = 'Rekap_Absensi_Custom_' . date('Ymd') . '.xlsx';
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
            default => '?'
        };
    }
}
