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
            $table->text('last_error_message')->nullable()->after('last_connected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mesin_fingers', function (Blueprint $table) {
            $table->dropColumn('last_error_message');
        });
    }
};
