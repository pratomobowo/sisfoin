<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResolveDuplicateLinkedUsersCommand extends Command
{
    protected $signature = 'users:resolve-duplicate-linked
        {--type=all : employee|dosen|all}
        {--apply : Apply changes (default is dry-run)}';

    protected $description = 'Resolve duplicate users linked to same employee_id+employee_type by detaching shadow accounts';

    public function handle(): int
    {
        $type = (string) $this->option('type');
        $apply = (bool) $this->option('apply');

        if (! in_array($type, ['all', 'employee', 'dosen'], true)) {
            $this->error('Invalid --type. Allowed: all, employee, dosen');

            return self::FAILURE;
        }

        $types = $type === 'all' ? ['employee', 'dosen'] : [$type];

        $totalGroups = 0;
        $totalDetached = 0;

        $this->info('Resolve duplicate linked users');
        $this->line('Mode: '.($apply ? 'APPLY' : 'DRY RUN'));

        foreach ($types as $userType) {
            $duplicateRefIds = DB::table('users')
                ->where('employee_type', $userType)
                ->whereNotNull('employee_id')
                ->selectRaw('employee_id, COUNT(*) as total')
                ->groupBy('employee_id')
                ->havingRaw('COUNT(*) > 1')
                ->orderBy('employee_id')
                ->pluck('employee_id');

            if ($duplicateRefIds->isEmpty()) {
                $this->line("[{$userType}] no duplicate groups.");

                continue;
            }

            $users = DB::table('users')
                ->select('id', 'name', 'email', 'nip', 'employee_type', 'employee_id', 'fingerprint_pin', 'fingerprint_enabled', 'created_at')
                ->where('employee_type', $userType)
                ->whereIn('employee_id', $duplicateRefIds)
                ->orderBy('employee_id')
                ->orderBy('id')
                ->get()
                ->groupBy('employee_id');

            $attendanceCounts = DB::table('employee_attendances')
                ->select('user_id', DB::raw('COUNT(*) as total'))
                ->whereIn('user_id', $users->flatten()->pluck('id'))
                ->groupBy('user_id')
                ->pluck('total', 'user_id');

            foreach ($users as $refId => $group) {
                $totalGroups++;
                [$canonical, $shadows] = $this->pickCanonicalAndShadows($group, $attendanceCounts);

                $this->line("[{$userType}] ref {$refId} canonical user {$canonical->id} ({$canonical->name}) shadows: ".$shadows->pluck('id')->implode(','));

                if (! $apply) {
                    $totalDetached += $shadows->count();

                    continue;
                }

                DB::transaction(function () use ($canonical, $shadows, &$totalDetached) {
                    foreach ($shadows as $shadow) {
                        $payload = [
                            'employee_type' => null,
                            'employee_id' => null,
                            'updated_at' => now(),
                        ];

                        // Preserve fingerprint access on canonical account if shadow has it and canonical does not
                        if (! $canonical->fingerprint_enabled && $shadow->fingerprint_enabled) {
                            DB::table('users')->where('id', $canonical->id)->update([
                                'fingerprint_enabled' => (bool) $shadow->fingerprint_enabled,
                                'fingerprint_pin' => $shadow->fingerprint_pin,
                                'updated_at' => now(),
                            ]);
                            $canonical->fingerprint_enabled = $shadow->fingerprint_enabled;
                            $canonical->fingerprint_pin = $shadow->fingerprint_pin;
                        }

                        DB::table('users')->where('id', $shadow->id)->update($payload);
                        $totalDetached++;
                    }
                });
            }
        }

        $this->newLine();
        $this->info('Summary');
        $this->line('Groups processed: '.$totalGroups);
        $this->line(($apply ? 'Users detached: ' : 'Users that would be detached: ').$totalDetached);

        return self::SUCCESS;
    }

    private function pickCanonicalAndShadows(Collection $group, Collection $attendanceCounts): array
    {
        $scored = $group->map(function ($user) use ($attendanceCounts) {
            $attendance = (int) ($attendanceCounts[$user->id] ?? 0);
            $score = 0;

            if ($attendance > 0) {
                $score += 100000 + $attendance;
            }

            if (! empty($user->nip)) {
                $score += 1000;
            }

            if (! empty($user->email)) {
                $score += 100;
            }

            // prefer older stable account in tie
            $timestamp = strtotime((string) $user->created_at) ?: 0;
            $score += max(0, 9999999999 - $timestamp);

            return [
                'user' => $user,
                'score' => $score,
            ];
        })->sortByDesc('score')->values();

        $canonical = $scored->first()['user'];
        $shadowIds = $scored->skip(1)->pluck('user.id');
        $shadows = $group->whereIn('id', $shadowIds)->values();

        return [$canonical, $shadows];
    }
}
