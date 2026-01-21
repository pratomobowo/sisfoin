<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CheckExcelFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-excel-file {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Excel file headers';

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

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Get header row
            $headerRow = $worksheet->rangeToArray('A1:AH1')[0];

            $this->info('Excel file analysis:');
            $this->info('====================');
            $this->info('Total columns: '.count($headerRow));
            $this->info('');

            $this->info('Headers found:');
            $this->info('--------------');
            foreach ($headerRow as $index => $header) {
                $column = chr(65 + $index);
                $this->line("Column {$column} (index {$index}): '{$header}'");
            }

            $this->info('');
            $this->info('Header values with details:');
            $this->info('---------------------------');
            foreach ($headerRow as $index => $header) {
                $column = chr(65 + $index);
                $this->line("Column {$column}:");
                $this->line("  Value: '{$header}'");
                $this->line('  Length: '.strlen($header));
                $this->line('  Ord values: '.implode(', ', array_map('ord', str_split($header))));
                $this->line('');
            }

        } catch (\Exception $e) {
            $this->error('Error reading Excel file: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
