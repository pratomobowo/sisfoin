<?php

namespace App\Console\Commands;

use App\Services\SevimaApiService;
use App\Models\Dosen;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TestDosenSync extends Command
{
    /**
     * The name and signature of console command.
     *
     * @var string
     */
    protected $signature = 'sevima:dosen-sync-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test dosen sync functionality with detailed error reporting';

    /**
     * Execute console command.
     */
    public function handle()
    {
        $this->info('Testing dosen sync functionality...');

        try {
            $startTime = microtime(true);
            $sevimaService = new SevimaApiService();

            // Test dosen connection first
            $this->info('Testing dosen API connection...');
            $dosenData = $sevimaService->getDosen();
            
            if (!is_array($dosenData)) {
                throw new Exception('Invalid dosen data format received from API');
            }

            $totalApi = count($dosenData);
            $this->info("Found " . $totalApi . " dosen records from API");

            // Truncate existing data and insert new data
            $inserted = 0;
            $errors = [];
            
            DB::transaction(function () use ($dosenData, $sevimaService, &$inserted, &$errors) {
                // Truncate table
                $this->info('Truncating dosens table...');
                Dosen::query()->delete();
                
                // Insert new data
                foreach ($dosenData as $index => $dosen) {
                    try {
                        $mappedData = $sevimaService->mapDosenToDosen($dosen);
                        Dosen::create($mappedData);
                        $inserted++;
                        
                        if ($inserted % 50 == 0) {
                            $this->info("Inserted " . $inserted . " records...");
                        }
                    } catch (Exception $e) {
                        $errorMsg = "Insert failed for dosen #" . ($index + 1) . " " . ($dosen['nama'] ?? 'unknown') . " (ID: " . ($dosen['id_pegawai'] ?? 'unknown') . "): " . $e->getMessage();
                        Log::error('Failed to insert dosen data', [
                            'error' => $e->getMessage(),
                            'dosen_id' => $dosen['id_pegawai'] ?? null,
                            'dosen_name' => $dosen['nama'] ?? null,
                            'data' => $dosen,
                        ]);
                        $errors[] = $errorMsg;
                        $this->error($errorMsg);
                    }
                }
                
                $this->info("Inserted " . $inserted . " dosen records");
            });

            $duration = round(microtime(true) - $startTime, 2);

            // Display results
            $this->info("\nDosen sync completed!");
            $this->info("Total duration: " . $duration . " seconds");
            $this->info("Total API records: " . $totalApi);
            $this->info("Total inserted: " . $inserted);
            $this->info("Total errors: " . count($errors));
            
            if (count($errors) > 0) {
                $this->warn("\nFirst 10 errors encountered:");
                foreach (array_slice($errors, 0, 10) as $error) {
                    $this->warn("- " . $error);
                }
                
                if (count($errors) > 10) {
                    $this->warn("... and " . (count($errors) - 10) . " more errors");
                }
            }

        } catch (Exception $e) {
            $this->error('Dosen sync failed: ' . $e->getMessage());
            Log::error('Dosen sync test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }
}