<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slip_gaji_import_previews', function (Blueprint $table) {
            $table->id();
            $table->string('token', 80)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('periode', 7);
            $table->string('mode', 20)->default('standard');
            $table->string('file_original');
            $table->string('status', 20)->default('pending');
            $table->json('summary_json')->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['periode', 'mode']);
            $table->index('expires_at');
        });

        Schema::create('slip_gaji_import_preview_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preview_id')->constrained('slip_gaji_import_previews')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('nip')->nullable();
            $table->string('nama')->nullable();
            $table->decimal('net_amount', 18, 2)->nullable();
            $table->decimal('gross_amount', 18, 2)->nullable();
            $table->decimal('deduction_amount', 18, 2)->nullable();
            $table->json('data_json')->nullable();
            $table->string('validation_status', 20)->default('valid');
            $table->json('validation_errors_json')->nullable();
            $table->timestamps();

            $table->index(['preview_id', 'row_number']);
            $table->index(['preview_id', 'validation_status']);
            $table->index('nip');
            $table->index('nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slip_gaji_import_preview_rows');
        Schema::dropIfExists('slip_gaji_import_previews');
    }
};
