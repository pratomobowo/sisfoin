<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique(['employee_type', 'employee_id'], 'users_employee_type_employee_id_unique');
            $table->index(['employee_type', 'employee_id'], 'users_employee_type_employee_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_employee_type_employee_id_unique');
            $table->dropIndex('users_employee_type_employee_id_index');
        });
    }
};
