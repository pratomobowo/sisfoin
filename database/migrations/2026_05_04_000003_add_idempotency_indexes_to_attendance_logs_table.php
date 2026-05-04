<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateAdmsIds = DB::table('attendance_logs')
            ->select('adms_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('adms_id')
            ->groupBy('adms_id')
            ->havingRaw('COUNT(*) > 1')
            ->limit(10)
            ->get();

        if ($duplicateAdmsIds->isNotEmpty()) {
            $sample = $duplicateAdmsIds->map(fn ($row) => 'adms_id='.$row->adms_id.', total='.$row->total)->implode('; ');
            throw new RuntimeException('Cannot add unique index to attendance_logs.adms_id because duplicates exist. Resolve duplicate ADMS IDs first. Sample: '.$sample);
        }

        $duplicatePinTimes = DB::table('attendance_logs')
            ->select('pin', 'datetime', DB::raw('COUNT(*) as total'))
            ->groupBy('pin', 'datetime')
            ->havingRaw('COUNT(*) > 1')
            ->limit(10)
            ->get();

        if ($duplicatePinTimes->isNotEmpty()) {
            $sample = $duplicatePinTimes->map(fn ($row) => 'pin='.$row->pin.', datetime='.$row->datetime.', total='.$row->total)->implode('; ');
            throw new RuntimeException('Cannot add unique index to attendance_logs pin + datetime because duplicates exist. Resolve duplicate log timestamps first. Sample: '.$sample);
        }

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->unique('adms_id', 'attendance_logs_adms_id_unique');
            $table->unique(['pin', 'datetime'], 'attendance_logs_pin_datetime_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropUnique('attendance_logs_adms_id_unique');
            $table->dropUnique('attendance_logs_pin_datetime_unique');
        });
    }
};
