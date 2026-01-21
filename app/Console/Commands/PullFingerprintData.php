<?php

namespace App\Console\Commands;

use App\Models\MesinFinger;
use App\Services\FingerprintService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PullFingerprintData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fingerprint:pull-data 
                            {--date-from= : Tanggal mulai (YYYY-MM-DD)} 
                            {--date-to= : Tanggal akhir (YYYY-MM-DD)} 
                            {--employee-id= : Filter berdasarkan ID karyawan}
                            {--process : Otomatis proses ke employee_attendances}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tarik data absensi dari ADMS API';

    /**
     * Execute the console command.
     */
    public function handle(FingerprintService $fingerprintService)
    {
        $dateFrom = $this->option('date-from') ?: now()->format('Y-m-d');
        $dateTo = $this->option('date-to') ?: now()->format('Y-m-d');
        $employeeId = $this->option('employee-id');
        $autoProcess = $this->option('process');

        $this->info('=== TARIK DATA ADMS API ===');
        $this->info("Periode: {$dateFrom} s/d {$dateTo}");
        if ($employeeId) {
            $this->info("Employee ID: {$employeeId}");
        }
        $this->info('===========================');

        try {
            // Test koneksi ke API
            $this->info('1. Menguji koneksi ke ADMS API...');
            $connectionTest = $fingerprintService->testConnection();

            if (!$connectionTest['success']) {
                $this->error('Gagal terhubung ke API: ' . $connectionTest['message']);
                return 1;
            }

            $this->info('✓ Koneksi API berhasil!');

            // Ambil data attendance dari ADMS
            $this->info('2. Mengambil data absensi dari ADMS...');
            $result = $fingerprintService->getAttendanceLogs($dateFrom, $dateTo, $employeeId);

            if (!$result['success']) {
                $this->error('Gagal mengambil data: ' . $result['message']);
                return 1;
            }

            $attendanceData = $result['data'];
            $this->info('✓ Berhasil mengambil ' . count($attendanceData) . ' record absensi');

            if (empty($attendanceData)) {
                $this->info('Tidak ada data baru untuk periode tersebut.');
                return 0;
            }

            // Tampilkan sample data
            $this->info("\nSample Data (5 record pertama):");
            $headers = ['PIN', 'DateTime', 'Status', 'Device SN'];
            $rows = array_map(function($record) {
                return [
                    $record['pin'],
                    $record['datetime'],
                    $record['status'] == 0 ? 'In' : 'Out',
                    $record['device_sn'] ?? 'N/A'
                ];
            }, array_slice($attendanceData, 0, 5));
            $this->table($headers, $rows);

            // Simpan ke database
            $this->info("\n3. Menyimpan data ke database...");
            $saveResult = $fingerprintService->saveAttendanceLogsToDatabase($attendanceData, $autoProcess);
            
            if ($saveResult['success']) {
                $this->info("✓ " . $saveResult['message']);
            } else {
                $this->error("✗ Gagal menyimpan data: " . $saveResult['message']);
                return 1;
            }

            $this->info("\n✓ Proses tarik data ADMS selesai!");
            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Error saat tarik data ADMS: ' . $e->getMessage());
            return 1;
        }
    }
}
