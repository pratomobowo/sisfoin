<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('employee_attendances')
            ->select('user_id', 'date', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id', 'date')
            ->havingRaw('COUNT(*) > 1')
            ->limit(10)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $sample = $duplicates
                ->map(fn ($row) => 'user_id='.$row->user_id.', date='.$row->date.', total='.$row->total)
                ->implode('; ');

            throw new RuntimeException('Cannot add unique index to employee_attendances because duplicate user_id + date rows exist. Run php artisan attendance:report-duplicates and resolve duplicates first. Sample: '.$sample);
        }

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->unique(['user_id', 'date'], 'employee_attendances_user_id_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->dropUnique('employee_attendances_user_id_date_unique');
        });
    }
};
