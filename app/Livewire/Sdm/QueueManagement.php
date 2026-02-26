<?php

namespace App\Livewire\Sdm;

use App\Livewire\Concerns\InteractsWithToast;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class QueueManagement extends Component
{
    use InteractsWithToast;

    public $isRunning = false;

    public $pids = [];

    public $lastUpdate = '';

    public function mount()
    {
        $this->checkStatus();
    }

    public function checkStatus()
    {
        if (! $this->isShellExecutionAvailable()) {
            $this->isRunning = false;
            $this->pids = [];
            $this->lastUpdate = now()->format('H:i:s');

            return;
        }

        $artisan = base_path('artisan');
        $command = 'ps -eo pid=,command= | grep '.escapeshellarg($artisan)." | grep 'queue:work' | grep -v grep";
        $output = [];
        \exec($command, $output);

        $this->isRunning = ! empty($output);
        $this->pids = [];

        if ($this->isRunning) {
            foreach ($output as $line) {
                if (preg_match('/^\s*(\d+)\s+/', $line, $matches)) {
                    $this->pids[] = $matches[1];
                }
            }
        }

        $this->lastUpdate = now()->format('H:i:s');
    }

    public function startQueue()
    {
        try {
            if (! $this->isShellExecutionAvailable()) {
                $this->toastError('Server PHP menonaktifkan fungsi shell (exec). Jalankan worker via aaPanel Process Manager/Supervisor.');

                return;
            }

            // Path to artisan
            $artisan = base_path('artisan');
            $php = $this->resolvePhpBinary();
            $logPath = storage_path('logs/queue-worker.log');

            if (! file_exists($artisan)) {
                $this->toastError('File artisan tidak ditemukan di server.');

                return;
            }

            $command = sprintf(
                'nohup %s %s queue:work --queue=emails,default --tries=3 --timeout=300 --sleep=3 >> %s 2>&1 & echo $!',
                escapeshellarg($php),
                escapeshellarg($artisan),
                escapeshellarg($logPath)
            );

            Log::info('Starting queue worker: '.$command);

            $pid_output = [];
            $exitCode = 0;
            \exec($command, $pid_output, $exitCode);
            $pid = ! empty($pid_output) ? trim($pid_output[0]) : '';

            if ($exitCode !== 0 || $pid === '' || ! ctype_digit($pid)) {
                $failureDetails = $this->readQueueWorkerLogTail();

                Log::error('Queue worker start command failed', [
                    'command' => $command,
                    'exit_code' => $exitCode,
                    'output' => $pid_output,
                    'queue_worker_log_tail' => $failureDetails,
                ]);

                $this->toastError($this->buildQueueStartFailureMessage('Gagal menjalankan queue worker.', $failureDetails));

                return;
            }

            sleep(1); // Give it a second to start
            $this->checkStatus();

            if ($this->isWorkerPidRunning((int) $pid)) {
                $this->toastSuccess('Email queue worker berhasil dijalankan (PID: '.$pid.')');
            } else {
                $failureDetails = $this->readQueueWorkerLogTail();
                $this->toastError($this->buildQueueStartFailureMessage('Worker tidak bertahan setelah start.', $failureDetails));
            }
        } catch (\Exception $e) {
            Log::error('Failed to start queue: '.$e->getMessage());
            $this->toastError('Gagal menjalankan queue: '.$e->getMessage());
        }
    }

    public function stopQueue()
    {
        try {
            if (! $this->isShellExecutionAvailable()) {
                $this->toastError('Server PHP menonaktifkan fungsi shell (exec). Hentikan worker via aaPanel Process Manager/Supervisor.');

                return;
            }

            $this->checkStatus();

            if (! $this->isRunning) {
                $this->toastWarning('Queue worker tidak sedang berjalan.');

                return;
            }

            if (count($this->pids) > 1) {
                $this->toastWarning('Terdeteksi lebih dari satu queue worker aktif. Hentikan worker melalui server agar tidak mengganggu proses lain.');

                return;
            }

            foreach ($this->pids as $pid) {
                $command = 'kill '.(int) $pid;
                \exec($command, $out, $code);
                Log::info('Stopped queue worker PID: '.$pid);
            }

            sleep(1);
            $this->checkStatus();

            $this->toastSuccess('Email queue worker berhasil dihentikan.');
        } catch (\Exception $e) {
            Log::error('Failed to stop queue: '.$e->getMessage());
            $this->toastError('Gagal menghentikan queue: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sdm.queue-management');
    }

    private function isShellExecutionAvailable(): bool
    {
        $disabled = array_filter(array_map('trim', explode(',', (string) ini_get('disable_functions'))));

        return function_exists('exec') && ! in_array('exec', $disabled, true);
    }

    private function resolvePhpBinary(): string
    {
        $candidates = [
            PHP_BINARY,
            PHP_BINDIR.'/php',
            '/usr/bin/php',
            '/usr/local/bin/php',
            '/opt/alt/php83/usr/bin/php',
            '/opt/alt/php82/usr/bin/php',
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && $this->isCliPhpBinary($candidate)) {
                return $candidate;
            }
        }

        return 'php';
    }

    private function isCliPhpBinary(string $binary): bool
    {
        if ($binary === '') {
            return false;
        }

        if ($binary !== 'php' && ! is_executable($binary)) {
            return false;
        }

        $output = [];
        $exitCode = 0;
        \exec(escapeshellarg($binary).' -v 2>&1', $output, $exitCode);

        if ($exitCode !== 0 || empty($output)) {
            return false;
        }

        $banner = strtolower(implode(' ', $output));

        return str_contains($banner, 'php') && ! str_contains($banner, 'php-fpm');
    }

    private function isWorkerPidRunning(int $pid): bool
    {
        if ($pid <= 0 || ! $this->isShellExecutionAvailable()) {
            return false;
        }

        $output = [];
        \exec('ps -p '.(int) $pid.' -o pid=', $output);

        return ! empty($output) && trim($output[0]) !== '';
    }

    private function readQueueWorkerLogTail(): ?string
    {
        $logPath = storage_path('logs/queue-worker.log');

        if (! is_file($logPath) || ! is_readable($logPath)) {
            return null;
        }

        $content = @file_get_contents($logPath);
        if ($content === false || trim($content) === '') {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        $tail = array_slice(array_filter($lines, fn ($line) => trim((string) $line) !== ''), -3);

        if (empty($tail)) {
            return null;
        }

        return trim(implode(' | ', array_map(fn ($line) => mb_substr($line, 0, 180), $tail)));
    }

    private function buildQueueStartFailureMessage(string $prefix, ?string $details): string
    {
        if ($details) {
            return $prefix.' Detail: '.$details;
        }

        return $prefix.' Cek storage/logs/queue-worker.log di server.';
    }
}
