<?php

namespace App\Console\Commands;

use App\Services\AttendanceProcessingService;
use Illuminate\Console\Command;

class ProcessAttendanceLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:process 
                            {--date-from= : Date from to process attendance logs (format: Y-m-d)}
                            {--date-to= : Date to to process attendance logs (format: Y-m-d)}
                            {--user-id= : Specific user ID to process}
                            {--force : Force processing even if logs are already processed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process attendance logs and convert them to employee attendance records';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceProcessingService $service)
    {
        $this->info('Memulai proses konversi log absensi ke data absensi karyawan...');

        $dateFrom = $this->option('date-from');
        $dateTo = $this->option('date-to');
        $userId = $this->option('user-id');

        try {
            if ($userId) {
                $result = $service->processAttendanceLogsForUser($userId, $dateFrom, $dateTo);
                $this->info($result['message']);
            } else {
                $result = $service->processAttendanceLogs($dateFrom, $dateTo);
                $this->info($result['message']);
            }

            $this->info('Proses konversi selesai.');
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}