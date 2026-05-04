<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportDuplicateSlipGajiDetailsCommand extends Command
{
    protected $signature = 'slip-gaji:report-duplicate-details {--header= : Limit report to one slip_gaji_header ID}';

    protected $description = 'Report duplicate slip gaji detail rows by header_id and NIP before adding unique constraints';

    public function handle(): int
    {
        $headerId = $this->option('header');

        $duplicateQuery = DB::table('slip_gaji_detail')
            ->select('header_id', 'nip', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nip')
            ->groupBy('header_id', 'nip')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('header_id')
            ->orderBy('nip');

        if ($headerId !== null) {
            $duplicateQuery->where('header_id', (int) $headerId);
        }

        $duplicates = $duplicateQuery->get();

        $this->info('Duplicate slip gaji detail groups: '.$duplicates->count());

        if ($duplicates->isEmpty()) {
            return self::SUCCESS;
        }

        $selectColumns = ['d.id', 'd.header_id', 'h.periode', 'h.mode', 'h.status as header_status', 'd.nip', 'd.status', 'd.created_at'];

        foreach (['penerimaan_kotor', 'penerimaan_bersih', 'gaji_bersih'] as $column) {
            if (Schema::hasColumn('slip_gaji_detail', $column)) {
                $selectColumns[] = 'd.'.$column;
            }
        }

        $rows = DB::table('slip_gaji_detail as d')
            ->join('slip_gaji_header as h', 'h.id', '=', 'd.header_id')
            ->where(function ($query) use ($duplicates) {
                foreach ($duplicates as $duplicate) {
                    $query->orWhere(function ($subQuery) use ($duplicate) {
                        $subQuery->where('d.header_id', $duplicate->header_id)
                            ->where('d.nip', $duplicate->nip);
                    });
                }
            })
            ->select($selectColumns)
            ->orderBy('d.header_id')
            ->orderBy('d.nip')
            ->orderBy('d.id')
            ->get()
            ->map(fn ($row) => [
                'detail_id' => (string) $row->id,
                'header_id' => (string) $row->header_id,
                'periode' => (string) $row->periode,
                'mode' => (string) $row->mode,
                'header_status' => (string) $row->header_status,
                'nip' => (string) $row->nip,
                'detail_status' => (string) $row->status,
                'gross' => (string) ($row->penerimaan_kotor ?? '-'),
                'net' => (string) ($row->penerimaan_bersih ?? $row->gaji_bersih ?? '-'),
                'created_at' => (string) $row->created_at,
            ])
            ->all();

        $this->table(['Detail ID', 'Header ID', 'Periode', 'Mode', 'Header Status', 'NIP', 'Detail Status', 'Gross', 'Net', 'Created'], $rows);
        $this->warn('Resolve these duplicates before running the unique index migration. No rows were changed.');

        return self::FAILURE;
    }
}
