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
        Schema::table('email_logs', function (Blueprint $table) {
            $table->foreignId('slip_gaji_detail_id')->nullable()->after('id');
            $table->foreign('slip_gaji_detail_id')->references('id')->on('slip_gaji_detail')->onDelete('cascade');
            
            // Add index for better performance
            $table->index('slip_gaji_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropForeign(['slip_gaji_detail_id']);
            $table->dropIndex(['slip_gaji_detail_id']);
            $table->dropColumn('slip_gaji_detail_id');
        });
    }
};
