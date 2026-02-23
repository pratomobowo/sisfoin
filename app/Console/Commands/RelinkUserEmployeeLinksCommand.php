<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RelinkUserEmployeeLinksCommand extends Command
{
    protected $signature = 'users:relink-employee-links
        {--type=all : Target type: employee|dosen|all}
        {--id-pegawai=* : Filter by one or more master id_pegawai}
        {--fill-nip : Fill empty users.nip from active employee/dosen record}
        {--dry-run : Show what would change without writing data}';

    protected $description = 'Relink users.employee_id to active employee/dosen records by id_pegawai';

    public function handle(): int
    {
        $type = (string) $this->option('type');
        $dryRun = (bool) $this->option('dry-run');
        $fillNip = (bool) $this->option('fill-nip');
        $masterIds = collect((array) $this->option('id-pegawai'))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->values();

        if (! in_array($type, ['all', 'employee', 'dosen'], true)) {
            $this->error('Invalid --type. Allowed: all, employee, dosen');

            return self::FAILURE;
        }

        $targets = $type === 'all'
            ? [
                ['type' => 'employee', 'model' => Employee::class],
                ['type' => 'dosen', 'model' => Dosen::class],
            ]
            : [
                ['type' => $type, 'model' => $type === 'employee' ? Employee::class : Dosen::class],
            ];

        $this->info('Relink users.employee_id by id_pegawai');
        $this->line('Mode: '.($dryRun ? 'DRY RUN' : 'WRITE'));

        if ($masterIds->isNotEmpty()) {
            $this->line('Filter id_pegawai: '.$masterIds->implode(', '));
        }

        $total = [
            'records_checked' => 0,
            'users_relinked' => 0,
            'users_nip_filled' => 0,
            'users_nip_skipped_conflict' => 0,
        ];

        foreach ($targets as $target) {
            $result = $this->processType(
                userType: $target['type'],
                modelClass: $target['model'],
                masterIds: $masterIds,
                dryRun: $dryRun,
                fillNip: $fillNip,
            );

            $total['records_checked'] += $result['records_checked'];
            $total['users_relinked'] += $result['users_relinked'];
            $total['users_nip_filled'] += $result['users_nip_filled'];
            $total['users_nip_skipped_conflict'] += $result['users_nip_skipped_conflict'];

            $this->table(
                ['Type', 'Master Checked', 'Users Relinked', 'NIP Filled', 'NIP Conflict'],
                [[
                    $target['type'],
                    $result['records_checked'],
                    $result['users_relinked'],
                    $result['users_nip_filled'],
                    $result['users_nip_skipped_conflict'],
                ]]
            );
        }

        $this->newLine();
        $this->info('Done.');
        $this->line('Master Checked: '.$total['records_checked']);
        $this->line('Users Relinked: '.$total['users_relinked']);
        $this->line('Users NIP Filled: '.$total['users_nip_filled']);
        $this->line('Users NIP Conflict (skipped): '.$total['users_nip_skipped_conflict']);

        if ($dryRun) {
            $this->comment('No data changed because --dry-run is enabled.');
        }

        return self::SUCCESS;
    }

    private function processType(string $userType, string $modelClass, Collection $masterIds, bool $dryRun, bool $fillNip): array
    {
        $activeRecords = $modelClass::query()
            ->whereNotNull('id_pegawai')
            ->when($masterIds->isNotEmpty(), fn ($q) => $q->whereIn('id_pegawai', $masterIds))
            ->get(['id', 'id_pegawai', 'nip'])
            ->sortByDesc('id')
            ->unique('id_pegawai')
            ->values();

        if ($activeRecords->isEmpty()) {
            return [
                'records_checked' => 0,
                'users_relinked' => 0,
                'users_nip_filled' => 0,
                'users_nip_skipped_conflict' => 0,
            ];
        }

        $canonicalByMaster = $activeRecords->mapWithKeys(fn ($r) => [(string) $r->id_pegawai => $r]);

        $historicalRecords = $modelClass::withTrashed()
            ->whereIn('id_pegawai', $canonicalByMaster->keys())
            ->get(['id', 'id_pegawai']);

        $historicalIdsByMaster = $historicalRecords
            ->groupBy('id_pegawai')
            ->map(fn ($rows) => $rows->pluck('id')->unique()->values());

        $usersRelinked = 0;
        $usersNipFilled = 0;
        $usersNipSkippedConflict = 0;

        foreach ($canonicalByMaster as $masterId => $canonical) {
            $historicalIds = $historicalIdsByMaster->get($masterId, collect());

            if ($historicalIds->isEmpty()) {
                continue;
            }

            $query = User::query()
                ->where('employee_type', $userType)
                ->whereIn('employee_id', $historicalIds)
                ->where('employee_id', '!=', $canonical->id);

            $affected = $query->count();

            if ($affected > 0) {
                $this->line("[{$userType}] id_pegawai {$masterId}: relink {$affected} user(s) to {$canonical->id}");
            }

            if (! $dryRun && $affected > 0) {
                DB::transaction(function () use ($query, $canonical, $fillNip, &$usersRelinked, &$usersNipFilled, &$usersNipSkippedConflict) {
                    $query->chunkById(200, function ($users) use ($canonical, $fillNip, &$usersRelinked, &$usersNipFilled, &$usersNipSkippedConflict) {
                        foreach ($users as $user) {
                            $payload = ['employee_id' => $canonical->id];

                            if ($fillNip && empty($user->nip) && ! empty($canonical->nip)) {
                                $nipInUse = User::query()
                                    ->where('nip', $canonical->nip)
                                    ->where('id', '!=', $user->id)
                                    ->exists();

                                if ($nipInUse) {
                                    $usersNipSkippedConflict++;
                                    $this->warn("Skip fill NIP '{$canonical->nip}' for user {$user->id} ({$user->name}) because it is already used.");
                                } else {
                                    $payload['nip'] = $canonical->nip;
                                    $usersNipFilled++;
                                }
                            }

                            $user->update($payload);
                            $usersRelinked++;
                        }
                    });
                });
            }

            if ($dryRun) {
                $usersRelinked += $affected;

                if ($fillNip && ! empty($canonical->nip) && $affected > 0) {
                    $candidateUsers = User::query()
                        ->where('employee_type', $userType)
                        ->whereIn('employee_id', $historicalIds)
                        ->where('employee_id', '!=', $canonical->id)
                        ->whereNull('nip')
                        ->count();

                    $nipAlreadyUsed = User::query()
                        ->where('nip', $canonical->nip)
                        ->exists();

                    if ($nipAlreadyUsed) {
                        $usersNipSkippedConflict += $candidateUsers;
                    } else {
                        $usersNipFilled += $candidateUsers;
                    }
                }
            }
        }

        return [
            'records_checked' => $canonicalByMaster->count(),
            'users_relinked' => $usersRelinked,
            'users_nip_filled' => $usersNipFilled,
            'users_nip_skipped_conflict' => $usersNipSkippedConflict,
        ];
    }
}
