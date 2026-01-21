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
            $table->dropColumn(['potongan_upz', 'potongan_infaq_masjid', 'potongan_bmt']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slip_gaji_detail', function (Blueprint $table) {
            $table->decimal('potongan_infaq_masjid', 15, 2)->nullable()->after('potongan_koperasi');
            $table->decimal('potongan_upz', 15, 2)->nullable()->after('potongan_bmt');
            $table->decimal('potongan_bmt', 15, 2)->nullable()->after('potongan_upz');
        });
    }
};
