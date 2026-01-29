<?php

namespace App\Services;

use App\Jobs\SendSlipGajiEmailJob;
use App\Models\EmailLog;
use App\Models\SlipGajiDetail;
use App\Models\SlipGajiHeader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlipGajiEmailService
{
    /**
     * Send bulk email for slip gaji details
     */
    public function sendBulkEmail(int $headerId, array $selectedDetails = []): array
    {
        try {
            DB::beginTransaction();

            $header = SlipGajiHeader::findOrFail($headerId);
            
            // Get details to send email
            $query = SlipGajiDetail::with(['employee', 'dosen'])
                ->where('header_id', $headerId);

            // If specific details selected, filter by them
            if (!empty($selectedDetails)) {
                $query->whereIn('id', $selectedDetails);
            }

            $details = $query->get();

            $validRecipients = [];
            $invalidRecipients = [];
            $emailLogs = [];

            // Process each detail to validate recipients
            foreach ($details as $detail) {
                $recipientEmail = $this->getRecipientEmail($detail);
                
                if ($recipientEmail) {
                    $validRecipients[] = [
                        'detail' => $detail,
                        'email' => $recipientEmail,
                        'name' => $this->getEmployeeName($detail),
                    ];
                } else {
                    $invalidRecipients[] = [
                        'detail' => $detail,
                        'reason' => 'No valid email found',
                    ];
                }
            }

            // Create email logs for valid recipients
            foreach ($validRecipients as $recipient) {
                $emailLog = EmailLog::create([
                    'from_email' => config('mail.from.address'),
                    'to_email' => $recipient['email'],
                    'subject' => 'Slip Gaji ' . $recipient['name'] . ' - Periode ' . $header->periode,
                    'message' => 'Slip gaji untuk periode ' . $header->periode,
                    'status' => 'pending',
                    'error_message' => null,
                    'sent_at' => null,
                    'slip_gaji_detail_id' => $recipient['detail']->id,
                ]);

                $emailLogs[] = $emailLog;

                // Dispatch job to queue
                SendSlipGajiEmailJob::dispatch($recipient['detail'], $emailLog)
                    ->onQueue('emails')
                    ->delay(now()->addSeconds(rand(1, 30))); // Random delay to avoid overwhelming
            }

            DB::commit();

            Log::info('Bulk slip gaji email queued', [
                'header_id' => $headerId,
                'valid_recipients' => count($validRecipients),
                'invalid_recipients' => count($invalidRecipients),
                'user_id' => Auth::id(),
            ]);

            return [
                'success' => true,
                'message' => 'Email slip gaji berhasil dijadwalkan untuk dikirim',
                'valid_recipients' => count($validRecipients),
                'invalid_recipients' => count($invalidRecipients),
                'email_logs' => $emailLogs,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error sending bulk slip gaji email', [
                'header_id' => $headerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim email slip gaji: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send single email for a slip gaji detail
     */
    public function sendSingleEmail(int $detailId): array
    {
        try {
            DB::beginTransaction();

            $detail = SlipGajiDetail::with(['employee', 'dosen', 'header'])
                ->findOrFail($detailId);

            $recipientEmail = $this->getRecipientEmail($detail);
            
            if (!$recipientEmail) {
                throw new \Exception('No valid email found for recipient');
            }

            $employeeName = $this->getEmployeeName($detail);

            // Create email log
            $emailLog = EmailLog::create([
                'from_email' => config('mail.from.address'),
                'to_email' => $recipientEmail,
                'subject' => 'Slip Gaji ' . $employeeName . ' - Periode ' . $detail->header->periode,
                'message' => 'Slip gaji untuk periode ' . $detail->header->periode,
                'status' => 'pending',
                'error_message' => null,
                'sent_at' => null,
                'slip_gaji_detail_id' => $detail->id,
            ]);

            // Dispatch job to queue
            SendSlipGajiEmailJob::dispatch($detail, $emailLog)
                ->onQueue('emails')
                ->delay(now()->addSeconds(rand(1, 5)));

            DB::commit();

            Log::info('Single slip gaji email queued', [
                'detail_id' => $detailId,
                'recipient_email' => $recipientEmail,
                'user_id' => Auth::id(),
            ]);

            return [
                'success' => true,
                'message' => 'Email slip gaji berhasil dijadwalkan untuk dikirim',
                'email_log' => $emailLog,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error sending single slip gaji email', [
                'detail_id' => $detailId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim email slip gaji: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Retry a single failed email log
     */
    public function retrySingleEmail(EmailLog $emailLog): array
    {
        try {
            DB::beginTransaction();

            // Load requirement
            $emailLog->load('slipGajiDetail');

            if (!$emailLog->slip_gaji_detail_id) {
                throw new \Exception('Email log is not related to a slip gaji detail');
            }

            // Reset status to pending
            $emailLog->update([
                'status' => 'pending',
                'error_message' => null,
                'sent_at' => null,
            ]);

            // Dispatch job again with the SAME email log
            SendSlipGajiEmailJob::dispatch($emailLog->slipGajiDetail, $emailLog)
                ->onQueue('emails')
                ->delay(now()->addSeconds(rand(1, 5)));

            DB::commit();

            return [
                'success' => true,
                'message' => 'Email slip gaji berhasil dijadwalkan ulang',
                'email_log' => $emailLog,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error retrying single slip gaji email', [
                'log_id' => $emailLog->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengulangi pengiriman email: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get email statistics for a header
     */
    public function getEmailStats(int $headerId): array
    {
        try {
            $stats = EmailLog::whereHas('slipGajiDetail', function ($query) use ($headerId) {
                $query->where('header_id', $headerId);
            })
            ->selectRaw('
                COUNT(*) as total_emails,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent_emails,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_emails,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_emails,
                SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing_emails
            ')
            ->first();

            return [
                'total_emails' => $stats->total_emails ?? 0,
                'sent_emails' => $stats->sent_emails ?? 0,
                'failed_emails' => $stats->failed_emails ?? 0,
                'pending_emails' => $stats->pending_emails ?? 0,
                'processing_emails' => $stats->processing_emails ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error('Error getting email stats', [
                'header_id' => $headerId,
                'error' => $e->getMessage(),
            ]);

            return [
                'total_emails' => 0,
                'sent_emails' => 0,
                'failed_emails' => 0,
                'pending_emails' => 0,
                'processing_emails' => 0,
            ];
        }
    }

    /**
     * Get email logs for a header with pagination
     */
    public function getEmailLogs(int $headerId, array $filters = []): array
    {
        try {
            $query = EmailLog::whereHas('slipGajiDetail', function ($query) use ($headerId) {
                $query->where('header_id', $headerId);
            })
            ->with(['slipGajiDetail.employee', 'slipGajiDetail.dosen']);

            // Filter by status
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            // Filter by search
            if (!empty($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('to_email', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('subject', 'like', '%' . $filters['search'] . '%')
                        ->orWhereHas('slipGajiDetail.employee', function ($subQ) use ($filters) {
                            $subQ->where('nama', 'like', '%' . $filters['search'] . '%');
                        })
                        ->orWhereHas('slipGajiDetail.dosen', function ($subQ) use ($filters) {
                            $subQ->where('nama', 'like', '%' . $filters['search'] . '%');
                        });
                });
            }

            // Order by latest first
            $query->orderBy('created_at', 'desc');

            // Get perPage from filters, default to 20
            $perPage = $filters['perPage'] ?? 20;
            $emailLogs = $query->paginate($perPage);

            return [
                'email_logs' => $emailLogs,
                'filters' => $filters,
            ];

        } catch (\Exception $e) {
            Log::error('Error getting email logs', [
                'header_id' => $headerId,
                'error' => $e->getMessage(),
            ]);

            return [
                'email_logs' => collect(),
                'filters' => $filters,
            ];
        }
    }

    /**
     * Retry failed emails for a header
     */
    public function retryFailedEmails(int $headerId): array
    {
        try {
            $failedEmailLogs = EmailLog::whereHas('slipGajiDetail', function ($query) use ($headerId) {
                $query->where('header_id', $headerId);
            })
            ->where('status', 'failed')
            ->with(['slipGajiDetail'])
            ->get();

            $retryCount = 0;

            foreach ($failedEmailLogs as $emailLog) {
                // Reset status to pending
                $emailLog->update([
                    'status' => 'pending',
                    'error_message' => null,
                    'sent_at' => null,
                ]);

                // Dispatch job again
                SendSlipGajiEmailJob::dispatch($emailLog->slipGajiDetail, $emailLog)
                    ->onQueue('emails')
                    ->delay(now()->addSeconds(rand(1, 30)));

                $retryCount++;
            }

            Log::info('Retried failed emails', [
                'header_id' => $headerId,
                'retry_count' => $retryCount,
                'user_id' => Auth::id(),
            ]);

            return [
                'success' => true,
                'message' => "$retryCount email gagal berhasil dijadwalkan ulang",
                'retry_count' => $retryCount,
            ];

        } catch (\Exception $e) {
            Log::error('Error retrying failed emails', [
                'header_id' => $headerId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengulangi pengiriman email: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get the recipient email address for a detail
     */
    private function getRecipientEmail(SlipGajiDetail $detail): ?string
    {
        // Priority: email kampus > email pribadi
        if ($detail->employee) {
            return $detail->employee->email_kampus ?: $detail->employee->email;
        }

        if ($detail->dosen) {
            return $detail->dosen->email_kampus ?: $detail->dosen->email;
        }

        return null;
    }

    /**
     * Get the employee name for a detail
     */
    private function getEmployeeName(SlipGajiDetail $detail): string
    {
        if ($detail->employee) {
            return trim(($detail->employee->gelar_depan ? $detail->employee->gelar_depan . ' ' : '') . 
                   $detail->employee->nama_lengkap . 
                   ($detail->employee->gelar_belakang ? ', ' . $detail->employee->gelar_belakang : ''));
        }

        if ($detail->dosen) {
            return trim(($detail->dosen->gelar_depan ? $detail->dosen->gelar_depan . ' ' : '') . 
                   $detail->dosen->nama . 
                   ($detail->dosen->gelar_belakang ? ', ' . $detail->dosen->gelar_belakang : ''));
        }

        return 'Karyawan';
    }
}
