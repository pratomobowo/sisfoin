<?php

namespace App\Services\Sync\Writers;

use App\Models\Employee;
use App\Services\SevimaApiService;
use Illuminate\Support\Facades\Log;

class EmployeeSyncWriter
{
    public function __construct(private readonly SevimaApiService $sevimaApiService) {}

    /**
     * @param  array<int, array<string, mixed>>  $rawEmployees
     * @return array<string, mixed>
     */
    public function sync(array $rawEmployees): array
    {
        $inserted = 0;
        $updated = 0;
        $failed = 0;
        $processed = 0;
        $errors = [];

        foreach ($rawEmployees as $row) {
            try {
                $mapped = $this->sevimaApiService->mapPegawaiToEmployee($row);
                $externalId = $mapped['id_pegawai'] ?? $row['id_pegawai'] ?? null;

                if (! $externalId) {
                    $failed++;
                    $errors[] = [
                        'external_id' => null,
                        'message' => 'Missing id_pegawai, row skipped',
                        'payload' => $row,
                    ];

                    continue;
                }

                $existing = Employee::withTrashed()
                    ->where('id_pegawai', $externalId)
                    ->first();

                if ($existing && $existing->trashed()) {
                    Log::info('Restored soft-deleted employee during sync', [
                        'id_pegawai' => $externalId,
                        'nip' => $mapped['nip'] ?? null,
                        'nama' => $mapped['nama'] ?? null,
                    ]);
                    $existing->restore();
                }

                Employee::updateOrCreate(
                    ['id_pegawai' => $externalId],
                    $mapped
                );

                if ($existing) {
                    $updated++;
                } else {
                    $inserted++;
                }

                $processed++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = [
                    'external_id' => $row['id_pegawai'] ?? null,
                    'message' => $e->getMessage(),
                    'payload' => $row,
                ];
            }
        }

        return [
            'fetched_count' => count($rawEmployees),
            'processed_count' => $processed,
            'inserted_count' => $inserted,
            'updated_count' => $updated,
            'failed_count' => $failed,
            'errors' => $errors,
        ];
    }
}
