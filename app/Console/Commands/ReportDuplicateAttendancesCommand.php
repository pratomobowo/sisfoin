<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReportDuplicateAttendancesCommand extends Command
{
    protected $signature = 'attendance:report-duplicates {--date= : Limit report to one date YYYY-MM-DD} {--user= : Limit report to one user ID}';

    protected $description = 'Report duplicate employee attendance rows by user_id and date before adding unique constraints';

    public function handle(): int
    {
        $date = $this->option('date');
        $userId = $this->option('user');

        $duplicateQuery = DB::table('employee_attendances')
            ->select('user_id', 'date', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id', 'date')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('date')
            ->orderBy('user_id');

        if ($date) {
            $duplicateQuery->whereDate('date', $date);
        }

        if ($userId) {
            $duplicateQuery->where('user_id', (int) $userId);
        }

        $duplicates = $duplicateQuery->get();

        $this->info('Duplicate attendance groups: '.$duplicates->count());

        if ($duplicates->isEmpty()) {
            return self::SUCCESS;
        }

        $rows = DB::table('employee_attendances as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->where(function ($query) use ($duplicates) {
                foreach ($duplicates as $duplicate) {
                    $query->orWhere(function ($subQuery) use ($duplicate) {
                        $subQuery->where('a.user_id', $duplicate->user_id)
                            ->whereDate('a.date', $duplicate->date);
                    });
                }
            })
            ->select('a.id', 'a.user_id', 'u.name', 'u.nip', 'a.date', 'a.check_in_time', 'a.check_out_time', 'a.status', 'a.total_hours', 'a.overtime_hours', 'a.notes', 'a.created_at')
            ->orderBy('a.date')
            ->orderBy('a.user_id')
            ->orderBy('a.id')
            ->get()
            ->map(fn ($row) => [
                'attendance_id' => (string) $row->id,
                'user_id' => (string) $row->user_id,
                'name' => (string) ($row->name ?? '-'),
                'nip' => (string) ($row->nip ?? '-'),
                'date' => (string) $row->date,
                'check_in' => (string) ($row->check_in_time ?? '-'),
                'check_out' => (string) ($row->check_out_time ?? '-'),
                'status' => (string) ($row->status ?? '-'),
                'hours' => (string) ($row->total_hours ?? '-'),
                'overtime' => (string) ($row->overtime_hours ?? '-'),
                'notes' => (string) ($row->notes ?? '-'),
                'created_at' => (string) $row->created_at,
            ])
            ->all();

        $this->table(['Attendance ID', 'User ID', 'Name', 'NIP', 'Date', 'Check In', 'Check Out', 'Status', 'Hours', 'Overtime', 'Notes', 'Created'], $rows);
        $this->warn('Resolve these duplicates before running the unique index migration. No rows were changed.');

        return self::FAILURE;
    }
}
