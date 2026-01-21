<?php

namespace App\Console\Commands;

use App\Imports\SlipGajiImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class AnalyzeExcel extends Command
{
    protected $signature = 'excel:analyze {file}';

    protected $description = 'Analyze Excel file structure and data';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return 1;
        }

        try {
            $this->info('=== EXCEL FILE ANALYSIS ===');
            $this->info("File: {$filePath}");

            // Read raw data
            $data = Excel::toArray(new SlipGajiImport(1), $filePath);

            if (empty($data) || empty($data[0])) {
                $this->error('No data found in Excel file');

                return 1;
            }

            $rows = $data[0];
            $this->info('Total rows: '.count($rows));

            if (count($rows) > 0) {
                $this->info('\n=== HEADERS ===');
                $headers = array_keys($rows[0]);
                foreach ($headers as $index => $header) {
                    $this->info(($index + 1).". {$header}");
                }

                $this->info('\n=== FIRST ROW DATA ===');
                foreach ($rows[0] as $key => $value) {
                    $this->info("{$key}: {$value}");
                }

                if (count($rows) > 1) {
                    $this->info('\n=== SECOND ROW DATA ===');
                    foreach ($rows[1] as $key => $value) {
                        $this->info("{$key}: {$value}");
                    }
                }

                $this->info('\n=== NUMERIC ANALYSIS ===');
                foreach ($headers as $header) {
                    $values = array_column($rows, $header);
                    $numericValues = array_filter($values, function ($val) {
                        return is_numeric($val) && $val != 0;
                    });

                    $totalCount = count($values);
                    $nonZeroCount = count($numericValues);
                    $this->info("{$header}: {$nonZeroCount}/{$totalCount} non-zero numeric values");

                    if ($nonZeroCount > 0) {
                        $sample = array_slice($numericValues, 0, 3);
                        $this->info('  Sample values: '.implode(', ', $sample));
                    }
                }
            }

        } catch (\Exception $e) {
            $this->error('Error analyzing Excel file: '.$e->getMessage());
            $this->error('Stack trace: '.$e->getTraceAsString());

            return 1;
        }

        return 0;
    }
}
