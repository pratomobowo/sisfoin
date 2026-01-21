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
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string'); // string, integer, time, array
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('group')->default('general');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert default settings
        $defaults = [
            ['key' => 'work_start_time', 'value' => '08:00', 'type' => 'time', 'label' => 'Jam Masuk Kerja', 'description' => 'Waktu mulai kerja standar', 'group' => 'schedule', 'sort_order' => 1],
            ['key' => 'work_end_time', 'value' => '14:00', 'type' => 'time', 'label' => 'Jam Pulang Kerja', 'description' => 'Waktu pulang kerja standar', 'group' => 'schedule', 'sort_order' => 2],
            ['key' => 'early_arrival_threshold', 'value' => '07:40', 'type' => 'time', 'label' => 'Batas Datang Lebih Awal', 'description' => 'Waktu yang dianggap datang lebih awal', 'group' => 'schedule', 'sort_order' => 3],
            ['key' => 'late_tolerance_minutes', 'value' => '5', 'type' => 'integer', 'label' => 'Toleransi Keterlambatan (menit)', 'description' => 'Menit setelah jam masuk sebelum dianggap terlambat', 'group' => 'schedule', 'sort_order' => 4],
            ['key' => 'min_checkout_duration_minutes', 'value' => '30', 'type' => 'integer', 'label' => 'Durasi Minimum Check-out (menit)', 'description' => 'Jarak waktu minimum antara check-in dan check-out', 'group' => 'rules', 'sort_order' => 5],
            ['key' => 'standard_work_hours', 'value' => '6', 'type' => 'integer', 'label' => 'Jam Kerja Standar', 'description' => 'Jumlah jam kerja per hari', 'group' => 'rules', 'sort_order' => 6],
            ['key' => 'working_days', 'value' => '1,2,3,4,5,6', 'type' => 'array', 'label' => 'Hari Kerja', 'description' => 'Hari kerja (1=Senin, 7=Minggu)', 'group' => 'schedule', 'sort_order' => 7],
        ];

        foreach ($defaults as $setting) {
            DB::table('attendance_settings')->insert(array_merge($setting, [
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
        Schema::dropIfExists('attendance_settings');
    }
};
