<?php

namespace App\Livewire\Superadmin;

use App\Models\SmtpSetting;
use App\Services\GmailSmtpService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SmtpSetup extends Component
{
    public $activeTab = 'setting'; // Default to setting tab
    public $gmailEmail = '';
    public $gmailPassword = '';
    public $passwordSaved = false;
    public $gmailFromName = 'USBYPKP System';
    public $isActive = true;
    
    // Test email form
    public $testEmail = '';
    public $testSubject = 'Test Email dari USBYPKP';
    public $testMessage = 'Ini adalah email test untuk verifikasi konfigurasi Gmail SMTP.';
    
    // Loading states
    public $isTesting = false;
    public $isSending = false;
    public $isSaving = false;
    
    // Messages
    public $connectionMessage = '';
    public $connectionStatus = '';
    public $emailMessage = '';
    public $emailStatus = '';
    public $debugMessage = '';
    public $debugStatus = '';
    public $showDebug = false;
    
    // No email log properties since it's moved to separate menu
    
    protected $rules = [
        'gmailEmail' => 'required|email',
        'gmailPassword' => 'required',
        'gmailFromName' => 'required|string',
        'testEmail' => 'required|email'
    ];
    
    protected $messages = [
        'gmailEmail.required' => 'Email Gmail wajib diisi',
        'gmailEmail.email' => 'Format email tidak valid',
        'gmailPassword.required' => 'Password Gmail wajib diisi',
        'gmailFromName.required' => 'From name wajib diisi',
        'testEmail.required' => 'Email test wajib diisi',
        'testEmail.email' => 'Format email test tidak valid',
    ];
    
    // Custom validation messages for test connection
    public function messages()
    {
        return [
            'gmailEmail.required' => 'Email Gmail wajib diisi',
            'gmailEmail.email' => 'Format email tidak valid',
            'gmailPassword.required' => 'Password Gmail wajib diisi (kosongkan jika ingin menggunakan password tersimpan)',
            'gmailFromName.required' => 'From name wajib diisi',
            'testEmail.required' => 'Email test wajib diisi',
            'testEmail.email' => 'Format email test tidak valid',
        ];
    }
    
    public function mount()
    {
        $this->loadConfiguration();
    }
    
    public function loadConfiguration()
    {
        $config = SmtpSetting::getActive();
        if ($config) {
            $this->gmailEmail = $config->gmail_email;
            $this->gmailFromName = $config->gmail_from_name;
            $this->isActive = $config->is_active;
            $this->passwordSaved = true; // Tandai bahwa password sudah tersimpan
            // Password tidak di-load untuk security
        }
    }
    
    public function save()
    {
        $rules = [
            'gmailEmail' => 'required|email',
            'gmailFromName' => 'required|string'
        ];
        
        // Password hanya wajib jika belum ada password tersimpan atau jika user mengisi password baru
        if (!$this->passwordSaved || !empty($this->gmailPassword)) {
            $rules['gmailPassword'] = 'required';
        }
        
        $this->validate($rules);
        
        $this->isSaving = true;
        
        try {
            // Jika password kosong dan sudah ada password tersimpan, gunakan password yang ada
            $passwordToSave = $this->gmailPassword;
            if (empty($this->gmailPassword) && $this->passwordSaved) {
                // Jangan update password, biarkan password yang ada di database
                $config = SmtpSetting::getActive();
                if ($config) {
                    $passwordToSave = $config->gmail_password; // Ini akan tetap terenkripsi
                }
            }
            
            $service = new GmailSmtpService();
            $result = $service->saveConfig([
                'gmail_email' => $this->gmailEmail,
                'gmail_password' => $passwordToSave,
                'gmail_from_name' => $this->gmailFromName,
                'is_active' => $this->isActive
            ]);
            
            if ($result['success']) {
                $this->passwordSaved = true; // Tandai bahwa password sudah tersimpan
                session()->flash('success', 'Konfigurasi Gmail SMTP berhasil disimpan');
            } else {
                session()->flash('error', $result['message']);
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan konfigurasi: ' . $e->getMessage());
        }
        
        $this->isSaving = false;
    }
    
    public function testConnection()
    {
        $rules = [
            'gmailEmail' => 'required|email',
            'gmailFromName' => 'required'
        ];
        
        // Password hanya wajib jika belum ada password tersimpan atau jika user mengisi password baru
        if (!$this->passwordSaved || !empty($this->gmailPassword)) {
            $rules['gmailPassword'] = 'required';
        }
        
        $this->validate($rules);
        
        $this->isTesting = true;
        $this->connectionMessage = '';
        $this->connectionStatus = '';
        
        try {
            // Create temporary config object for testing
            $tempConfig = new \stdClass();
            $tempConfig->gmail_email = $this->gmailEmail;
            $tempConfig->gmail_from_name = $this->gmailFromName;
            
            // Gunakan password yang diinput atau password tersimpan dari database
            if (!empty($this->gmailPassword)) {
                $tempConfig->gmail_password = $this->gmailPassword;
            } elseif ($this->passwordSaved) {
                // Ambil password tersimpan dari database
                $config = SmtpSetting::getActive();
                if ($config) {
                    $tempConfig->gmail_password = $config->gmail_password; // Ini sudah ter-decrypt oleh accessor
                } else {
                    throw new \Exception('Tidak dapat menemukan konfigurasi SMTP aktif di database');
                }
            } else {
                throw new \Exception('Password Gmail wajib diisi untuk test koneksi');
            }
            
            $service = new GmailSmtpService();
            $result = $service->testConnectionWithData($tempConfig);
            
            $this->connectionMessage = $result['message'];
            $this->connectionStatus = $result['success'] ? 'success' : 'error';
            
        } catch (\Exception $e) {
            $this->connectionMessage = 'Test gagal: ' . $e->getMessage();
            $this->connectionStatus = 'error';
        }
        
        $this->isTesting = false;
    }
    
    public function sendTestEmail()
    {
        $this->validate(['testEmail' => 'required|email']);
        
        $this->isSending = true;
        $this->emailMessage = '';
        $this->emailStatus = '';
        
        try {
            // Get active config from database
            $config = SmtpSetting::getActive();
            if (!$config) {
                throw new Exception('Tidak ada konfigurasi SMTP aktif ditemukan. Silakan atur konfigurasi SMTP di tab Setting terlebih dahulu.');
            }
            
            $service = new GmailSmtpService();
            $result = $service->sendTestEmail($this->testEmail, $this->testSubject, $this->testMessage);
            
            $this->emailMessage = $result['message'];
            $this->emailStatus = $result['success'] ? 'success' : 'error';
            
        } catch (\Exception $e) {
            $this->emailMessage = 'Gagal mengirim email test: ' . $e->getMessage();
            $this->emailStatus = 'error';
        }
        
        $this->isSending = false;
    }
    
    // No email log methods since it's moved to separate menu
    
    public function toggleDebug()
    {
        $this->showDebug = !$this->showDebug;
        if ($this->showDebug) {
            $this->runDebug();
        }
    }
    
    public function runDebug()
    {
        try {
            $service = new GmailSmtpService();
            $result = $service->debugSmtpConfig();
            
            if ($result['success']) {
                $this->debugMessage = json_encode($result, JSON_PRETTY_PRINT);
                $this->debugStatus = 'success';
            } else {
                $this->debugMessage = $result['message'];
                $this->debugStatus = 'error';
            }
            
        } catch (\Exception $e) {
            $this->debugMessage = 'Debug gagal: ' . $e->getMessage();
            $this->debugStatus = 'error';
        }
    }
    
    public function render()
    {
        // No email log data since it's moved to separate menu
        return view('livewire.superadmin.smtp-setup');
    }
}
