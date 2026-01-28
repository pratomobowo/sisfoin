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
            // Drop the old unique index
            $table->dropUnique('unique_periode');
            
            // Create a new unique index spanning both periode and mode
            $table->unique(['periode', 'mode'], 'unique_periode_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slip_gaji_header', function (Blueprint $table) {
            // Drop the new unique index
            $table->dropUnique('unique_periode_mode');
            
            // Restore the old unique index
            $table->unique('periode', 'unique_periode');
        });
    }
};
