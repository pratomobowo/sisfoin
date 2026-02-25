<?php

namespace App\Services;

use App\Models\SystemService;
use App\Models\SystemServiceExecutionLog;
use Illuminate\Support\Facades\Artisan;

class SystemServiceRunner
{
    public function resolveServiceCommand(string $serviceKey): ?string
    {
        return match ($serviceKey) {
            'fingerprint_sync' => 'fingerprint:pull-data --process',
            'attendance_processor' => 'attendance:process',
            'email_queue' => 'queue:work --stop-when-empty --tries=1 --timeout=60',
            default => null,
        };
    }

    /**
     * @return array{success: bool, message: string, exit_code: int|null, output: string|null, log_id: int|null}
     */
    public function run(SystemService $service, string $triggeredBy = 'scheduler', ?int $triggeredByUserId = null): array
    {
        $command = $this->resolveServiceCommand($service->key);
        if (! $command) {
            return [
                'success' => false,
                'message' => "Service {$service->name} belum memiliki command otomatis.",
                'exit_code' => null,
                'output' => null,
                'log_id' => null,
            ];
        }

        $service->last_run_started_at = now();
        $service->status = 'running';
        $service->save();

        $executionLog = SystemServiceExecutionLog::create([
            'system_service_id' => $service->id,
            'service_key' => $service->key,
            'service_name' => $service->name,
            'command' => $command,
            'triggered_by' => $triggeredBy,
            'triggered_by_user_id' => $triggeredByUserId,
            'started_at' => now(),
            'status' => 'running',
        ]);

        try {
            $exitCode = Artisan::call($command);
            $output = trim(Artisan::output());

            $isSuccess = $exitCode === 0;
            $resultStatus = $isSuccess ? 'success' : 'error';
            $message = $isSuccess
                ? "Service {$service->name} berhasil dijalankan."
                : "Service {$service->name} gagal dijalankan.";

            $service->last_run_at = now();
            $service->last_run_finished_at = now();
            $service->last_run_result = $resultStatus;
            $service->last_run_message = $output !== '' ? mb_substr($output, 0, 1000) : null;
            $service->status = $isSuccess ? 'running' : 'error';
            $service->save();

            $executionLog->update([
                'finished_at' => now(),
                'status' => $resultStatus,
                'exit_code' => $exitCode,
                'message' => $message,
                'output' => $output !== '' ? mb_substr($output, 0, 10000) : null,
            ]);

            return [
                'success' => $isSuccess,
                'message' => $message,
                'exit_code' => $exitCode,
                'output' => $output !== '' ? mb_substr($output, 0, 1000) : null,
                'log_id' => $executionLog->id,
            ];
        } catch (\Throwable $e) {
            $service->last_run_finished_at = now();
            $service->last_run_result = 'error';
            $service->last_run_message = mb_substr($e->getMessage(), 0, 1000);
            $service->status = 'error';
            $service->save();

            $executionLog->update([
                'finished_at' => now(),
                'status' => 'error',
                'message' => mb_substr($e->getMessage(), 0, 1000),
                'output' => mb_substr($e->getTraceAsString(), 0, 10000),
            ]);

            return [
                'success' => false,
                'message' => "Service {$service->name} error: {$e->getMessage()}",
                'exit_code' => null,
                'output' => mb_substr($e->getMessage(), 0, 1000),
                'log_id' => $executionLog->id,
            ];
        }
    }
}
