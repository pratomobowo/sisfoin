<?php

namespace App\Livewire\Staff;

use App\Models\AttendanceSetting;
use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AttendanceHistory extends Component
{
    use WithPagination;

    public int $perPage = 10;

    // Filters
    public $month;

    public $year;

    public $statusFilter = '';

    // UI State
    public $title = 'Riwayat Absensi';

    protected $queryString = [
        'month' => ['except' => ''],
        'year' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount()
    {
        $this->month = $this->month ?? now()->month;
        $this->year = $this->year ?? now()->year;
    }

    public function getDaysInMonthProperty()
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();

        $days = [];
        $current = $startDate->copy();

        // Get working days setting (1=Mon, 2=Tue, ..., 7=Sun)
        $workingDays = AttendanceSetting::getValue('working_days', '1,2,3,4,5,6');
        $workingDaysArray = is_array($workingDays) ? $workingDays : explode(',', $workingDays);

        while ($current->lte($endDate)) {
            $isHoliday = Holiday::isHoliday($current);
            $holidayInfo = $isHoliday ? Holiday::getHolidayInfo($current) : null;
            $isWorkingDay = in_array($current->dayOfWeekIso, $workingDaysArray);

            $days[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->day,
                'day_name' => $current->locale('id')->isoFormat('dddd'),
                'formatted_date' => $current->format('d M Y'),
                'is_weekend' => ! $isWorkingDay,
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayInfo?->name,
            ];

            $current->addDay();
        }

        return collect($days)->reverse();
    }

    public function getAttendanceData()
    {
        $user = auth()->user();
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()->format('Y-m-d');

        // Get actual attendance records
        $records = EmployeeAttendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        $history = [];
        $summary = [
            'present' => 0,
            'late' => 0,
            'incomplete' => 0,
            'absent' => 0,
            'holiday' => 0,
        ];

        foreach ($this->daysInMonth as $day) {
            $date = $day['date'];
            $record = $records->get($date);
            $status = null;
            $statusLabel = null;
            $statusBadge = null;
            $checkIn = null;
            $checkOut = null;
            $notes = null;

            if ($record) {
                $status = $record->status;
                $statusLabel = $record->status_label;
                $statusBadge = $record->status_badge;
                $checkIn = $record->check_in_time ? $record->check_in_time->format('H:i') : '-';
                $checkOut = $record->check_out_time ? $record->check_out_time->format('H:i') : '-';
                $notes = $record->notes;

                // Simple summary mapping
                if (in_array($status, ['on_time', 'early_arrival', 'present'])) {
                    $summary['present']++;
                } elseif ($status === 'late') {
                    $summary['present']++;
                    $summary['late']++;
                } elseif ($status === 'incomplete') {
                    $summary['incomplete']++;
                } elseif ($status === 'absent') {
                    $summary['absent']++;
                }
            } else {
                // Synthesis for missing records
                $isPastDay = Carbon::parse($date)->isPast() && ! Carbon::parse($date)->isToday();

                if ($day['is_holiday'] || $day['is_weekend']) {
                    $status = 'off';
                    $statusLabel = $day['is_holiday'] ? 'Libur' : 'Weekend';
                    $statusBadge = 'gray';
                    if ($day['is_holiday']) {
                        $summary['holiday']++;
                    }
                } elseif ($isPastDay) {
                    $status = 'absent';
                    $statusLabel = 'Tidak Hadir';
                    $statusBadge = 'red';
                    $summary['absent']++;
                }
            }

            // Apply status filter
            if ($this->statusFilter && $status !== $this->statusFilter) {
                if ($this->statusFilter === 'present' && ! in_array($status, ['on_time', 'early_arrival', 'present', 'late'])) {
                    continue;
                }
                if ($this->statusFilter === 'late' && $status !== 'late') {
                    continue;
                }
                if ($this->statusFilter === 'absent' && $status !== 'absent') {
                    continue;
                }
                if ($this->statusFilter === 'incomplete' && $status !== 'incomplete') {
                    continue;
                }

                // If filtering by specific status and it doesn't match, skip
                if (! in_array($this->statusFilter, ['present', 'late', 'absent', 'incomplete'])) {
                    continue;
                }
            }

            $history[] = [
                'date' => $day['date'],
                'day_name' => $day['day_name'],
                'formatted_date' => $day['formatted_date'],
                'is_holiday' => $day['is_holiday'],
                'is_weekend' => $day['is_weekend'],
                'holiday_name' => $day['holiday_name'],
                'status' => $status,
                'status_label' => $statusLabel,
                'status_badge' => $statusBadge,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'notes' => $notes,
            ];
        }

        return [
            'history' => $history,
            'summary' => $summary,
        ];
    }

    public function render()
    {
        $data = $this->getAttendanceData();
        $historyCollection = collect($data['history']);
        $history = new LengthAwarePaginator(
            $historyCollection->forPage($this->getPage(), $this->perPage)->values(),
            $historyCollection->count(),
            $this->perPage,
            $this->getPage(),
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('livewire.staff.attendance-history', [
            'history' => $history,
            'summary' => $data['summary'],
            'months' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ],
            'years' => range(now()->year, 2024, -1),
        ]);
    }
}
