<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE email_logs MODIFY status ENUM('sent','failed','pending','processing') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE email_logs MODIFY status ENUM('sent','failed','pending') DEFAULT 'pending'");
    }
};
