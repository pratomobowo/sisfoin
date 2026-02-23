<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_run_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sync_run_id')->constrained('sync_runs')->cascadeOnDelete();
            $table->string('entity_type');
            $table->string('external_id')->nullable();
            $table->string('level')->default('info');
            $table->text('message');
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['sync_run_id', 'level']);
            $table->index(['entity_type', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_run_items');
    }
};
