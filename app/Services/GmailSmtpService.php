<?php

namespace App\Services;

use App\Models\SmtpSetting;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Exception;

class GmailSmtpService
{
    /**
     * Get active Gmail configuration
     */
    public function getActiveConfig()
    {
        return SmtpSetting::getActive();
    }

    /**
     * Test Gmail connection
     */
    public function testConnection()
    {
        try {
            $config = $this->getActiveConfig();
            if (!$config) {
                throw new Exception('Tidak ada konfigurasi SMTP aktif ditemukan');
            }

            // Configure mail for testing
            $this->applyConfig($config);

            // Test connection by actually trying to establish a connection
            $mailer = app('mailer');
            $transport = $mailer->getSymfonyTransport();
            
            // Try to start and stop the transport to test connection
            $transport->start();
            $transport->stop();

            return [
                'success' => true, 
                'message' => 'Koneksi Gmail SMTP berhasil'
            ];

        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => 'Koneksi gagal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test Gmail connection with provided data
     */
    public function testConnectionWithData($config)
    {
        try {
            if (!$config || !isset($config->gmail_email) || !isset($config->gmail_password)) {
                throw new Exception('Data konfigurasi SMTP tidak lengkap');
            }

            // Temporarily apply the test configuration using Laravel 12 structure
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', 'smtp.gmail.com');
            Config::set('mail.mailers.smtp.port', 587);
            Config::set('mail.mailers.smtp.encryption', 'tls');
            Config::set('mail.mailers.smtp.username', $config->gmail_email);
            Config::set('mail.mailers.smtp.password', $config->gmail_password);
            Config::set('mail.mailers.smtp.timeout', 30);
            Config::set('mail.mailers.smtp.local_domain', null);
            
            // Set from address
            Config::set('mail.from.address', $config->gmail_email);
            Config::set('mail.from.name', $config->gmail_from_name ?? 'USBYPKP System');
            
            // Clear the mailer instance to force reload with new config
            app()->forgetInstance('mailer');
            app()->forgetInstance('mail.manager');

            // Test connection by actually trying to establish a connection
            $mailer = app('mailer');
            $transport = $mailer->getSymfonyTransport();
            
            // Try to start and stop the transport to test connection
            $transport->start();
            $transport->stop();

            return [
                'success' => true, 
                'message' => 'Koneksi Gmail SMTP berhasil'
            ];

        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => 'Koneksi gagal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send test email
     */
    public function sendTestEmail($toEmail, $subject = 'Test Email', $message = 'This is a test email')
    {
        try {
            $config = $this->getActiveConfig();
            if (!$config) {
                throw new Exception('Tidak ada konfigurasi SMTP aktif ditemukan');
            }

            // Create email log
            $emailLog = EmailLog::create([
                'from_email' => $config->gmail_email,
                'to_email' => $toEmail,
                'subject' => $subject,
                'message' => $message,
                'status' => 'pending'
            ]);

            // Configure mail
            $this->applyConfig($config);

            // Send email with better error handling
            Mail::html($this->createTestEmailHtml($message), function ($mail) use ($toEmail, $subject, $config) {
                $mail->to($toEmail)
                     ->subject($subject)
                     ->from($config->gmail_email, $config->gmail_from_name)
                     ->priority(1); // High priority
            });

            // Update log status
            $emailLog->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

            return [
                'success' => true, 
                'message' => 'Email test berhasil dikirim ke ' . $toEmail . '. Silakan periksa inbox dan folder spam.',
                'log_id' => $emailLog->id
            ];

        } catch (Exception $e) {
            // Update log status to failed
            if (isset($emailLog)) {
                $emailLog->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }

            // Log detailed error for debugging
            \Log::error('SMTP Test Email Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to_email' => $toEmail,
                'subject' => $subject
            ]);

            return [
                'success' => false, 
                'message' => 'Gagal mengirim email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create HTML content for test email
     */
    private function createTestEmailHtml($message)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Test Email</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                }
                .content {
                    background-color: #ffffff;
                    padding: 30px;
                    border: 1px solid #e9ecef;
                    border-top: none;
                    border-radius: 0 0 5px 5px;
                }
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    font-size: 12px;
                    color: #6c757d;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Test Email dari USBYPKP</h2>
            </div>
            <div class="content">
                <p><strong>Waktu:</strong> ' . now()->format('d/m/Y H:i:s') . '</p>
                <p><strong>Pesan:</strong></p>
                <p>' . nl2br(htmlspecialchars($message)) . '</p>
                <hr>
                <p><em>Ini adalah email test untuk verifikasi konfigurasi Gmail SMTP.</em></p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' USBYPKP System. All rights reserved.</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Send email with logging
     */
    public function sendEmail($toEmail, $subject, $message, $attachments = [])
    {
        try {
            $config = $this->getActiveConfig();
            if (!$config) {
                throw new Exception('Tidak ada konfigurasi SMTP aktif ditemukan');
            }

            // Create email log
            $emailLog = EmailLog::create([
                'from_email' => $config->gmail_email,
                'to_email' => $toEmail,
                'subject' => $subject,
                'message' => $message,
                'status' => 'pending'
            ]);

            // Configure mail
            $this->applyConfig($config);

            // Send email with better error handling
            Mail::html($message, function ($mail) use ($toEmail, $subject, $attachments, $config) {
                $mail->to($toEmail)
                     ->subject($subject)
                     ->from($config->gmail_email, $config->gmail_from_name)
                     ->priority(1); // High priority

                // Add attachments if any
                foreach ($attachments as $attachment) {
                    if (isset($attachment['path']) && isset($attachment['name'])) {
                        $mail->attach($attachment['path'], [
                            'as' => $attachment['name'],
                            'mime' => $attachment['mime'] ?? 'application/octet-stream'
                        ]);
                    }
                }
            });

            // Update log status
            $emailLog->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Email berhasil dikirim ke ' . $toEmail,
                'log_id' => $emailLog->id
            ];

        } catch (Exception $e) {
            // Update log status to failed
            if (isset($emailLog)) {
                $emailLog->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }

            // Log detailed error for debugging
            \Log::error('SMTP Email Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to_email' => $toEmail,
                'subject' => $subject
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Debug SMTP configuration and connection
     */
    public function debugSmtpConfig()
    {
        try {
            $config = $this->getActiveConfig();
            if (!$config) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada konfigurasi SMTP aktif ditemukan'
                ];
            }

            // Get current mail configuration
            $mailConfig = [
                'mailer' => config('mail.mailer'),
                'host' => config('mail.host'),
                'port' => config('mail.port'),
                'encryption' => config('mail.encryption'),
                'username' => config('mail.username'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ];

            // Test actual connection
            $this->applyConfig($config);
            $mailer = app('mailer');
            $transport = $mailer->getSymfonyTransport();

            return [
                'success' => true,
                'message' => 'Debug informasi SMTP',
                'config' => $mailConfig,
                'db_config' => [
                    'email' => $config->gmail_email,
                    'from_name' => $config->gmail_from_name,
                    'has_password' => !empty($config->gmail_password),
                ],
                'transport_class' => get_class($transport),
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Debug gagal: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Apply Gmail configuration to system
     */
    public function applyConfig($config = null)
    {
        if (!$config) {
            $config = $this->getActiveConfig();
        }

        if ($config) {
            // Laravel 12 configuration structure
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', 'smtp.gmail.com');
            Config::set('mail.mailers.smtp.port', 587);
            Config::set('mail.mailers.smtp.encryption', 'tls');
            Config::set('mail.mailers.smtp.username', $config->gmail_email);
            Config::set('mail.mailers.smtp.password', $config->gmail_password);
            Config::set('mail.mailers.smtp.timeout', 30);
            Config::set('mail.mailers.smtp.local_domain', null);
            
            // Set from address
            Config::set('mail.from.address', $config->gmail_email);
            Config::set('mail.from.name', $config->gmail_from_name);
            
            // Clear the mailer instance to force reload with new config
            app()->forgetInstance('mailer');
            app()->forgetInstance('mail.manager');
        }
    }

    /**
     * Save SMTP configuration
     */
    public function saveConfig($data)
    {
        try {
            // Validate required fields
            if (!isset($data['gmail_email']) || !isset($data['gmail_password']) || !isset($data['gmail_from_name'])) {
                throw new Exception('Semua field wajib diisi');
            }

            // Update or create configuration
            $config = SmtpSetting::updateOrCreate(
                ['id' => 1], // Always use ID 1 for single configuration
                [
                    'gmail_email' => $data['gmail_email'],
                    'gmail_password' => $data['gmail_password'],
                    'gmail_from_name' => $data['gmail_from_name'],
                    'is_active' => $data['is_active'] ?? true
                ]
            );

            return [
                'success' => true,
                'message' => 'Konfigurasi SMTP berhasil disimpan',
                'config' => $config
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menyimpan konfigurasi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get email statistics
     */
    public function getEmailStats()
    {
        return [
            'total' => EmailLog::count(),
            'sent' => EmailLog::sent()->count(),
            'failed' => EmailLog::failed()->count(),
            'pending' => EmailLog::pending()->count(),
            'today' => EmailLog::whereDate('created_at', today())->count(),
            'this_week' => EmailLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => EmailLog::whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * Get recent email logs
     */
    public function getRecentEmails($limit = 10)
    {
        return EmailLog::with([]) // No relationships needed for basic display
                         ->orderBy('created_at', 'desc')
                         ->limit($limit)
                         ->get();
    }

    /**
     * Clear old email logs (optional cleanup)
     */
    public function clearOldLogs($days = 90)
    {
        try {
            $deleted = EmailLog::where('created_at', '<', now()->subDays($days))->delete();
            
            return [
                'success' => true,
                'message' => "Berhasil menghapus {$deleted} log email lama",
                'deleted_count' => $deleted
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menghapus log email lama: ' . $e->getMessage()
            ];
        }
    }
}
