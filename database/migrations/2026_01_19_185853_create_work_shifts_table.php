<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('work_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->time('start_time');
            $table->time('end_time');
            $table->time('early_arrival_threshold');
            $table->integer('late_tolerance_minutes')->default(5);
            $table->decimal('work_hours', 4, 2)->default(6);
            $table->string('color', 20)->default('blue');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default shifts
        $shifts = [
            [
                'name' => 'Shift Normal',
                'code' => 'NORMAL',
                'start_time' => '08:00:00',
                'end_time' => '14:00:00',
                'early_arrival_threshold' => '07:40:00',
                'late_tolerance_minutes' => 5,
                'work_hours' => 6,
                'color' => 'blue',
                'is_default' => true,
                'is_active' => true,
                'description' => 'Shift standar untuk sebagian besar pegawai',
            ],
            [
                'name' => 'Shift Pagi',
                'code' => 'PAGI',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'early_arrival_threshold' => '05:40:00',
                'late_tolerance_minutes' => 5,
                'work_hours' => 8,
                'color' => 'green',
                'is_default' => false,
                'is_active' => true,
                'description' => 'Shift pagi untuk keamanan dan staf tertentu',
            ],
            [
                'name' => 'Shift Siang',
                'code' => 'SIANG',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'early_arrival_threshold' => '13:40:00',
                'late_tolerance_minutes' => 5,
                'work_hours' => 8,
                'color' => 'yellow',
                'is_default' => false,
                'is_active' => true,
                'description' => 'Shift siang untuk keamanan',
            ],
            [
                'name' => 'Shift Malam',
                'code' => 'MALAM',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'early_arrival_threshold' => '21:40:00',
                'late_tolerance_minutes' => 5,
                'work_hours' => 8,
                'color' => 'purple',
                'is_default' => false,
                'is_active' => true,
                'description' => 'Shift malam untuk keamanan',
            ],
        ];

        foreach ($shifts as $shift) {
            DB::table('work_shifts')->insert(array_merge($shift, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_shifts');
    }
};
