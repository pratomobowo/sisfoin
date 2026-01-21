<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use App\Models\User;
use App\Models\Employee\Attendance as EmployeeAttendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateAttendanceData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:migrate {--chunk=1000 : Jumlah data per batch} {--force : Force run without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Memindahkan data dari attendance_logs ke employee_attendances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda yakin ingin memindahkan data dari attendance_logs ke employee_attendances?')) {
                $this->info('Proses dibatalkan.');
                return 0;
            }
        }

        $chunkSize = $this->option('chunk');
        $totalRecords = AttendanceLog::count();
        
        $this->info("Total data yang akan diproses: {$totalRecords}");
        $this->info("Memproses dengan batch size: {$chunkSize}");

        $processed = 0;
        $success = 0;
        $failed = 0;

        // Progress bar
        $progressBar = $this->output->createProgressBar($totalRecords);
        $progressBar->start();

        AttendanceLog::orderBy('datetime')
            ->chunk($chunkSize, function ($logs) use (&$processed, &$success, &$failed, $progressBar) {
                foreach ($logs as $log) {
                    try {
                        // Cari user berdasarkan PIN
                        $user = User::where('fingerprint_pin', $log->pin)->first();
                        
                        if (!$user) {
                            $this->line("Skipped: No user found for PIN {$log->pin}");
                            $processed++;
                            $progressBar->advance();
                            continue;
                        }

                        // Cek apakah data sudah ada untuk user dan tanggal yang sama
                        $existingAttendance = EmployeeAttendance::where('user_id', $user->id)
                            ->where('date', $log->datetime->format('Y-m-d'))
                            ->first();

                        if ($existingAttendance) {
                            // Update data yang sudah ada
                            $this->updateExistingAttendance($existingAttendance, $log, $user);
                            $this->line("Updated: User ID {$user->id} (PIN {$log->pin}), Date {$log->datetime->format('Y-m-d')}");
                        } else {
                            // Buat data baru
                            $this->createNewAttendance($log, $user);
                            $this->line("Created: User ID {$user->id} (PIN {$log->pin}), Date {$log->datetime->format('Y-m-d')}");
                        }

                        $success++;
                    } catch (\Exception $e) {
                        $failed++;
                        Log::error("Gagal memindahkan data AttendanceLog ID {$log->id}: " . $e->getMessage());
                        $this->error("Gagal memindahkan data ID {$log->id}: " . $e->getMessage());
                    }

                    $processed++;
                    $progressBar->advance();
                }
            });

        $progressBar->finish();
        $this->line("\n");

        $this->info("Proses migrasi selesai!");
        $this->info("Total diproses: {$processed}");
        $this->info("Berhasil: {$success}");
        $this->info("Gagal: {$failed}");

        return 0;
    }

    /**
     * Update existing attendance record
     */
    private function updateExistingAttendance($existingAttendance, $log, $user)
    {
        // Jika belum ada check-in time, set dari log
        if (!$existingAttendance->check_in_time) {
            $existingAttendance->check_in_time = $log->datetime;
        }

        // Jika log ini adalah check-out (lebih sore dari check-in yang ada)
        if ($log->datetime->gt($existingAttendance->check_in_time)) {
            $existingAttendance->check_out_time = $log->datetime;
            
            // Hitung total hours hanya jika check-in dan check-out di hari yang sama
            if ($existingAttendance->check_in_time && $existingAttendance->check_out_time) {
                $checkInDate = $existingAttendance->check_in_time->format('Y-m-d');
                $checkOutDate = $existingAttendance->check_out_time->format('Y-m-d');
                
                if ($checkInDate === $checkOutDate) {
                    $totalMinutes = $existingAttendance->check_out_time->diffInMinutes($existingAttendance->check_in_time);
                    $existingAttendance->total_hours = round($totalMinutes / 60, 2);
                    
                    // Hitung overtime hours
                    $standardHours = 8;
                    $existingAttendance->overtime_hours = $existingAttendance->total_hours > $standardHours 
                        ? $existingAttendance->total_hours - $standardHours 
                        : 0;
                } else {
                    // Jika check-out di hari berikutnya, hitung hanya sampai midnight
                    $midnight = \Carbon\Carbon::parse($existingAttendance->check_in_time->format('Y-m-d') . ' 23:59:59');
                    $totalMinutes = $midnight->diffInMinutes($existingAttendance->check_in_time);
                    $existingAttendance->total_hours = round($totalMinutes / 60, 2);
                    $existingAttendance->overtime_hours = 0;
                }
            }
        }

        // Update status berdasarkan log terbaru
        $existingAttendance->status = $this->mapStatus($log->status);
        
        // Update notes jika ada
        $notes = $existingAttendance->notes ?: '';
        $logNotes = $log->raw_data['notes'] ?? null;
        if ($logNotes && !str_contains($notes, $logNotes)) {
            $existingAttendance->notes = $notes ? $notes . '; ' . $logNotes : $logNotes;
        }

        $existingAttendance->save();
    }

    /**
     * Create new attendance record
     */
    private function createNewAttendance($log, $user)
    {
        EmployeeAttendance::create([
            'user_id' => $user->id,
            'date' => $log->datetime->format('Y-m-d'),
            'check_in_time' => $log->datetime,
            'check_out_time' => null, // Akan diupdate jika ada check-out
            'break_start_time' => null,
            'break_end_time' => null,
            'total_hours' => 0,
            'overtime_hours' => 0,
            'status' => $this->mapStatus($log->status),
            'notes' => $log->raw_data['notes'] ?? null,
            'location_check_in' => null,
            'location_check_out' => null,
            'ip_address' => null,
            'device_info' => null,
            'created_by' => null, // System migration
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Map status dari integer ke string
     */
    private function mapStatus($status)
    {
        return match ($status) {
            0 => 'present',
            1 => 'late',
            2 => 'absent',
            default => 'present'
        };
    }
}
