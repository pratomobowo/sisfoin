<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE slip_gaji_header MODIFY COLUMN mode ENUM('standard', 'gaji_13', 'thr') DEFAULT 'standard'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE slip_gaji_header MODIFY COLUMN mode ENUM('standard', 'gaji_13') DEFAULT 'standard'");
    }
};
