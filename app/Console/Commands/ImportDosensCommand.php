<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportDosensCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dosens:import {file} {--truncate : Truncate table before import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dosens data from SQL file';

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

        $this->info('Starting dosen data import...');

        if ($this->option('truncate')) {
            $this->info('Truncating dosens table...');
            DB::table('dosens')->truncate();
        }

        $sqlContent = file_get_contents($filePath);

        // Extract INSERT statements for dosens table
        $pattern = '/INSERT INTO (?:slip_gaji\.)?dosens \(([^)]+)\) VALUES\s*([^;]+);/s';
        preg_match_all($pattern, $sqlContent, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            $this->error('No dosen INSERT statements found in the SQL file.');

            return 1;
        }

        $this->info('Found '.count($matches).' INSERT statements.');

        $totalImported = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($matches as $match) {
                $columns = array_map('trim', explode(',', $match[1]));
                $valuesString = $match[2];

                // Parse multiple value sets
                $valuesSets = $this->parseValuesSets($valuesString);

                foreach ($valuesSets as $values) {
                    try {
                        $data = $this->mapColumnsToData($columns, $values);
                        $data = $this->transformData($data);

                        Dosen::create($data);
                        $totalImported++;

                        if ($totalImported % 10 == 0) {
                            $this->info("Imported {$totalImported} records...");
                        }

                    } catch (\Exception $e) {
                        $errors[] = 'Error importing record: '.$e->getMessage();
                        Log::error('Dosen import error', [
                            'data' => $data ?? null,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            DB::commit();

            $this->info("\nImport completed successfully!");
            $this->info("Total records imported: {$totalImported}");

            if (! empty($errors)) {
                $this->warn("\nErrors encountered:");
                foreach ($errors as $error) {
                    $this->warn($error);
                }
            }

        } catch (\Exception $e) {
            DB::rollback();
            $this->error('Import failed: '.$e->getMessage());

            return 1;
        }

        return 0;
    }

    private function parseValuesSets($valuesString)
    {
        $sets = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = null;
        $depth = 0;

        for ($i = 0; $i < strlen($valuesString); $i++) {
            $char = $valuesString[$i];

            if (($char === '"' || $char === "'") && ($i === 0 || $valuesString[$i - 1] !== '\\')) {
                if (! $inQuotes) {
                    $inQuotes = true;
                    $quoteChar = $char;
                } elseif ($char === $quoteChar) {
                    $inQuotes = false;
                    $quoteChar = null;
                }
            }

            if (! $inQuotes) {
                if ($char === '(') {
                    if ($depth === 0) {
                        $current = '';
                    } else {
                        $current .= $char;
                    }
                    $depth++;
                } elseif ($char === ')') {
                    $depth--;
                    if ($depth === 0) {
                        $sets[] = $this->parseValues($current);
                        $current = '';
                    } else {
                        $current .= $char;
                    }
                } else {
                    if ($depth > 0) {
                        $current .= $char;
                    }
                }
            } else {
                $current .= $char;
            }
        }

        return $sets;
    }

    private function parseValues($valuesString)
    {
        $values = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = null;

        for ($i = 0; $i < strlen($valuesString); $i++) {
            $char = $valuesString[$i];

            if (($char === '"' || $char === "'") && ($i === 0 || $valuesString[$i - 1] !== '\\')) {
                if (! $inQuotes) {
                    $inQuotes = true;
                    $quoteChar = $char;
                } elseif ($char === $quoteChar) {
                    $inQuotes = false;
                    $quoteChar = null;
                }
                $current .= $char;
            } elseif ($char === ',' && ! $inQuotes) {
                $values[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if ($current !== '') {
            $values[] = trim($current);
        }

        return $values;
    }

    private function mapColumnsToData($columns, $values)
    {
        $data = [];

        for ($i = 0; $i < count($columns) && $i < count($values); $i++) {
            $column = trim($columns[$i]);
            $value = trim($values[$i]);

            // Remove quotes from values
            if (($value[0] === "'" && $value[-1] === "'") ||
                ($value[0] === '"' && $value[-1] === '"')) {
                $value = substr($value, 1, -1);
            }

            // Handle NULL values
            if (strtoupper($value) === 'NULL') {
                $value = null;
            }

            $data[$column] = $value;
        }

        return $data;
    }

    private function transformData($data)
    {
        // Transform dates
        foreach (['tanggal_lahir', 'tanggal_masuk', 'tanggal_sertifikasi_dosen'] as $dateField) {
            if (isset($data[$dateField]) && $data[$dateField] && $data[$dateField] !== '') {
                try {
                    $data[$dateField] = date('Y-m-d', strtotime($data[$dateField]));
                } catch (\Exception $e) {
                    $data[$dateField] = null;
                }
            } else {
                $data[$dateField] = null;
            }
        }

        // Transform timestamps
        foreach (['api_created_at', 'api_updated_at', 'last_synced_at'] as $timestampField) {
            if (isset($data[$timestampField]) && $data[$timestampField] && $data[$timestampField] !== '') {
                try {
                    $data[$timestampField] = date('Y-m-d H:i:s', strtotime($data[$timestampField]));
                } catch (\Exception $e) {
                    $data[$timestampField] = null;
                }
            } else {
                $data[$timestampField] = null;
            }
        }

        // Transform boolean
        if (isset($data['is_deleted'])) {
            $data['is_deleted'] = (bool) $data['is_deleted'];
        }

        // Transform integers
        $intFields = [
            'id_agama', 'id_kecamatan_domisili', 'id_kota_domisili', 'id_provinsi_domisili',
            'id_kecamatan_ktp', 'id_kota_ktp', 'id_provinsi_ktp', 'id_satuan_kerja',
            'id_home_base', 'id_pendidikan_terakhir', 'id_sso',
        ];

        foreach ($intFields as $field) {
            if (isset($data[$field]) && $data[$field] !== '' && $data[$field] !== null) {
                $data[$field] = (int) $data[$field];
            } else {
                $data[$field] = null;
            }
        }

        // Handle empty strings for enum fields - convert to null
        $enumFields = ['status_aktif', 'status_kepegawaian', 'jenis_kelamin', 'status_nikah'];
        foreach ($enumFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }
}
