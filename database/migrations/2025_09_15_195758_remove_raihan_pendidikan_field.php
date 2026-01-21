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
            if (Schema::hasColumn('slip_gaji_detail', 'raihan_pendidikan')) {
                $table->dropColumn('raihan_pendidikan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slip_gaji_detail', function (Blueprint $table) {
            $table->decimal('raihan_pendidikan', 15, 2)->nullable()->after('tunjangan_fungsional');
        });
    }
};
