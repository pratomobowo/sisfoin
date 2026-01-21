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
            // Cek jika field raihan_s2s3 masih ada, hapus dulu
            if (Schema::hasColumn('slip_gaji_detail', 'raihan_s2s3')) {
                $table->dropColumn('raihan_s2s3');
            }

            // Cek jika field raihan_pendidikan belum ada, tambahkan
            if (! Schema::hasColumn('slip_gaji_detail', 'raihan_pendidikan')) {
                $table->decimal('raihan_pendidikan', 15, 2)->nullable()->after('tunjangan_fungsional');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slip_gaji_detail', function (Blueprint $table) {
            // Cek jika field raihan_pendidikan ada, hapus dulu
            if (Schema::hasColumn('slip_gaji_detail', 'raihan_pendidikan')) {
                $table->dropColumn('raihan_pendidikan');
            }

            // Cek jika field raihan_s2s3 belum ada, tambahkan
            if (! Schema::hasColumn('slip_gaji_detail', 'raihan_s2s3')) {
                $table->string('raihan_s2s3', 10)->nullable()->after('tunjangan_fungsional');
            }
        });
    }
};
