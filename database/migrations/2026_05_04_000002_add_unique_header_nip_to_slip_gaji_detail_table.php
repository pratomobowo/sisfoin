<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('slip_gaji_detail')
            ->select('header_id', 'nip', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nip')
            ->groupBy('header_id', 'nip')
            ->havingRaw('COUNT(*) > 1')
            ->limit(10)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $sample = $duplicates
                ->map(fn ($row) => 'header_id='.$row->header_id.', nip='.$row->nip.', total='.$row->total)
                ->implode('; ');

            throw new RuntimeException('Cannot add unique index to slip_gaji_detail because duplicate header_id + nip rows exist. Run php artisan slip-gaji:report-duplicate-details and resolve duplicates first. Sample: '.$sample);
        }

        Schema::table('slip_gaji_detail', function (Blueprint $table) {
            $table->unique(['header_id', 'nip'], 'slip_gaji_detail_header_id_nip_unique');
        });
    }

    public function down(): void
    {
        Schema::table('slip_gaji_detail', function (Blueprint $table) {
            $table->dropUnique('slip_gaji_detail_header_id_nip_unique');
        });
    }
};
