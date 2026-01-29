<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\SlipGajiDetail;
use App\Services\SlipGajiService;
use App\Services\GmailSmtpService;
use App\Events\EmailLogSent;
use App\Events\EmailLogFailed;
use App\Events\EmailLogUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendSlipGajiEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $slipGajiDetail;
    protected $emailLog;
    protected $retryAfter = 60; // Retry after 60 seconds
    protected $tries = 3; // Max 3 attempts

    /**
     * Create a new job instance.
     */
    public function __construct(SlipGajiDetail $slipGajiDetail, EmailLog $emailLog)
    {
        $this->slipGajiDetail = $slipGajiDetail;
        $this->emailLog = $emailLog;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('SendSlipGajiEmailJob started', ['log_id' => $this->emailLog->id]);
        try {
            // Update status to processing
            $this->emailLog->update([
                'status' => 'processing',
                'error_message' => null,
            ]);

            // Apply SMTP configuration from database
            $gmailSmtpService = app(GmailSmtpService::class);
            $gmailSmtpService->applyConfig();

            // Get recipient email
            $recipientEmail = $this->getRecipientEmail();
            
            if (!$recipientEmail) {
                throw new \Exception('No valid email found for recipient');
            }
            Log::info('Recipient identified', ['email' => $recipientEmail]);

            // Generate PDF
            $slipGajiService = app(SlipGajiService::class);
            $pdfContent = $slipGajiService->generatePdfSlip($this->slipGajiDetail->id);
            $pdfFilename = $slipGajiService->generatePdfFilename($this->slipGajiDetail);
            Log::info('PDF generated successfully', ['filename' => $pdfFilename, 'size' => strlen($pdfContent)]);

            // Store PDF temporarily
            $pdfPath = 'temp/slip-gaji/' . $pdfFilename;
            Storage::disk('local')->put($pdfPath, $pdfContent);

            // Get employee name
            $employeeName = $this->getEmployeeName();
            $periode = $this->slipGajiDetail->header->periode;

            // Prepare email data
            $emailData = [
                'employeeName' => $employeeName,
                'periode' => $periode,
                'periodeFormatted' => $this->formatPeriode($periode),
                'nip' => $this->slipGajiDetail->nip,
                'penerimaanBersih' => number_format($this->slipGajiDetail->penerimaan_bersih, 0, ',', '.'),
                'totalPotongan' => number_format($this->slipGajiDetail->total_potongan, 0, ',', '.'),
            ];
            Log::info('Attempting to send mail', ['to' => $recipientEmail]);

            // Send email
            Mail::send('emails.slip-gaji', $emailData, function ($message) use ($recipientEmail, $pdfPath, $pdfFilename, $employeeName, $periode) {
                $message->to($recipientEmail)
                    ->subject('Slip Gaji ' . $employeeName . ' - Periode ' . $periode)
                    ->attach(Storage::disk('local')->path($pdfPath), [
                        'as' => $pdfFilename,
                        'mime' => 'application/pdf',
                    ]);
            });

            // Update email log to sent
            $this->emailLog->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);

            // Clean up temporary file
            Storage::disk('local')->delete($pdfPath);

            // Broadcast events for real-time update
            event(new EmailLogSent($this->emailLog));
            event(new EmailLogUpdated($this->emailLog));

            Log::info('Slip gaji email sent successfully', [
                'email_log_id' => $this->emailLog->id,
                'slip_gaji_detail_id' => $this->slipGajiDetail->id,
                'recipient_email' => $recipientEmail,
            ]);

        } catch (\Exception $e) {
            // Update email log to failed
            $this->emailLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // Broadcast events for real-time update
            event(new EmailLogFailed($this->emailLog));
            event(new EmailLogUpdated($this->emailLog));

            Log::error('Failed to send slip gaji email', [
                'email_log_id' => $this->emailLog->id,
                'slip_gaji_detail_id' => $this->slipGajiDetail->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw the exception to let Laravel handle the retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Update email log to failed if not already updated
        if ($this->emailLog->status !== 'failed') {
            $this->emailLog->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }

        // Broadcast events for real-time update
        event(new EmailLogFailed($this->emailLog));
        event(new EmailLogUpdated($this->emailLog));

        Log::error('Slip gaji email job failed permanently', [
            'email_log_id' => $this->emailLog->id,
            'slip_gaji_detail_id' => $this->slipGajiDetail->id,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Get the recipient email address
     */
    private function getRecipientEmail(): ?string
    {
        // Priority: email kampus > email pribadi
        if ($this->slipGajiDetail->employee) {
            return $this->slipGajiDetail->employee->email_kampus ?: $this->slipGajiDetail->employee->email;
        }

        if ($this->slipGajiDetail->dosen) {
            return $this->slipGajiDetail->dosen->email_kampus ?: $this->slipGajiDetail->dosen->email;
        }

        return null;
    }

    /**
     * Get the employee name
     */
    private function getEmployeeName(): string
    {
        if ($this->slipGajiDetail->employee) {
            return $this->slipGajiDetail->employee->nama_lengkap_with_gelar;
        }

        if ($this->slipGajiDetail->dosen) {
            return $this->slipGajiDetail->dosen->nama_lengkap_with_gelar;
        }

        return 'Pegawai';
    }

    /**
     * Format periode for display
     */
    private function formatPeriode(string $periode): string
    {
        $months = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        [$year, $month] = explode('-', $periode);

        return $months[$month] . ' ' . $year;
    }
}
