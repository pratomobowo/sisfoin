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
        Schema::table('users', function (Blueprint $table) {
            $table->string('fingerprint_pin')->nullable()->after('employee_id');
            $table->boolean('fingerprint_enabled')->default(false)->after('fingerprint_pin');

            $table->index('fingerprint_pin');
            $table->index('fingerprint_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['fingerprint_pin']);
            $table->dropIndex(['fingerprint_enabled']);
            $table->dropColumn(['fingerprint_pin', 'fingerprint_enabled']);
        });
    }
};
