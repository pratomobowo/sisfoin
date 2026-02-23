<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
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
    public function handle(AttendanceService $service)
    {
        $this->info('Memulai proses konversi log absensi ke data absensi karyawan...');

        $dateFrom = $this->option('date-from');
        $dateTo = $this->option('date-to');
        $userId = $this->option('user-id');

        $force = (bool) $this->option('force');

        try {
            if ($userId) {
                $result = $service->processLogs($dateFrom, $dateTo, (int) $userId, $force);
                $this->info($result['message']);
            } else {
                $result = $service->processLogs($dateFrom, $dateTo, null, $force);
                $this->info($result['message']);
            }

            if (isset($result['processed_count'])) {
                $this->line('Processed: '.$result['processed_count']);
            }
            if (isset($result['error_count'])) {
                $this->line('Errors: '.$result['error_count']);
            }
            if (isset($result['execution_time'])) {
                $this->line('Execution time: '.$result['execution_time'].'s');
            }

            $this->info('Proses konversi selesai.');
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
