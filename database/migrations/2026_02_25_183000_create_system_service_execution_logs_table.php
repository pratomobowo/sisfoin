<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_service_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_service_id')->constrained('system_services')->cascadeOnDelete();
            $table->string('service_key', 64);
            $table->string('service_name', 191);
            $table->string('command', 255);
            $table->string('triggered_by', 32)->default('scheduler'); // scheduler|manual
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('status', 16)->default('running'); // running|success|error|skipped
            $table->integer('exit_code')->nullable();
            $table->text('message')->nullable();
            $table->longText('output')->nullable();
            $table->timestamps();

            $table->index(['service_key', 'started_at']);
            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_service_execution_logs');
    }
};
