<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display staff attendance history.
     */
    public function index(Request $request): View
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $statusFilter = $request->get('status', '');

        $daysInMonth = $this->getDaysInMonth($year, $month);
        $data = $this->getAttendanceData($year, $month, $statusFilter, $daysInMonth);

        return view('staff.attendance-history', [
            'history' => $data['history'],
            'summary' => $data['summary'],
            'months' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ],
            'years' => range(now()->year, 2024, -1),
            'month' => $month,
            'year' => $year,
        ]);
    }

    private function getDaysInMonth($year, $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $days = [];
        $current = $startDate->copy();

        // Get working days setting (1=Mon, 2=Tue, ..., 7=Sun)
        $workingDays = AttendanceSetting::getValue('working_days', '1,2,3,4,5,6');
        $workingDaysArray = $this->normalizeWorkingDays($workingDays);

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

    private function getAttendanceData($year, $month, $statusFilter, $daysInMonth)
    {
        $user = Auth::user();
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

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

        foreach ($daysInMonth as $day) {
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
            if ($statusFilter && $status !== $statusFilter) {
                if ($statusFilter === 'present' && ! in_array($status, ['on_time', 'early_arrival', 'present', 'late'])) {
                    continue;
                }
                if ($statusFilter === 'late' && $status !== 'late') {
                    continue;
                }
                if ($statusFilter === 'absent' && $status !== 'absent') {
                    continue;
                }
                if ($statusFilter === 'incomplete' && $status !== 'incomplete') {
                    continue;
                }

                // If filtering by specific status and it doesn't match, skip
                if (! in_array($statusFilter, ['present', 'late', 'absent', 'incomplete'])) {
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

    /**
     * Normalize working days config into integer day-of-week ISO values.
     *
     * @param  array<int, int|string>|string  $workingDays
     * @return array<int, int>
     */
    private function normalizeWorkingDays(array|string $workingDays): array
    {
        $source = is_array($workingDays) ? $workingDays : explode(',', $workingDays);

        $normalized = collect($source)
            ->map(fn ($value) => (int) trim((string) $value))
            ->filter(fn (int $value) => $value >= 1 && $value <= 7)
            ->unique()
            ->values()
            ->all();

        return $normalized !== [] ? $normalized : [1, 2, 3, 4, 5, 6];
    }
}
