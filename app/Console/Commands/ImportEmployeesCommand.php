<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportEmployeesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:import {file=employees_202509132152.sql : The SQL file to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import employee data from SQL file without modifying structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fileName = $this->argument('file');
        $filePath = dirname(base_path()).'/.qoder/file-pendukung/'.$fileName;

        if (! File::exists($filePath)) {
            $this->error('File not found: '.$filePath);

            return 1;
        }

        $this->info('Starting employee data import...');
        $this->info('File: '.$filePath);

        try {
            // Read the SQL file content
            $sqlContent = File::get($filePath);

            // Extract INSERT statements for employees table
            $pattern = '/INSERT INTO (?:slip_gaji\.)?employees \(([^)]+)\) VALUES\s*([^;]+);/s';
            preg_match_all($pattern, $sqlContent, $matches, PREG_SET_ORDER);

            if (empty($matches)) {
                $this->error('No INSERT statements found for employees table');

                return 1;
            }

            $this->info('Found '.count($matches).' INSERT statement(s)');

            DB::beginTransaction();

            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Clear existing data
            $this->info('Clearing existing employee data...');
            Employee::truncate();

            $totalInserted = 0;

            foreach ($matches as $match) {
                $columns = $match[1];
                $values = $match[2];

                // Clean up the data to handle enum fields properly
                $values = str_replace(",'',", ',NULL,', $values); // Replace empty strings with NULL
                $values = str_replace("''", 'NULL', $values); // Replace quoted empty strings with NULL

                // Execute the INSERT statement directly
                $sql = "INSERT INTO employees ({$columns}) VALUES {$values}";
                $result = DB::statement($sql);

                if ($result) {
                    // Count the number of rows in this INSERT
                    $rowCount = substr_count($values, '),(') + 1;
                    $totalInserted += $rowCount;
                    $this->info("Inserted {$rowCount} records");
                }
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info("Successfully imported {$totalInserted} employee records");
            $this->info('Employee data import completed!');

            DB::commit();

            return 0;

        } catch (\Exception $e) {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            try {
                DB::rollBack();
            } catch (\Exception $rollbackException) {
                // Transaction might already be closed
            }

            $this->error('Import failed: '.$e->getMessage());
            $this->error('File: '.$e->getFile().' Line: '.$e->getLine());

            return 1;
        }
    }
}
