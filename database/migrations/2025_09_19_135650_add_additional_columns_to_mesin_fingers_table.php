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
        Schema::table('mesin_fingers', function (Blueprint $table) {
            // Update status column to use enum
            $table->enum('status', ['active', 'inactive', 'error', 'maintenance'])->default('inactive')->change();

            // Add new columns
            $table->string('serial_number')->nullable()->after('keterangan');
            $table->string('device_model')->default('X100C')->after('serial_number');
            $table->timestamp('last_connected_at')->nullable()->after('device_model');
            $table->json('device_info')->nullable()->after('last_connected_at');
            $table->boolean('auto_sync')->default(false)->after('device_info');
            $table->integer('sync_interval')->default(60)->after('auto_sync'); // in minutes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mesin_fingers', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn([
                'serial_number',
                'device_model',
                'last_connected_at',
                'device_info',
                'auto_sync',
                'sync_interval',
            ]);

            // Revert status column to string
            $table->string('status')->default('inactive')->change();
        });
    }
};
