<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditDuplicateLinkedUsersCommand extends Command
{
    protected $signature = 'users:audit-duplicates-linked {--type=all : employee|dosen|all}';

    protected $description = 'Audit duplicate users linked to the same employee_id/employee_type';

    public function handle(): int
    {
        $type = (string) $this->option('type');
        if (! in_array($type, ['all', 'employee', 'dosen'], true)) {
            $this->error('Invalid --type. Allowed: all, employee, dosen');

            return self::FAILURE;
        }

        $types = $type === 'all' ? ['employee', 'dosen'] : [$type];

        foreach ($types as $t) {
            $this->line('');
            $this->info('Audit duplicate linked users: '.$t);

            $duplicateIds = DB::table('users')
                ->where('employee_type', $t)
                ->whereNotNull('employee_id')
                ->selectRaw('employee_id, COUNT(*) as total')
                ->groupBy('employee_id')
                ->havingRaw('COUNT(*) > 1')
                ->orderBy('employee_id')
                ->pluck('employee_id');

            $this->line('Duplicate groups: '.$duplicateIds->count());

            if ($duplicateIds->isEmpty()) {
                continue;
            }

            $rows = DB::table('users')
                ->select('id', 'name', 'email', 'nip', 'employee_type', 'employee_id', 'created_at')
                ->where('employee_type', $t)
                ->whereIn('employee_id', $duplicateIds)
                ->orderBy('employee_id')
                ->orderBy('id')
                ->get();

            $attendanceCounts = DB::table('employee_attendances')
                ->select('user_id', DB::raw('COUNT(*) as total_attendance'))
                ->whereIn('user_id', $rows->pluck('id'))
                ->groupBy('user_id')
                ->pluck('total_attendance', 'user_id');

            $report = [];
            foreach ($rows->groupBy('employee_id') as $employeeId => $users) {
                foreach ($users as $user) {
                    $report[] = [
                        'type' => $t,
                        'employee_id' => (string) $employeeId,
                        'user_id' => (string) $user->id,
                        'name' => $user->name,
                        'nip' => $user->nip ?? '-',
                        'attendance' => (string) ($attendanceCounts[$user->id] ?? 0),
                        'created_at' => (string) $user->created_at,
                    ];
                }
            }

            $this->table(['Type', 'Ref ID', 'User ID', 'Name', 'NIP', 'Attendance', 'Created'], $report);
        }

        $this->line('');
        $this->info('Done auditing duplicate linked users.');

        return self::SUCCESS;
    }
}
