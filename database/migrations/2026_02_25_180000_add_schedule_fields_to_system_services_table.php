<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_services', function (Blueprint $table) {
            $table->string('schedule_preset', 32)->default('disabled')->after('status');
            $table->timestamp('last_run_started_at')->nullable()->after('last_run_at');
            $table->timestamp('last_run_finished_at')->nullable()->after('last_run_started_at');
            $table->string('last_run_result', 16)->nullable()->after('last_run_finished_at');
            $table->text('last_run_message')->nullable()->after('last_run_result');
        });

        // Default presets for seeded services
        DB::table('system_services')->where('key', 'fingerprint_sync')->update(['schedule_preset' => 'every_15_minutes']);
        DB::table('system_services')->where('key', 'attendance_processor')->update(['schedule_preset' => 'every_15_minutes']);
        DB::table('system_services')->where('key', 'email_queue')->update(['schedule_preset' => 'every_5_minutes']);
        DB::table('system_services')->where('key', 'system_backup')->update(['schedule_preset' => 'daily_01_00']);
    }

    public function down(): void
    {
        Schema::table('system_services', function (Blueprint $table) {
            $table->dropColumn([
                'schedule_preset',
                'last_run_started_at',
                'last_run_finished_at',
                'last_run_result',
                'last_run_message',
            ]);
        });
    }
};
