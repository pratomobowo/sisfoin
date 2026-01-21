<?php

namespace App\Console\Commands;

use App\Services\SevimaApiService;
use App\Models\Employee;
use App\Models\Dosen;
use App\Traits\SevimaDataMappingTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TestSevimaSync extends Command
{
    use SevimaDataMappingTrait;

    /**
     * The name and signature of console command.
     *
     * @var string
     */
    protected $signature = 'sevima:sync-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test complete Sevima sync functionality';

    /**
     * Execute console command.
     */
    public function handle()
    {
        $this->info('Testing complete Sevima sync functionality...');

        try {
            $startTime = microtime(true);
            $sevimaService = new SevimaApiService();

            // Test connection first
            $this->info('Testing API connection...');
            if (!$sevimaService->testConnection()) {
                throw new Exception('Gagal terhubung ke API Sevima. Periksa koneksi internet dan konfigurasi API.');
            }
            $this->info('API Connection successful!');

            // Sync pegawai data
            $this->info("\nSyncing pegawai data...");
            $pegawaiResult = $this->syncPegawaiData($sevimaService);
            $this->info("Pegawai sync completed: " . $pegawaiResult['total_inserted'] . " records inserted");

            // Sync dosen data
            $this->info("\nSyncing dosen data...");
            try {
                $dosenResult = $this->syncDosenData($sevimaService);
                $this->info("Dosen sync completed: " . $dosenResult['total_inserted'] . " records inserted");
            } catch (Exception $e) {
                $this->warn("Dosen sync failed: " . $e->getMessage());
                $dosenResult = [
                    'total_api' => 0,
                    'total_processed' => 0,
                    'total_inserted' => 0,
                    'total_errors' => 1,
                    'errors' => ['Dosen sync failed: ' . $e->getMessage()],
                    'duration' => 0,
                ];
            }

            // Generate summary
            $summary = $this->generateSyncSummary($pegawaiResult, $dosenResult);
            $summary['duration'] = round(microtime(true) - $startTime, 2);
            
            $this->logSyncResults($summary);

            // Display results
            $this->info("\nSync completed successfully!");
            $this->info("Total duration: " . $summary['duration'] . " seconds");
            $this->info("Pegawai: " . $summary['pegawai']['total_inserted'] . " inserted");
            $this->info("Dosen: " . $summary['dosen']['total_inserted'] . " inserted");
            
            if ($summary['pegawai']['total_errors'] > 0 || $summary['dosen']['total_errors'] > 0) {
                $this->warn("\nErrors encountered:");
                foreach ($summary['pegawai']['errors'] as $error) {
                    $this->warn("Pegawai: " . $error);
                }
                foreach ($summary['dosen']['errors'] as $error) {
                    $this->warn("Dosen: " . $error);
                }
            }

        } catch (Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            Log::error('Sevima sync test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Sync pegawai data from Sevima API
     */
    private function syncPegawaiData(SevimaApiService $sevimaService)
    {
        $startTime = microtime(true);
        
        try {
            // Fetch data from API
            $pegawaiData = $sevimaService->getPegawai();
            
            if (!is_array($pegawaiData)) {
                throw new Exception('Invalid pegawai data format received from API');
            }

            $totalApi = count($pegawaiData);
            $this->info("Found " . $totalApi . " pegawai records from API");
            
            // Process batch data
            $batchResult = $this->processPegawaiBatch($pegawaiData);
            $this->info("Processed " . $batchResult['total_processed'] . " pegawai records");
            
            // Truncate existing data and insert new data
            $inserted = 0;
            DB::transaction(function () use ($batchResult, $sevimaService, &$inserted) {
                // Truncate table
                $this->info('Truncating employees table...');
                Employee::query()->delete();
                
                // Insert new data
                foreach ($batchResult['processed'] as $pegawai) {
                    try {
                        $mappedData = $sevimaService->mapPegawaiToEmployee($pegawai);
                        Employee::create($mappedData);
                        $inserted++;
                    } catch (Exception $e) {
                        Log::error('Failed to insert pegawai data', [
                            'error' => $e->getMessage(),
                            'data' => $pegawai,
                        ]);
                        $batchResult['errors'][] = "Insert failed: " . $e->getMessage();
                    }
                }
                
                $this->info("Inserted " . $inserted . " pegawai records");
            });
            
            $batchResult['total_inserted'] = $inserted;

            $batchResult['total_api'] = $totalApi;
            $batchResult['duration'] = round(microtime(true) - $startTime, 2);

            return $batchResult;

        } catch (Exception $e) {
            Log::error('Pegawai sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Sync dosen data from Sevima API
     */
    private function syncDosenData(SevimaApiService $sevimaService)
    {
        $startTime = microtime(true);
        
        try {
            // Fetch data from API
            $dosenData = $sevimaService->getDosen();
            
            if (!is_array($dosenData)) {
                throw new Exception('Invalid dosen data format received from API');
            }

            $totalApi = count($dosenData);
            $this->info("Found " . $totalApi . " dosen records from API");
            
            // Process batch data
            $batchResult = $this->processDosenBatch($dosenData);
            $this->info("Processed " . $batchResult['total_processed'] . " dosen records");
            
            // Truncate existing data and insert new data
            $inserted = 0;
            DB::transaction(function () use ($batchResult, $sevimaService, &$inserted) {
                // Truncate table
                $this->info('Truncating dosens table...');
                Dosen::query()->delete();
                
                // Insert new data
                foreach ($batchResult['processed'] as $dosen) {
                    try {
                        $mappedData = $sevimaService->mapDosenToDosen($dosen);
                        Dosen::create($mappedData);
                        $inserted++;
                    } catch (Exception $e) {
                        $errorMsg = "Insert failed for dosen " . ($dosen['nama'] ?? 'unknown') . ": " . $e->getMessage();
                        Log::error('Failed to insert dosen data', [
                            'error' => $e->getMessage(),
                            'data' => $dosen,
                        ]);
                        $batchResult['errors'][] = $errorMsg;
                        $this->warn($errorMsg);
                    }
                }
                
                $this->info("Inserted " . $inserted . " dosen records");
            });
            
            $batchResult['total_inserted'] = $inserted;

            $batchResult['total_api'] = $totalApi;
            $batchResult['duration'] = round(microtime(true) - $startTime, 2);

            return $batchResult;

        } catch (Exception $e) {
            Log::error('Dosen sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}