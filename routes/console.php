<?php

use App\Models\SystemService;
use App\Services\SystemServiceRunner;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (Schema::hasTable('system_services')) {
    SystemService::query()
        ->where('is_active', true)
        ->get()
        ->each(function (SystemService $service): void {
            $command = match ($service->key) {
                'fingerprint_sync' => 'fingerprint:pull-data --process',
                'attendance_processor' => 'attendance:process',
                'email_queue' => 'queue:work --stop-when-empty --tries=1 --timeout=60',
                default => null,
            };

            if (! $command) {
                return;
            }

            $event = Schedule::call(function () use ($service): void {
                app(SystemServiceRunner::class)->run($service, 'scheduler', null);
            })->name('system_service:'.$service->key)->withoutOverlapping()->onOneServer();

            match ($service->schedule_preset) {
                'every_5_minutes' => $event->everyFiveMinutes(),
                'every_10_minutes' => $event->everyTenMinutes(),
                'every_15_minutes' => $event->everyFifteenMinutes(),
                'every_30_minutes' => $event->everyThirtyMinutes(),
                'hourly' => $event->hourly(),
                'daily_00_00' => $event->dailyAt('00:00'),
                'daily_01_00' => $event->dailyAt('01:00'),
                'daily_02_00' => $event->dailyAt('02:00'),
                'daily_03_00' => $event->dailyAt('03:00'),
                default => $event->cron('0 0 31 2 *'),
            };
        });
}
