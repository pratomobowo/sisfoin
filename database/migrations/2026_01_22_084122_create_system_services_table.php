<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_services', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status')->default('running'); // running, stopped, error
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });

        // Insert default services
        \DB::table('system_services')->insert([
            [
                'key' => 'email_queue',
                'name' => 'Antrian Email',
                'description' => 'Mengelola pengiriman email secara background menggunakan queue worker.',
                'is_active' => true,
                'status' => 'running',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'fingerprint_sync',
                'name' => 'Sinkronisasi Sidik Jari',
                'description' => 'Menarik data log absensi dari mesin ADMS secara otomatis.',
                'is_active' => true,
                'status' => 'running',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'attendance_processor',
                'name' => 'Pemroses Kehadiran',
                'description' => 'Menghitung status kehadiran, keterlambatan, dan lembur dari log mentah.',
                'is_active' => true,
                'status' => 'running',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'system_backup',
                'name' => 'Pencadangan Sistem',
                'description' => 'Melakukan backup database dan file penting secara berkala.',
                'is_active' => false,
                'status' => 'stopped',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_services');
    }
};
