<?php

namespace App\Services\Sync\Writers;

use App\Models\Dosen;
use App\Services\SevimaApiService;
use Illuminate\Support\Facades\Log;

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

                $existing = Dosen::query()
                    ->where('id_pegawai', $externalId)
                    ->first();

                $incomingNip = $mapped['nip'] ?? null;
                $duplicateNipRecord = $this->findDuplicateActiveNipRecord($incomingNip, $externalId);

                if ($duplicateNipRecord) {
                    $failed++;
                    $errors[] = [
                        'external_id' => $externalId,
                        'message' => sprintf(
                            'Duplicate active dosen nip [%s] already linked to id_pegawai [%s]',
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

                $trashed = Dosen::onlyTrashed()
                    ->where('id_pegawai', $externalId)
                    ->orderByDesc('id')
                    ->first();

                if ($trashed) {
                    Log::info('Restored soft-deleted dosen during sync', [
                        'id_pegawai' => $externalId,
                        'nip' => $mapped['nip'] ?? null,
                        'nama' => $mapped['nama'] ?? null,
                    ]);
                    $trashed->restore();
                    $trashed->fill($mapped);
                    $trashed->save();

                    $updated++;
                } else {
                    Dosen::create($mapped);
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

    private function findDuplicateActiveNipRecord(?string $nip, string $externalId): ?Dosen
    {
        if ($nip === null || $nip === '') {
            return null;
        }

        return Dosen::query()
            ->where('nip', $nip)
            ->where(function ($query) use ($externalId) {
                $query->whereNull('id_pegawai')
                    ->orWhere('id_pegawai', '!=', $externalId);
            })
            ->orderByDesc('id')
            ->first();
    }
}
