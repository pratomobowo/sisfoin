<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SlipGajiDetail;
use App\Models\EmailLog;
use App\Jobs\SendSlipGajiEmailJob;
use Illuminate\Support\Facades\Log;

class DebugSlipEmail extends Command
{
    protected $signature = 'debug:slip-email {detail_id}';
    protected $description = 'Debug slip gaji email sending synchronously';

    public function handle()
    {
        $detailId = $this->argument('detail_id');
        $this->info("Debugging Slip Gaji Detail ID: {$detailId}");

        $detail = SlipGajiDetail::find($detailId);
        if (!$detail) {
            $this->error("Detail not found!");
            return;
        }

        $emailLog = EmailLog::where('slip_gaji_detail_id', $detailId)->latest()->first();
        if (!$emailLog) {
            $this->warn("No email log found, creating a temporary one.");
            $emailLog = EmailLog::create([
                'slip_gaji_detail_id' => $detail->id,
                'from_email' => 'debug@example.com',
                'to_email' => 'debug@example.com',
                'subject' => 'Debug Email',
                'message' => 'Debug',
                'status' => 'pending'
            ]);
        }

        $this->info("Running job synchronously...");
        
        try {
            $job = new SendSlipGajiEmailJob($detail, $emailLog);
            $job->handle();
            $this->info("Job handle() completed without throwing exception.");
        } catch (\Exception $e) {
            $this->error("CAUGHT EXCEPTION: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->info("Check logs for detailed trace.");
    }
}
