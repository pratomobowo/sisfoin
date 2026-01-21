<?php

namespace App\Console\Commands;

use App\Services\SevimaApiService;
use Illuminate\Console\Command;
use Exception;

class TestSevimaApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sevima:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Sevima API connection and data retrieval';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Sevima API Service...');

        try {
            // Create service instance
            $sevimaService = new SevimaApiService();

            // Test connection
            $this->info('Testing API connection...');
            $connectionTest = $sevimaService->testConnection();

            if ($connectionTest) {
                $this->info('✅ API Connection successful!');

                // Test pegawai endpoint
                $this->info("\nTesting /pegawai endpoint...");
                $pegawaiData = $sevimaService->getPegawai();
                $this->info('✅ Pegawai data retrieved successfully!');
                $this->info('Total pegawai records: ' . count($pegawaiData));

                // Show first record structure
                if (count($pegawaiData) > 0) {
                    $this->info("\nFirst pegawai record structure:");
                    $firstRecord = $pegawaiData[0];
                    $this->info('Keys: ' . implode(', ', array_keys($firstRecord)));
                    $this->info('Sample data: ' . json_encode($firstRecord, JSON_PRETTY_PRINT));
                }

                // Test dosen endpoint
                $this->info("\nTesting /dosen endpoint...");
                $dosenData = $sevimaService->getDosen();
                $this->info('✅ Dosen data retrieved successfully!');
                $this->info('Total dosen records: ' . count($dosenData));

                // Show full response structure for dosen
                $this->info("\nFull dosen response structure:");
                $this->info('Type: ' . gettype($dosenData));
                if (is_array($dosenData)) {
                    $this->info('Keys: ' . implode(', ', array_keys($dosenData)));
                    if (isset($dosenData['data'])) {
                        $this->info('Data array count: ' . count($dosenData['data']));
                        if (count($dosenData['data']) > 0) {
                            $this->info('First dosen record: ' . json_encode($dosenData['data'][0], JSON_PRETTY_PRINT));
                        }
                    } else {
                        $this->info('Full response: ' . json_encode($dosenData, JSON_PRETTY_PRINT));
                    }
                } else {
                    $this->info('Response: ' . $dosenData);
                }

                $this->info("\n🎉 All tests passed! Sevima API integration is working correctly.");

            } else {
                $this->error('❌ API Connection failed!');
            }

        } catch (Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}