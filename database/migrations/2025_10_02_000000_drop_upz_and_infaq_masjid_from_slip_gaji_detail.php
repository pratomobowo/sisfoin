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
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        $columnsToDrop = [];
        foreach (['potongan_upz', 'potongan_infaq_masjid', 'potongan_bmt'] as $column) {
            if (Schema::hasColumn('slip_gaji_detail', $column)) {
                $columnsToDrop[] = $column;
            }
        }

        if (empty($columnsToDrop)) {
            return;
        }

        Schema::table('slip_gaji_detail', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['potongan_upz', 'potongan_infaq_masjid', 'potongan_bmt'] as $column) {
                if (Schema::hasColumn('slip_gaji_detail', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('slip_gaji_detail', function (Blueprint $table) {
            if (! Schema::hasColumn('slip_gaji_detail', 'potongan_infaq_masjid')) {
                $table->decimal('potongan_infaq_masjid', 15, 2)->nullable()->after('potongan_koperasi');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'potongan_upz')) {
                $table->decimal('potongan_upz', 15, 2)->nullable()->after('potongan_bmt');
            }

            if (! Schema::hasColumn('slip_gaji_detail', 'potongan_bmt')) {
                $table->decimal('potongan_bmt', 15, 2)->nullable()->after('potongan_upz');
            }
        });
    }
};
