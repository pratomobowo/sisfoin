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
        // First, update any existing records to the new code
        DB::table('slip_gaji_detail')
            ->where('status', 'DOSEN_PERJANJIAN_KHUSUS')
            ->update(['status' => 'DOSEN_TETAP']); // Temporarily move to a safe value to allow enum change if necessary, 
                                                   // although MySQL usually allows adding to enum easily.
                                                   // Actually, let's just update the enum first.

        Schema::table('slip_gaji_detail', function (Blueprint $table) {
            $table->enum('status', [
                'KARYAWAN_TETAP', 
                'KARYAWAN_KONTRAK', 
                'KARYAWAN_MAGANG', 
                'DOSEN_TETAP', 
                'DOSEN_DPLK', 
                'DOSEN_DPK', 
                'DOSEN_GURU_BESAR',
                'DOSEN_PK'
            ])->change();
        });

        // Update records to the new code
        DB::table('slip_gaji_detail')
            ->where('status', 'DOSEN_PERJANJIAN_KHUSUS')
            ->update(['status' => 'DOSEN_PK']);
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
                'DOSEN_GURU_BESAR',
                'DOSEN_PERJANJIAN_KHUSUS'
            ])->change();
        });

        DB::table('slip_gaji_detail')
            ->where('status', 'DOSEN_PK')
            ->update(['status' => 'DOSEN_PERJANJIAN_KHUSUS']);
    }
};
