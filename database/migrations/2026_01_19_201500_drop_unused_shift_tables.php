<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drop tables that are no longer used after restructuring shift management.
     */
    public function up(): void
    {
        // Drop user_weekly_shifts - replaced by employee_shift_assignments
        Schema::dropIfExists('user_weekly_shifts');
        
        // Drop unit_shift_assignments - not needed anymore (simplified to employee-based)
        Schema::dropIfExists('unit_shift_assignments');
        
        // Drop user_shift_overrides - replaced by employee_shift_assignments
        Schema::dropIfExists('user_shift_overrides');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate user_weekly_shifts
        Schema::create('user_weekly_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('work_shift_id')->constrained('work_shifts')->cascadeOnDelete();
            $table->date('week_start_date');
            $table->integer('year');
            $table->integer('week_number');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'year', 'week_number'], 'unique_user_week');
        });
        
        // Recreate unit_shift_assignments
        Schema::create('unit_shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('satuan_kerja');
            $table->foreignId('work_shift_id')->constrained('work_shifts')->cascadeOnDelete();
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        
        // Recreate user_shift_overrides
        Schema::create('user_shift_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('work_shift_id')->constrained('work_shifts')->cascadeOnDelete();
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
};
