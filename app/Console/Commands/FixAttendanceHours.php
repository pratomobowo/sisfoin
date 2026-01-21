<?php

namespace App\Console\Commands;

use App\Models\Employee\Attendance as EmployeeAttendance;
use Illuminate\Console\Command;

class FixAttendanceHours extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:fix-hours {--force : Force run without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Memperbaiki perhitungan total_hours di employee_attendances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda yakin ingin memperbaiki perhitungan jam kerja?')) {
                $this->info('Proses dibatalkan.');
                return 0;
            }
        }

        $attendances = EmployeeAttendance::where('total_hours', '<', 0)
            ->orWhere(function($query) {
                $query->whereNotNull('check_in_time')
                      ->whereNotNull('check_out_time')
                      ->where('total_hours', 0);
            })
            ->get();

        $total = $attendances->count();
        $fixed = 0;
        $failed = 0;

        if ($total === 0) {
            $this->info('Tidak ada data yang perlu diperbaiki.');
            return 0;
        }

        $this->info("Ditemukan {$total} data yang perlu diperbaiki.");

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        foreach ($attendances as $attendance) {
            try {
                $this->recalculateHours($attendance);
                $attendance->save();
                $fixed++;
                $this->line("Fixed: Attendance ID {$attendance->id}, User ID {$attendance->user_id}, Date {$attendance->date}");
            } catch (\Exception $e) {
                $failed++;
                $this->error("Failed to fix Attendance ID {$attendance->id}: " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line("\n");

        $this->info("Proses perbaikan selesai!");
        $this->info("Total diperbaiki: {$fixed}");
        $this->info("Gagal: {$failed}");

        return 0;
    }

    /**
     * Recalculate hours for attendance
     */
    private function recalculateHours($attendance)
    {
        if (!$attendance->check_in_time || !$attendance->check_out_time) {
            $attendance->total_hours = 0;
            $attendance->overtime_hours = 0;
            return;
        }

        $checkInDate = $attendance->check_in_time->format('Y-m-d');
        $checkOutDate = $attendance->check_out_time->format('Y-m-d');

        if ($checkInDate === $checkOutDate) {
            // Check-in dan check-out di hari yang sama
            $totalMinutes = $attendance->check_out_time->diffInMinutes($attendance->check_in_time);
            $attendance->total_hours = round($totalMinutes / 60, 2);
            
            // Hitung overtime hours
            $standardHours = 8;
            $attendance->overtime_hours = $attendance->total_hours > $standardHours 
                ? $attendance->total_hours - $standardHours 
                : 0;
        } else {
            // Check-out di hari berikutnya, hitung hanya sampai midnight
            $midnight = \Carbon\Carbon::parse($attendance->check_in_time->format('Y-m-d') . ' 23:59:59');
            $totalMinutes = $midnight->diffInMinutes($attendance->check_in_time);
            $attendance->total_hours = round($totalMinutes / 60, 2);
            $attendance->overtime_hours = 0;
        }

        // Pastikan total hours tidak negatif
        if ($attendance->total_hours < 0) {
            $attendance->total_hours = 0;
        }

        // Pastikan overtime hours tidak negatif
        if ($attendance->overtime_hours < 0) {
            $attendance->overtime_hours = 0;
        }
    }
}
