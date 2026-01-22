<?php

namespace App\Livewire\Sdm;

use App\Models\User;
use App\Models\Employee;
use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Models\AttendanceLog;
use App\Models\FingerprintUserMapping;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class EmployeeAttendanceManagement extends Component

{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filter properties
    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $status = '';
    public $employeeType = '';
    public $unitKerja = '';
    public $perPage = 10;

    // Modal properties
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDetailModal = false;
    public $showUnmappedModal = false;
    public $selectedAttendanceId;
    public $mappingUserIds = [];

    // Form properties
    public $user_id;
    public $date;
    public $check_in_time;
    public $check_out_time;
    public $status_form;
    public $notes;

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    protected function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'check_in_time' => 'nullable',
            'check_out_time' => 'nullable',
            'status_form' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function mount()
    {
        // Set default date range to current month
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = EmployeeAttendance::with(['employee'])
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc');

        // Apply filters
        if ($this->search) {
            $query->whereHas('employee', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('date', [$this->dateFrom, $this->dateTo]);
        } elseif ($this->dateFrom) {
            $query->whereDate('date', '>=', $this->dateFrom);
        } elseif ($this->dateTo) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $attendances = $query->paginate($this->perPage);

        // Get list of employees/users for dropdown
        $employees = User::query()
            ->with(['employee', 'dosen'])
            ->orderBy('name')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'full_name_with_title' => $user->full_name_with_title, // Accessor handles both employee/dosen
                    'nip' => $user->nip ?? ($user->employee->nip ?? ($user->dosen->nip ?? '-')),
                ];
            });

        return view('livewire.sdm.employee-attendance-management', [
            'attendances' => $attendances,
            'employees' => $employees,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function edit($id)
    {
        $attendance = EmployeeAttendance::find($id);
        if (!$attendance) {
            session()->flash('error', 'Data absensi tidak ditemukan.');
            return;
        }

        $this->selectedAttendanceId = $attendance->id;
        $this->user_id = $attendance->user_id;
        $this->date = $attendance->date->format('Y-m-d');
        $this->check_in_time = $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : null;
        $this->check_out_time = $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : null;
        $this->status_form = $attendance->status;
        $this->notes = $attendance->notes;

        $this->showEditModal = true;
    }

    public function showUnmappedLogs()
    {
        $this->showUnmappedModal = true;
    }

    public function getGroupedUnmappedLogsProperty()
    {
        return \App\Models\AttendanceLog::with('mesinFinger')
            ->whereNull('user_id')
            ->select('pin', 'mesin_finger_id', DB::raw('count(*) as total'), DB::raw('max(datetime) as last_seen'), DB::raw('max(id) as last_id'))
            ->groupBy('pin', 'mesin_finger_id')
            ->get()
            ->map(function($item) {
                $lastLog = \App\Models\AttendanceLog::find($item->last_id);
                return [
                    'pin' => $item->pin,
                    'total' => $item->total,
                    'last_seen' => $item->last_seen,
                    'mesin' => $item->mesinFinger->sn ?? '-',
                    'adms_id' => $lastLog->adms_id ?? '-',
                ];
            });
    }

    public function mapPin($pin)
    {
        $userId = $this->mappingUserIds[$pin] ?? null;
        
        if (!$userId) {
            session()->flash('error', 'Silakan pilih karyawan terlebih dahulu.');
            return;
        }

        try {
            DB::beginTransaction();

            $user = User::find($userId);
            if (!$user) {
                throw new \Exception('Karyawan tidak ditemukan.');
            }

            // 1. Update User's Fingerprint PIN
            $user->update([
                'fingerprint_pin' => $pin,
                'fingerprint_enabled' => true
            ]);

            // 2. Update existing unmapped logs
            \App\Models\AttendanceLog::where('pin', $pin)
                ->whereNull('user_id')
                ->update(['user_id' => $userId]);

            // 3. Process logs for this user to generate attendance records
            $attendanceService = new AttendanceService();
            $attendanceService->processLogs(null, null, $userId);

            DB::commit();

            // Clear selection
            unset($this->mappingUserIds[$pin]);
            
            // Refresh stats
            $this->dispatch('refreshStats'); // Optional if you have listeners, else render handles it
            
            session()->flash('success', "PIN {$pin} berhasil dimapping ke {$user->name}. Data absensi telah diperbarui.");

        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Gagal melakukan mapping: ' . $e->getMessage());
        }
    }

    public function view($id)
    {
        $attendance = EmployeeAttendance::with(['employee'])->find($id);
        if (!$attendance) {
            session()->flash('error', 'Data absensi tidak ditemukan.');
            return;
        }

        $this->selectedAttendanceId = $attendance->id;
        $this->showDetailModal = true;
    }

    public function save()
    {
        $validatedData = $this->validate();

        try {
            if ($this->selectedAttendanceId) {
                // Update existing attendance
                $attendance = EmployeeAttendance::find($this->selectedAttendanceId);
                if (!$attendance) {
                    session()->flash('error', 'Data absensi tidak ditemukan.');
                    return;
                }

                $checkInDateTime = $this->check_in_time ? 
                    \Carbon\Carbon::parse($this->date . ' ' . $this->check_in_time) : null;
                $checkOutDateTime = $this->check_out_time ? 
                    \Carbon\Carbon::parse($this->date . ' ' . $this->check_out_time) : null;

                $attendance->update([
                    'user_id' => $this->user_id,
                    'date' => $this->date,
                    'check_in_time' => $checkInDateTime,
                    'check_out_time' => $checkOutDateTime,
                    'status' => $this->status_form,
                    'notes' => $this->notes,
                ]);

                session()->flash('success', 'Data absensi berhasil diperbarui.');
            } else {
                // Create new attendance
                $checkInDateTime = $this->check_in_time ? 
                    \Carbon\Carbon::parse($this->date . ' ' . $this->check_in_time) : null;
                $checkOutDateTime = $this->check_out_time ? 
                    \Carbon\Carbon::parse($this->date . ' ' . $this->check_out_time) : null;

                $attendance = EmployeeAttendance::create([
                    'user_id' => $this->user_id,
                    'date' => $this->date,
                    'check_in_time' => $checkInDateTime,
                    'check_out_time' => $checkOutDateTime,
                    'status' => $this->status_form,
                    'notes' => $this->notes,
                    'created_by' => auth()->id(),
                ]);

                session()->flash('success', 'Data absensi berhasil ditambahkan.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan data absensi: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $attendance = EmployeeAttendance::find($id);
        if (!$attendance) {
            session()->flash('error', 'Data absensi tidak ditemukan.');
            return;
        }

        $attendance->delete();
        session()->flash('success', 'Data absensi berhasil dihapus.');
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDetailModal = false;
        $this->showUnmappedModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'selectedAttendanceId',
            'user_id',
            'date',
            'check_in_time',
            'check_out_time',
            'status_form',
            'notes',
        ]);
        $this->resetErrorBag();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'dateFrom', 'dateTo', 'status']);
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
    
    public function getAttendanceEmployeeName()
    {
        if (!$this->selectedAttendanceId) {
            return null;
        }
        
        $attendance = \App\Models\Employee\Attendance::with('employee')->find($this->selectedAttendanceId);
        return $attendance ? ($attendance->employee ? $attendance->employee->name : 'N/A') : 'N/A';
    }
    
    public function getAttendanceFormattedDate()
    {
        if (!$this->selectedAttendanceId) {
            return null;
        }
        
        $attendance = \App\Models\Employee\Attendance::find($this->selectedAttendanceId);
        return $attendance ? $attendance->formatted_date : 'N/A';
    }
    
    public function getAttendanceFormattedCheckIn()
    {
        if (!$this->selectedAttendanceId) {
            return null;
        }
        
        $attendance = \App\Models\Employee\Attendance::find($this->selectedAttendanceId);
        return $attendance ? $attendance->formatted_check_in : 'N/A';
    }
    
    public function getAttendanceFormattedCheckOut()
    {
        if (!$this->selectedAttendanceId) {
            return null;
        }
        
        $attendance = \App\Models\Employee\Attendance::find($this->selectedAttendanceId);
        return $attendance ? $attendance->formatted_check_out : 'N/A';
    }
    
    public function getAttendanceStatusBadge()
    {
        if (!$this->selectedAttendanceId) {
            return 'gray';
        }
        
        $attendance = \App\Models\Employee\Attendance::find($this->selectedAttendanceId);
        return $attendance ? $attendance->status_badge : 'gray';
    }
    
    public function getAttendanceStatusLabel()
    {
        if (!$this->selectedAttendanceId) {
            return 'N/A';
        }
        
        $attendance = \App\Models\Employee\Attendance::find($this->selectedAttendanceId);
        return $attendance ? $attendance->status_label : 'N/A';
    }
    
    public function getAttendanceTotalHoursFormatted()
    {
        if (!$this->selectedAttendanceId) {
            return "0:00";
        }
        
        $attendance = \App\Models\Employee\Attendance::find($this->selectedAttendanceId);
        if (!$attendance || !$attendance->total_hours) {
            return "0:00";
        }
        
        $totalMinutes = (int) abs(round($attendance->total_hours * 60));
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        
        return sprintf("%d:%02d", $hours, $minutes);
    }
    
    public function getAttendanceNotes()
    {
        if (!$this->selectedAttendanceId) {
            return '';
        }
        
        $attendance = \App\Models\Employee\Attendance::find($this->selectedAttendanceId);
        return $attendance ? ($attendance->notes ?? '') : '';
    }

    /**
     * Get overtime formatted for the selected attendance
     */
    public function getAttendanceOvertimeFormatted()
    {
        if (!$this->selectedAttendanceId) {
            return "0:00";
        }
        
        $attendance = \App\Models\Employee\Attendance::find($this->selectedAttendanceId);
        if (!$attendance || !$attendance->check_out_time) {
            return "0:00";
        }
        
        $shift = $attendance->effective_shift;
        if (!$shift || !$shift->end_time) {
            return "0:00";
        }
        
        $shiftEnd = \Carbon\Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $shift->end_time);
        $checkOut = $attendance->check_out_time;
        
        if ($checkOut->gt($shiftEnd)) {
            $totalMinutes = (int) abs(round($shiftEnd->floatDiffInMinutes($checkOut)));
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            return sprintf("%d:%02d", $hours, $minutes);
        }
        
        return "0:00";
    }

    /**
     * Get early arrival formatted for the selected attendance
     */
    public function getAttendanceEarlyFormatted()
    {
        if (!$this->selectedAttendanceId) {
            return "0:00";
        }
        
        $attendance = \App\Models\Employee\Attendance::find($this->selectedAttendanceId);
        if (!$attendance || !$attendance->check_in_time) {
            return "0:00";
        }
        
        $shift = $attendance->effective_shift;
        if (!$shift || !$shift->start_time) {
            return "0:00";
        }
        
        $shiftStart = \Carbon\Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $shift->start_time);
        $checkIn = $attendance->check_in_time;
        
        if ($checkIn->lt($shiftStart)) {
            $totalMinutes = (int) abs(round($checkIn->floatDiffInMinutes($shiftStart)));
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            return sprintf("%d:%02d", $hours, $minutes);
        }
        
        return "0:00";
    }

    /**
     * Get shift name for the selected attendance
     */
    public function getAttendanceShiftName()
    {
        if (!$this->selectedAttendanceId) {
            return '-';
        }
        
        $attendance = \App\Models\Employee\Attendance::find($this->selectedAttendanceId);
        if (!$attendance) {
            return '-';
        }
        
        $shift = $attendance->effective_shift;
        return $shift ? $shift->name : 'Default';
    }

    /**
     * Get shift time range for the selected attendance
     */
    public function getAttendanceShiftTime()
    {
        if (!$this->selectedAttendanceId) {
            return '-';
        }
        
        $attendance = \App\Models\Employee\Attendance::find($this->selectedAttendanceId);
        if (!$attendance) {
            return '-';
        }
        
        $shift = $attendance->effective_shift;
        if (!$shift) {
            return '-';
        }
        
        return substr($shift->start_time, 0, 5) . ' - ' . substr($shift->end_time, 0, 5);
    }

    public function processAttendanceLogs()
    {
        try {
            $attendanceService = new AttendanceService();
            
            // Process all attendance logs (we use NULL dates to process all unprocessed)
            $result = $attendanceService->processLogs();
            
            // Refresh counts/stats
            $stats = $this->attendance_log_stats;
            
            $message = $result['message'];
            session()->flash('success', $message);
            
            // Refresh the data
            $this->render();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memproses data absensi: ' . $e->getMessage());
        }
    }

    /**
     * Force reprocess ALL attendance data (including already processed)
     */
    public function reprocessAllAttendance()
    {
        try {
            $attendanceService = new AttendanceService();
            
            // Force reprocess all logs
            $result = $attendanceService->processLogs(null, null, null, true);
            
            $message = $result['message'] . " (Semua data telah diproses ulang)";
            session()->flash('success', $message);
            
            // Refresh the data
            $this->render();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memproses ulang data absensi: ' . $e->getMessage());
        }
    }

    /**
     * Get attendance log statistics for display
     */
    public function getAttendanceLogStatsProperty()
    {
        try {
            $totalLogs = \App\Models\AttendanceLog::count();
            $mappedLogs = \App\Models\AttendanceLog::whereNotNull('user_id')->count();
            $unmappedLogs = \App\Models\AttendanceLog::whereNull('user_id')->count();
            $processedLogs = \App\Models\AttendanceLog::whereNotNull('processed_at')->count();
            $uniqueUsers = \App\Models\AttendanceLog::whereNotNull('user_id')->distinct('user_id')->count('user_id');
            $dateRange = \App\Models\AttendanceLog::selectRaw('MIN(datetime) as min_date, MAX(datetime) as max_date')->first();

            return [
                'total_logs' => $totalLogs,
                'mapped_logs' => $mappedLogs,
                'unmapped_logs' => $unmappedLogs,
                'processed_logs' => $processedLogs,
                'unique_users' => $uniqueUsers,
                'date_range' => [
                    'start' => $dateRange->min_date,
                    'end' => $dateRange->max_date,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'total_logs' => 0,
                'mapped_logs' => 0,
                'unmapped_logs' => 0,
                'processed_logs' => 0,
                'unique_users' => 0,
                'date_range' => ['start' => null, 'end' => null],
            ];
        }
    }

    /**
     * Clear all employee attendance data (for fresh processing)
     */
    public function clearAllEmployeeAttendance()
    {
        try {
            $deletedCount = \App\Models\Employee\Attendance::count();
            \App\Models\Employee\Attendance::truncate();
            
            session()->flash('success', "Berhasil menghapus {$deletedCount} data absensi karyawan.");
            
            // Refresh the data
            $this->render();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data absensi: ' . $e->getMessage());
        }
    }

}
