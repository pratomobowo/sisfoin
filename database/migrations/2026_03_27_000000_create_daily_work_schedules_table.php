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
        Schema::create('daily_work_schedules', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('day_of_week')->unique(); // 1=Mon, 2=Tue, ..., 6=Sat
            $table->string('day_name', 20);
            $table->time('start_time')->default('08:00:00');
            $table->time('end_time')->default('14:00:00');
            $table->time('early_arrival_threshold')->default('07:40:00');
            $table->integer('late_tolerance_minutes')->default(5);
            $table->decimal('work_hours', 4, 2)->default(6);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default schedules: Mon-Fri 08:00-14:00, Sat 08:00-12:00
        $days = [
            ['day_of_week' => 1, 'day_name' => 'Senin',  'start_time' => '08:00:00', 'end_time' => '14:00:00', 'work_hours' => 6, 'is_active' => true],
            ['day_of_week' => 2, 'day_name' => 'Selasa', 'start_time' => '08:00:00', 'end_time' => '14:00:00', 'work_hours' => 6, 'is_active' => true],
            ['day_of_week' => 3, 'day_name' => 'Rabu',   'start_time' => '08:00:00', 'end_time' => '14:00:00', 'work_hours' => 6, 'is_active' => true],
            ['day_of_week' => 4, 'day_name' => 'Kamis',  'start_time' => '08:00:00', 'end_time' => '14:00:00', 'work_hours' => 6, 'is_active' => true],
            ['day_of_week' => 5, 'day_name' => 'Jumat',  'start_time' => '08:00:00', 'end_time' => '14:00:00', 'work_hours' => 6, 'is_active' => true],
            ['day_of_week' => 6, 'day_name' => 'Sabtu',  'start_time' => '08:00:00', 'end_time' => '12:00:00', 'work_hours' => 4, 'is_active' => true],
        ];

        foreach ($days as $day) {
            DB::table('daily_work_schedules')->insert(array_merge($day, [
                'early_arrival_threshold' => '07:40:00',
                'late_tolerance_minutes' => 5,
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
        Schema::dropIfExists('daily_work_schedules');
    }
};
