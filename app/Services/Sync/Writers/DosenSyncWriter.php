<?php

namespace App\Services\Sync\Writers;

use App\Models\Dosen;
use App\Services\SevimaApiService;

class DosenSyncWriter
{
    public function __construct(private readonly SevimaApiService $sevimaApiService) {}

    /**
     * @param  array<int, array<string, mixed>>  $rawDosens
     * @return array<string, mixed>
     */
    public function sync(array $rawDosens): array
    {
        $inserted = 0;
        $updated = 0;
        $failed = 0;
        $processed = 0;
        $errors = [];

        foreach ($rawDosens as $row) {
            try {
                $mapped = $this->sevimaApiService->mapDosenToDosen($row);
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

                $existing = Dosen::query()->where('id_pegawai', $externalId)->first();

                Dosen::updateOrCreate(
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
            'fetched_count' => count($rawDosens),
            'processed_count' => $processed,
            'inserted_count' => $inserted,
            'updated_count' => $updated,
            'failed_count' => $failed,
            'errors' => $errors,
        ];
    }
}
