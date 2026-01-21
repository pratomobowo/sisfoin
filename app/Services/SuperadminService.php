<?php

namespace App\Services;

use App\Models\User;
use App\Models\MesinFinger;
use App\Models\AttendanceLog;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SuperadminService
{
    /**
     * Get dashboard statistics for superadmin.
     */
    public function getDashboardStats(): array
    {
        return Cache::remember('superadmin.dashboard.stats', 300, function () {
            return [
                'total_users' => User::count(),
                'active_users' => User::count(), // Since is_active column doesn't exist, assume all users are active
                'total_roles' => Role::count(),
                'total_machines' => MesinFinger::count(),
                'active_machines' => MesinFinger::where('status', 'active')->count(),
                'today_attendance' => AttendanceLog::whereDate('datetime', today())->count(),
                'recent_activities' => $this->getRecentActivities(),
                'user_growth' => $this->getUserGrowthData(),
                'attendance_summary' => $this->getAttendanceSummary(),
            ];
        });
    }

    /**
     * Get recent activities for dashboard.
     */
    public function getRecentActivities(int $limit = 10): array
    {
        return Activity::with('causer')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'causer_name' => $activity->causer?->name ?? 'System',
                    'subject_type' => class_basename($activity->subject_type),
                    'created_at' => $activity->created_at,
                    'properties' => $activity->properties,
                ];
            })
            ->toArray();
    }

    /**
     * Get user growth data for charts.
     */
    public function getUserGrowthData(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $userGrowth = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = [];
        $counts = [];
        $runningTotal = User::where('created_at', '<', $startDate)->count();

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = $date;
            
            $dayCount = $userGrowth->where('date', $date)->first()?->count ?? 0;
            $runningTotal += $dayCount;
            $counts[] = $runningTotal;
        }

        return [
            'dates' => $dates,
            'counts' => $counts,
        ];
    }

    /**
     * Get attendance summary for dashboard.
     */
    public function getAttendanceSummary(): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today' => AttendanceLog::whereDate('datetime', $today)->count(),
            'this_week' => AttendanceLog::where('datetime', '>=', $thisWeek)->count(),
            'this_month' => AttendanceLog::where('datetime', '>=', $thisMonth)->count(),
            'unique_users_today' => AttendanceLog::whereDate('datetime', $today)
                ->distinct('pin')
                ->whereNotNull('pin')
                ->count(),
        ];
    }

    /**
     * Get system health status.
     */
    public function getSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'fingerprint_machines' => $this->checkFingerprintMachinesHealth(),
            'storage' => $this->checkStorageHealth(),
            'cache' => $this->checkCacheHealth(),
        ];
    }

    /**
     * Check database health.
     */
    private function checkDatabaseHealth(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'healthy',
                'message' => 'Database connection is working',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check fingerprint machines health.
     */
    private function checkFingerprintMachinesHealth(): array
    {
        $totalMachines = MesinFinger::count();
        $activeMachines = MesinFinger::where('status', 'active')->count();
        $errorMachines = MesinFinger::where('status', 'error')->count();

        $status = 'healthy';
        if ($errorMachines > 0) {
            $status = $errorMachines >= $totalMachines / 2 ? 'critical' : 'warning';
        }

        return [
            'status' => $status,
            'total' => $totalMachines,
            'active' => $activeMachines,
            'error' => $errorMachines,
            'message' => "{$activeMachines}/{$totalMachines} machines are active",
        ];
    }

    /**
     * Check storage health.
     */
    private function checkStorageHealth(): array
    {
        try {
            $storagePath = storage_path();
            $freeBytes = disk_free_space($storagePath);
            $totalBytes = disk_total_space($storagePath);
            $usedPercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;

            $status = 'healthy';
            if ($usedPercent > 90) {
                $status = 'critical';
            } elseif ($usedPercent > 80) {
                $status = 'warning';
            }

            return [
                'status' => $status,
                'used_percent' => round($usedPercent, 2),
                'free_space' => $this->formatBytes($freeBytes),
                'total_space' => $this->formatBytes($totalBytes),
                'message' => "Storage is {$usedPercent}% full",
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Could not check storage: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache health.
     */
    private function checkCacheHealth(): array
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            if ($retrieved === 'test') {
                return [
                    'status' => 'healthy',
                    'message' => 'Cache is working properly',
                ];
            } else {
                return [
                    'status' => 'warning',
                    'message' => 'Cache test failed',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Clear all superadmin caches.
     */
    public function clearCaches(): bool
    {
        try {
            Cache::forget('superadmin.dashboard.stats');
            Cache::tags(['superadmin'])->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get export data for users.
     */
    public function getUsersExportData(array $filters = []): array
    {
        $query = User::with(['roles']);

        // Apply filters
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        // if (!empty($filters['status'])) {
        //     $query->where('is_active', $filters['status'] === 'active');
        // } // Removed as is_active column doesn't exist

        if (!empty($filters['employee_type'])) {
            $query->where('employee_type', $filters['employee_type']);
        }

        return $query->get()->map(function ($user) {
            return [
                'ID' => $user->id,
                'Nama' => $user->name,
                'Email' => $user->email,
                'NIP' => $user->nip,
                'Tipe Karyawan' => $user->employee_type,
                'ID Karyawan' => $user->employee_id,
                'Status' => 'Aktif', // Since is_active column doesn't exist, assume all users are active
                'Peran' => $user->roles->pluck('display_name')->join(', '),
                'Fingerprint Enabled' => $user->fingerprint_enabled ? 'Ya' : 'Tidak',
                'Fingerprint PIN' => $user->fingerprint_pin,
                'Dibuat' => $user->created_at?->format('Y-m-d H:i:s'),
                'Diperbarui' => $user->updated_at?->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }
}
