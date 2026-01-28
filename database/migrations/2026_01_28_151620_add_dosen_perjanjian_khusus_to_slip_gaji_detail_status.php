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
        Schema::table('slip_gaji_detail', function (Blueprint $table) {
            $table->enum('status', [
                'KARYAWAN_TETAP', 
                'KARYAWAN_KONTRAK', 
                'KARYAWAN_MAGANG', 
                'DOSEN_TETAP', 
                'DOSEN_DPLK', 
                'DOSEN_DPK', 
                'DOSEN_GURU_BESAR',
                'DOSEN_PERJANJIAN_KHUSUS'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slip_gaji_detail', function (Blueprint $table) {
            $table->enum('status', [
                'KARYAWAN_TETAP', 
                'KARYAWAN_KONTRAK', 
                'KARYAWAN_MAGANG', 
                'DOSEN_TETAP', 
                'DOSEN_DPLK', 
                'DOSEN_DPK', 
                'DOSEN_GURU_BESAR'
            ])->change();
        });
    }
};
