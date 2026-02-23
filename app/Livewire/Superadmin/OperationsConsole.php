<?php

namespace App\Livewire\Superadmin;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class OperationsConsole extends Component
{
    public string $selectedCommand = 'users:relink-employee-links';

    public string $confirmationText = '';

    public array $commandOptions = [
        'relink_type' => 'all',
        'relink_fill_nip' => true,
        'relink_dry_run' => true,
        'attendance_force' => true,
        'attendance_date_from' => '',
        'attendance_date_to' => '',
        'attendance_user_id' => '',
        'users_import_force' => false,
        'sevima_sync_confirm' => false,
    ];

    public array $lastRun = [
        'status' => '',
        'command' => '',
        'output' => '',
        'exit_code' => null,
        'run_at' => '',
    ];

    public function runCommand(): void
    {
        $this->validate([
            'selectedCommand' => 'required|in:users:relink-employee-links,attendance:process,optimize:clear,users:import,sevima:sync-test',
            'confirmationText' => 'required|in:RUN',
            'commandOptions.relink_type' => 'required|in:all,employee,dosen',
            'commandOptions.attendance_date_from' => 'nullable|date',
            'commandOptions.attendance_date_to' => 'nullable|date',
            'commandOptions.attendance_user_id' => 'nullable|integer',
        ]);

        if ($this->selectedCommand === 'sevima:sync-test' && ! $this->commandOptions['sevima_sync_confirm']) {
            $this->addError('commandOptions.sevima_sync_confirm', 'Centang konfirmasi untuk menjalankan sync Sevima.');

            return;
        }

        [$command, $arguments] = $this->buildCommandAndArguments();

        try {
            $exitCode = Artisan::call($command, $arguments);
            $output = trim((string) Artisan::output());
            $status = $exitCode === 0 ? 'success' : 'error';

            $this->lastRun = [
                'status' => $status,
                'command' => $command,
                'output' => $output,
                'exit_code' => $exitCode,
                'run_at' => now()->format('Y-m-d H:i:s'),
            ];

            activity('admin_operations')
                ->causedBy(Auth::user())
                ->withProperties([
                    'command' => $command,
                    'arguments' => $arguments,
                    'exit_code' => $exitCode,
                    'status' => $status,
                ])
                ->log('Operations console command executed');

            session()->flash($status, $status === 'success' ? 'Command berhasil dijalankan.' : 'Command selesai dengan error.');
        } catch (\Throwable $e) {
            $this->lastRun = [
                'status' => 'error',
                'command' => $command,
                'output' => $e->getMessage(),
                'exit_code' => 1,
                'run_at' => now()->format('Y-m-d H:i:s'),
            ];

            session()->flash('error', 'Gagal menjalankan command: '.$e->getMessage());
        }

        $this->confirmationText = '';
    }

    public function render()
    {
        return view('livewire.superadmin.operations-console', [
            'commandGroups' => $this->getCommandGroups(),
        ]);
    }

    private function buildCommandAndArguments(): array
    {
        return match ($this->selectedCommand) {
            'users:relink-employee-links' => [
                'users:relink-employee-links',
                [
                    '--type' => $this->commandOptions['relink_type'],
                    '--fill-nip' => (bool) $this->commandOptions['relink_fill_nip'],
                    '--dry-run' => (bool) $this->commandOptions['relink_dry_run'],
                ],
            ],
            'attendance:process' => [
                'attendance:process',
                array_filter([
                    '--force' => (bool) $this->commandOptions['attendance_force'],
                    '--date-from' => $this->commandOptions['attendance_date_from'] ?: null,
                    '--date-to' => $this->commandOptions['attendance_date_to'] ?: null,
                    '--user-id' => $this->commandOptions['attendance_user_id'] ?: null,
                ], fn ($v) => $v !== null),
            ],
            'users:import' => [
                'users:import',
                ['--force' => (bool) $this->commandOptions['users_import_force']],
            ],
            'sevima:sync-test' => ['sevima:sync-test', []],
            default => ['optimize:clear', []],
        };
    }

    private function getCommandGroups(): array
    {
        return [
            'Absensi' => [
                'users:relink-employee-links',
                'attendance:process',
            ],
            'SDM Sync' => [
                'users:import',
                'sevima:sync-test',
            ],
            'Sistem' => [
                'optimize:clear',
            ],
        ];
    }
}
