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

                $existing = Employee::query()
                    ->where('id_pegawai', $externalId)
                    ->first();

                $incomingNip = $mapped['nip'] ?? null;
                $duplicateNipRecord = $this->findDuplicateActiveNipRecord($incomingNip, $externalId);

                if ($duplicateNipRecord) {
                    $failed++;
                    $errors[] = [
                        'external_id' => $externalId,
                        'message' => sprintf(
                            'Duplicate active employee nip [%s] already linked to id_pegawai [%s]',
                            $incomingNip,
                            $duplicateNipRecord->id_pegawai
                        ),
                        'payload' => $row,
                    ];

                    continue;
                }

                if ($existing) {
                    $existing->fill($mapped);
                    $existing->save();

                    $updated++;
                    $processed++;

                    continue;
                }

                $trashed = Employee::onlyTrashed()
                    ->where('id_pegawai', $externalId)
                    ->orderByDesc('id')
                    ->first();

                if ($trashed) {
                    Log::info('Restored soft-deleted employee during sync', [
                        'id_pegawai' => $externalId,
                        'nip' => $mapped['nip'] ?? null,
                        'nama' => $mapped['nama'] ?? null,
                    ]);
                    $trashed->restore();
                    $trashed->fill($mapped);
                    $trashed->save();

                    $updated++;
                } else {
                    Employee::create($mapped);
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

    private function findDuplicateActiveNipRecord(?string $nip, string $externalId): ?Employee
    {
        if ($nip === null || $nip === '') {
            return null;
        }

        return Employee::query()
            ->where('nip', $nip)
            ->where(function ($query) use ($externalId) {
                $query->whereNull('id_pegawai')
                    ->orWhere('id_pegawai', '!=', $externalId);
            })
            ->orderByDesc('id')
            ->first();
    }
}
