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
            $table->string('nip')->unique()->nullable()->after('email');
            $table->string('employee_type')->nullable()->after('nip'); // 'employee' or 'dosen'
            $table->string('employee_id')->nullable()->after('employee_type'); // reference to employee or dosen id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'employee_type', 'employee_id']);
        });
    }
};
