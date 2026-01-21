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
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->bigInteger('adms_id')->nullable()->after('id')->index();
        });

        // Backfill data from raw_data
        DB::table('attendance_logs')->orderBy('id')->chunk(500, function ($logs) {
            foreach ($logs as $log) {
                if ($log->raw_data) {
                    $rawData = json_decode($log->raw_data, true);
                    if (isset($rawData['id'])) {
                        DB::table('attendance_logs')
                            ->where('id', $log->id)
                            ->update(['adms_id' => $rawData['id']]);
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn('adms_id');
        });
    }
};
