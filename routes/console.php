<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register custom commands
Artisan::command('attendance:process {--date-from=} {--date-to=} {--user-id=} {--force}', function () {
    $this->call('attendance:process', [
        '--date-from' => $this->option('date-from'),
        '--date-to' => $this->option('date-to'),
        '--user-id' => $this->option('user-id'),
        '--force' => $this->option('force'),
    ]);
})->purpose('Process attendance logs and convert them to employee attendance records');
