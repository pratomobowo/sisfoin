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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('pin')->index(); // ID pengguna
            $table->string('name')->nullable(); // Nama pengguna
            $table->dateTime('datetime')->index(); // Waktu absensi
            $table->tinyInteger('status')->default(0); // Status absensi
            $table->tinyInteger('verify')->default(0); // Metode verifikasi
            $table->integer('workcode')->default(0); // Kode kerja
            $table->foreignId('mesin_finger_id')->constrained('mesin_fingers')->onDelete('cascade');
            $table->json('raw_data')->nullable(); // Data mentah dari mesin
            $table->timestamps();

            // Index untuk query yang sering digunakan
            $table->index(['datetime', 'pin']);
            $table->index(['mesin_finger_id', 'datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
