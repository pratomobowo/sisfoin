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
        Schema::table('slip_gaji_header', function (Blueprint $table) {
            $table->enum('mode', ['standard', 'gaji_13'])->default('standard')->after('periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slip_gaji_header', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
