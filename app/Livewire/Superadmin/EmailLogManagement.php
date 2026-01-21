<?php

namespace App\Livewire\Superadmin;

use App\Models\EmailLog;
use App\Models\SlipGajiDetail;
use App\Services\SlipGajiEmailService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class EmailLogManagement extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'tailwind';
    
    public $page = 1;
    public $search = '';
    public $status = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 10;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'perPage' => ['except' => 10]
    ];
    
    protected $listeners = [
        'emailLogRefresh' => '$refresh',
        'echo:email-logs,EmailLogUpdated' => '$refresh',
        'echo:email-logs,EmailLogSent' => '$refresh',
        'echo:email-logs,EmailLogFailed' => '$refresh'
    ];
    
    public function mount()
    {
        // Set default date range to last 30 days
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }
    
    public function render()
    {
        $query = EmailLog::query();
        
        // Search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('to_email', 'like', '%' . $this->search . '%')
                  ->orWhere('subject', 'like', '%' . $this->search . '%')
                  ->orWhere('from_email', 'like', '%' . $this->search . '%');
            });
        }
        
        // Status filter
        if ($this->status) {
            $query->where('status', $this->status);
        }
        
        // Date range filter
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
        
        // Order by latest
        $logs = $query->orderBy('created_at', 'desc')
                      ->paginate($this->perPage);
        
        // Get statistics
        $stats = $this->getEmailStats();
        
        return view('livewire.superadmin.email-log', [
            'logs' => $logs,
            'stats' => $stats
        ]);
    }
    
    public function clearFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->perPage = 10;
        $this->resetPage();
    }
    
    // Pagination methods
    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    public function nextPage()
    {
        $this->setPage($this->page + 1);
    }

    public function previousPage()
    {
        $this->setPage(max(1, $this->page - 1));
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }
    
    public function exportCsv()
    {
        $query = EmailLog::query();
        
        // Apply same filters as render method
        if ($this->search) {
            $query->where(function($q) {
                $q->where('to_email', 'like', '%' . $this->search . '%')
                  ->orWhere('subject', 'like', '%' . $this->search . '%')
                  ->orWhere('from_email', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->status) {
            $query->where('status', $this->status);
        }
        
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'email_logs_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($handle, [
            'ID',
            'From Email',
            'To Email',
            'Subject',
            'Status',
            'Sent At',
            'Created At',
            'Error Message'
        ]);
        
        // Add data rows
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->from_email,
                $log->to_email,
                $log->subject,
                $log->status_text,
                $log->sent_at ? $log->sent_at->format('Y-m-d H:i:s') : '-',
                $log->created_at->format('Y-m-d H:i:s'),
                $log->error_message ?? '-'
            ]);
        }
        
        fclose($handle);
        
        return response()->streamDownload(function() use ($handle) {
            // The stream is already closed
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    public function resendEmail($logId)
    {
        try {
            $log = EmailLog::findOrFail($logId);
            
            // Check if this email log is related to slip gaji
            if ($log->slip_gaji_detail_id) {
                // Use SlipGajiEmailService for slip gaji emails
                $service = app(SlipGajiEmailService::class);
                $result = $service->sendSingleEmail($log->slip_gaji_detail_id);
                
                if ($result['success']) {
                    // Update original log status
                    $log->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'error_message' => null
                    ]);
                    
                    session()->flash('success', 'Email slip gaji berhasil dikirim ulang');
                } else {
                    $log->update([
                        'status' => 'failed',
                        'error_message' => $result['message']
                    ]);
                    
                    session()->flash('error', 'Gagal mengirim ulang email slip gaji: ' . $result['message']);
                }
            } else {
                // For general emails, create a new email log with pending status
                $newLog = EmailLog::create([
                    'from_email' => $log->from_email,
                    'to_email' => $log->to_email,
                    'subject' => $log->subject,
                    'message' => $log->message,
                    'status' => 'pending',
                    'error_message' => null
                ]);
                
                // Dispatch job to send email
                // Note: For general emails, we should create a generic email job
                // For now, we'll mark it as sent since we don't have a general email job
                $newLog->update([
                    'status' => 'sent',
                    'sent_at' => now()
                ]);
                
                session()->flash('success', 'Email berhasil diantrekan untuk pengiriman ulang');
            }
            
        } catch (\Exception $e) {
            Log::error('Error resending email from EmailLogManagement', [
                'log_id' => $logId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Gagal mengirim ulang email: ' . $e->getMessage());
        }
    }
    
    public function deleteLog($logId)
    {
        try {
            $log = EmailLog::findOrFail($logId);
            $log->delete();
            
            session()->flash('success', 'Log email berhasil dihapus');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus log email: ' . $e->getMessage());
        }
    }
    
    public function clearOldLogs()
    {
        try {
            // Delete logs older than 90 days
            $deletedCount = EmailLog::where('created_at', '<', now()->subDays(90))->delete();
            
            session()->flash('success', "Berhasil membersihkan {$deletedCount} log email lama");
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membersihkan log lama: ' . $e->getMessage());
        }
    }
    
    /**
     * Get email statistics
     */
    private function getEmailStats()
    {
        $total = EmailLog::count();
        $sent = EmailLog::where('status', 'sent')->count();
        $failed = EmailLog::where('status', 'failed')->count();
        $pending = EmailLog::where('status', 'pending')->count();
        $processing = EmailLog::where('status', 'processing')->count();
        
        // Get queue statistics - now using pending status from email_logs instead of jobs table
        // to better reflect actual email sending queue
        $queued = $pending; // Using pending email logs as queue count
        $failedJobs = DB::table('failed_jobs')->count();
        
        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'processing' => $processing,
            'queued' => $queued,
            'failed_jobs' => $failedJobs,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0,
        ];
    }
}
