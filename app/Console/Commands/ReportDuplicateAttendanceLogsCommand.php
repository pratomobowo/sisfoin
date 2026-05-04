<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReportDuplicateAttendanceLogsCommand extends Command
{
    protected $signature = 'attendance-logs:report-duplicates {--type=all : adms|pin_datetime|all}';

    protected $description = 'Report duplicate raw attendance logs before adding idempotency indexes';

    public function handle(): int
    {
        $type = (string) $this->option('type');
        if (! in_array($type, ['adms', 'pin_datetime', 'all'], true)) {
            $this->error('Invalid --type. Allowed: adms, pin_datetime, all');

            return self::FAILURE;
        }

        $hasDuplicates = false;

        if (in_array($type, ['adms', 'all'], true)) {
            $hasDuplicates = $this->reportAdmsDuplicates() || $hasDuplicates;
        }

        if (in_array($type, ['pin_datetime', 'all'], true)) {
            $hasDuplicates = $this->reportPinDatetimeDuplicates() || $hasDuplicates;
        }

        return $hasDuplicates ? self::FAILURE : self::SUCCESS;
    }

    private function reportAdmsDuplicates(): bool
    {
        $duplicates = DB::table('attendance_logs')
            ->select('adms_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('adms_id')
            ->groupBy('adms_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $this->info('Duplicate ADMS ID groups: '.$duplicates->count());

        if ($duplicates->isEmpty()) {
            return false;
        }

        $rows = DB::table('attendance_logs')
            ->whereIn('adms_id', $duplicates->pluck('adms_id'))
            ->select('id', 'adms_id', 'pin', 'user_id', 'datetime', 'status', 'verify', 'mesin_finger_id', 'processed_at', 'created_at')
            ->orderBy('adms_id')
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => $this->formatRow($row))
            ->all();

        $this->table(['ID', 'ADMS ID', 'PIN', 'User ID', 'Datetime', 'Status', 'Verify', 'Machine', 'Processed', 'Created'], $rows);

        return true;
    }

    private function reportPinDatetimeDuplicates(): bool
    {
        $duplicates = DB::table('attendance_logs')
            ->select('pin', 'datetime', DB::raw('COUNT(*) as total'))
            ->groupBy('pin', 'datetime')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $this->info('Duplicate PIN + datetime groups: '.$duplicates->count());

        if ($duplicates->isEmpty()) {
            return false;
        }

        $rows = DB::table('attendance_logs')
            ->where(function ($query) use ($duplicates) {
                foreach ($duplicates as $duplicate) {
                    $query->orWhere(function ($subQuery) use ($duplicate) {
                        $subQuery->where('pin', $duplicate->pin)
                            ->where('datetime', $duplicate->datetime);
                    });
                }
            })
            ->select('id', 'adms_id', 'pin', 'user_id', 'datetime', 'status', 'verify', 'mesin_finger_id', 'processed_at', 'created_at')
            ->orderBy('pin')
            ->orderBy('datetime')
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => $this->formatRow($row))
            ->all();

        $this->table(['ID', 'ADMS ID', 'PIN', 'User ID', 'Datetime', 'Status', 'Verify', 'Machine', 'Processed', 'Created'], $rows);

        return true;
    }

    private function formatRow(object $row): array
    {
        return [
            'id' => (string) $row->id,
            'adms_id' => (string) ($row->adms_id ?? '-'),
            'pin' => (string) $row->pin,
            'user_id' => (string) ($row->user_id ?? '-'),
            'datetime' => (string) $row->datetime,
            'status' => (string) $row->status,
            'verify' => (string) $row->verify,
            'machine' => (string) $row->mesin_finger_id,
            'processed' => (string) ($row->processed_at ?? '-'),
            'created' => (string) $row->created_at,
        ];
    }
}
