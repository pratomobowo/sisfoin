<?php

namespace App\Console\Commands;

use App\Imports\SlipGajiImport;
use App\Models\SlipGajiDetail;
use App\Models\SlipGajiHeader;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class TestImportExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'excel:test-import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test import Excel file and show the results';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return 1;
        }

        $this->info("Testing import of: {$filePath}");
        $this->info('===========================================');

        // Count records before import
        $beforeCount = SlipGajiDetail::count();
        $this->info("Records before import: {$beforeCount}");

        try {
            // Try to find existing header or create a new one with unique periode
            $periode = date('Y-m');
            $header = SlipGajiHeader::where('periode', $periode)->first();

            if (! $header) {
                $header = SlipGajiHeader::create([
                    'periode' => $periode,
                    'file_original' => basename($filePath),
                    'uploaded_by' => 1, // Default user ID
                    'uploaded_at' => now(),
                ]);
                $this->info("Created new header with ID: {$header->id}");
            } else {
                $this->info("Using existing header with ID: {$header->id}");
            }

            // Import the file
            Excel::import(new SlipGajiImport($header->id), $filePath);

            // Count records after import
            $afterCount = SlipGajiDetail::count();
            $imported = $afterCount - $beforeCount;

            $this->info("Records after import: {$afterCount}");
            $this->info("New records imported: {$imported}");

            if ($imported > 0) {
                // Show the latest imported records
                $this->info("\nLatest imported records:");
                $this->info('========================');

                $latestRecords = SlipGajiDetail::latest()->take($imported)->get();

                foreach ($latestRecords as $record) {
                    $this->info("ID: {$record->id}");
                    $this->info("NIP: {$record->nip}");
                    $this->info("Nama: {$record->nama}");
                    $this->info('Gaji Pokok: '.number_format($record->gaji_pokok));
                    $this->info('Tunjangan Keluarga: '.number_format($record->tunjangan_keluarga));
                    $this->info('Tunjangan Golongan: '.number_format($record->tunjangan_golongan));
                    $this->info('Penerimaan Kotor: '.number_format($record->penerimaan_kotor));
                    $this->info('Pajak: '.number_format($record->pajak));
                    $this->info('Gaji Bersih: '.number_format($record->gaji_bersih));
                    $this->info('---');
                }
            }

            $this->info("\nImport completed successfully!");

        } catch (\Exception $e) {
            $this->error('Import failed: '.$e->getMessage());
            $this->error('Stack trace: '.$e->getTraceAsString());

            return 1;
        }

        return 0;
    }
}
