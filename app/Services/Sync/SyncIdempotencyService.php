<?php

namespace App\Services\Sync;

class SyncIdempotencyService
{
    public function generate(string $mode, int|string|null $triggeredBy, string $source): string
    {
        $parts = [
            strtolower($mode),
            (string) ($triggeredBy ?? 'system'),
            strtolower($source),
            \now()->format('Y-m-d-H'),
        ];

        return hash('sha256', implode('|', $parts));
    }
}
