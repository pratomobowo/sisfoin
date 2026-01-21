<?php

namespace App\Console\Commands;

use App\Models\SlipGajiDetail;
use Illuminate\Console\Command;

class CheckPph21Data extends Command
{
    protected $signature = 'excel:check-pph21';

    protected $description = 'Check PPh21 data in imported slip gaji';

    public function handle()
    {
        $this->info('Checking PPh21 data in slip gaji details...');
        $this->info('=========================================');

        // Get latest 5 records
        $records = SlipGajiDetail::latest()->take(5)->get();

        if ($records->isEmpty()) {
            $this->error('No records found!');

            return 1;
        }

        foreach ($records as $record) {
            $this->info("ID: {$record->id}");
            $this->info("NIP: {$record->nip}");
            $this->info("Nama: {$record->nama}");
            $this->info('PPh21 Terhutang: '.number_format($record->pph21_terhutang, 0, ',', '.'));
            $this->info('PPh21 Sudah Dipotong: '.number_format($record->pph21_sudah_dipotong, 0, ',', '.'));
            $this->info('PPh21 Kurang Dipotong: '.number_format($record->pph21_kurang_dipotong, 0, ',', '.'));
            $this->info('Pajak: '.number_format($record->pajak, 0, ',', '.'));
            $this->info('Gaji Bersih: '.number_format($record->gaji_bersih, 0, ',', '.'));
            $this->info('---');
        }

        // Check if any PPh21 values are non-zero
        $nonZeroPph21 = SlipGajiDetail::where(function ($query) {
            $query->where('pph21_terhutang', '>', 0)
                ->orWhere('pph21_sudah_dipotong', '>', 0)
                ->orWhere('pph21_kurang_dipotong', '>', 0);
        })->count();

        $this->info("Records with non-zero PPh21 values: {$nonZeroPph21}");

        return 0;
    }
}
