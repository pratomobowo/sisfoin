# Dokumentasi Lengkap - SMTP Gmail Setup untuk USBYPKP System

## Table of Contents
1. [Overview](#overview)
2. [Features](#features)
3. [System Architecture](#system-architecture)
4. [Installation & Setup](#installation--setup)
5. [Configuration](#configuration)
6. [Usage Guide](#usage-guide)
7. [Troubleshooting](#troubleshooting)
8. [API Reference](#api-reference)
9. [Database Schema](#database-schema)
10. [Security Considerations](#security-considerations)
11. [Best Practices](#best-practices)
12. [Changelog](#changelog)

## Overview

Sistem SMTP Gmail Setup adalah komponen krusial dalam USBYPKP System yang bertanggung jawab untuk mengelola konfigurasi dan pengiriman email melalui server Gmail SMTP. Sistem ini dirancang khusus untuk Laravel 12 dengan mempertimbangkan compatibility, performance, dan security.

### Key Benefits
- **Laravel 12 Compatible**: Dioptimalkan untuk Laravel 12 dengan configuration structure yang tepat
- **Real-time Testing**: Kemampuan test koneksi SMTP secara real-time
- **Comprehensive Logging**: Sistem logging yang detail untuk monitoring dan debugging
- **User-friendly Interface**: Interface yang intuitif untuk manajemen konfigurasi
- **Secure Implementation**: Penyimpanan password yang terenkripsi dan secure handling

### Target Users
- **Superadmin**: Untuk konfigurasi dan monitoring sistem email
- **System Administrators**: Untuk maintenance dan troubleshooting
- **Developers**: Untuk integration dan custom development

## Features

### 1. SMTP Configuration Management
- **Dynamic Configuration**: Kemampuan mengubah konfigurasi SMTP tanpa restart aplikasi
- **Multiple Environment Support**: Support untuk development, staging, dan production
- **Configuration Validation**: Validasi otomatis untuk memastikan konfigurasi valid
- **Password Security**: Penyimpanan password terenkripsi dengan opsi update tanpa mengubah existing password

### 2. Connection Testing
- **Real-time Connection Test**: Test koneksi SMTP secara real-time
- **Comprehensive Diagnostics**: Informasi detail tentang status koneksi
- **Error Handling**: Error message yang spesifik dan actionable
- **Transport Verification**: Verifikasi bahwa SMTP transport digunakan dengan benar

### 3. Email Testing
- **Test Email Sending**: Kemampuan mengirim email test untuk verifikasi
- **HTML Email Template**: Template HTML yang professional dan responsive
- **Attachment Support**: Support untuk attachment dalam email test
- **Delivery Confirmation**: Konfirmasi pengiriman dengan logging yang detail

### 4. Debug & Monitoring
- **Debug Information**: Informasi debug yang komprehensif untuk troubleshooting
- **Configuration Inspector**: Tool untuk inspect konfigurasi yang sedang aktif
- **Transport Class Verification**: Verifikasi transport class yang digunakan
- **Real-time Configuration Display**: Display konfigurasi real-time

### 5. Logging & Analytics
- **Email Logging**: Logging untuk semua email yang dikirim
- **Status Tracking**: Tracking status (pending, sent, failed)
- **Performance Metrics**: Metrics untuk delivery rate dan failure analysis
- **Error Logging**: Detailed error logging untuk debugging

## System Architecture

### Core Components

#### 1. GmailSmtpService (Service Layer)
```php
namespace App\Services;

class GmailSmtpService
{
    // Main service for SMTP operations
}
```

**Responsibilities:**
- SMTP configuration management
- Connection testing
- Email sending
- Debug information generation
- Configuration validation

#### 2. SmtpSetting (Model)
```php
namespace App\Models;

class SmtpSetting extends Model
{
    // Model for storing SMTP configuration
}
```

**Responsibilities:**
- Database interaction for SMTP settings
- Configuration retrieval and storage
- Active configuration management

#### 3. EmailLog (Model)
```php
namespace App\Models;

class EmailLog extends Model
{
    // Model for email logging
}
```

**Responsibilities:**
- Email activity logging
- Status tracking
- Error message storage
- Analytics data provision

#### 4. SmtpSetup (Livewire Component)
```php
namespace App\Livewire\Superadmin;

class SmtpSetup extends Component
{
    // UI component for SMTP management
}
```

**Responsibilities:**
- User interface rendering
- Form handling
- Real-time updates
- User interaction management

### Data Flow

```
User Interface (Livewire)
        ↓
SmtpSetup Component
        ↓
GmailSmtpService
        ↓
Configuration Models
        ↓
Laravel Mail System
        ↓
Gmail SMTP Server
        ↓
Email Recipient
```

### Configuration Flow

```
Database → SmtpSetting → GmailSmtpService → Laravel Config → Mail System
```

## Installation & Setup

### Prerequisites
- PHP 8.1+
- Laravel 12
- MySQL 8.0+
- Gmail Account dengan 2FA (disarankan)
- App Password dari Google Account

### Step 1: Database Migration
```bash
php artisan migrate
```

Migrations yang dijalankan:
- `2025_09_25_170200_create_smtp_settings_table.php`
- `2025_09_25_170300_create_email_logs_table.php`

### Step 2: Model Generation
```bash
php artisan model:show SmtpSetting
php artisan model:show EmailLog
```

### Step 3: Service Registration
Service sudah terdaftar secara otomatis melalui Laravel's service container.

### Step 4: Route Configuration
Route sudah terkonfigurasi di `routes/web.php`:
```php
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/superadmin/smtp', SmtpSetup::class)->name('superadmin.smtp');
});
```

### Step 5: Menu Integration
Tambahkan menu item di navigation:
```html
<a href="{{ route('superadmin.smtp') }}" class="...">
    <i class="fas fa-envelope"></i>
    SMTP Setup
</a>
```

## Configuration

### Gmail Account Setup

#### 1. Enable 2FA (Two-Factor Authentication)
- Kunjungi [Google Account Security](https://myaccount.google.com/security)
- Enable 2FA pada account Anda
- Selesahi setup process

#### 2. Generate App Password
- Kunjungi [App Passwords](https://myaccount.google.com/apppasswords)
- Pilih app: "Mail"
- Pilih device: "Other (Custom name)"
- Beri nama: "USBYPKP System"
- Generate dan copy password (16 karakter)

#### 3. SMTP Server Information
- **Host**: `smtp.gmail.com`
- **Port**: `587`
- **Encryption**: `TLS`
- **Authentication**: `Yes`

### System Configuration

#### 1. Basic Configuration
```php
// Format konfigurasi di database
[
    'gmail_email' => 'your-email@gmail.com',
    'gmail_password' => 'encrypted-app-password',
    'gmail_from_name' => 'USBYPKP System',
    'is_active' => true
]
```

#### 2. Laravel 12 Configuration Structure
```php
// Format yang digunakan di Laravel 12
Config::set('mail.default', 'smtp');
Config::set('mail.mailers.smtp.transport', 'smtp');
Config::set('mail.mailers.smtp.host', 'smtp.gmail.com');
Config::set('mail.mailers.smtp.port', 587);
Config::set('mail.mailers.smtp.encryption', 'tls');
Config::set('mail.mailers.smtp.username', 'your-email@gmail.com');
Config::set('mail.mailers.smtp.password', 'app-password');
Config::set('mail.from.address', 'your-email@gmail.com');
Config::set('mail.from.name', 'USBYPKP System');
```

#### 3. Environment Variables (Optional)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Usage Guide

### 1. Accessing SMTP Setup

#### Login sebagai Superadmin
1. Login ke USBYPKP System
2. Pastikan memiliki role "superadmin"
3. Akses menu Superadmin → SMTP Setup

#### Interface Overview
- **Tab Navigation**: Setting dan Test Email
- **Configuration Form**: Form untuk input konfigurasi SMTP
- **Test Controls**: Buttons untuk test koneksi dan kirim email
- **Debug Information**: Panel debug untuk troubleshooting

### 2. SMTP Configuration

#### Step 1: Navigate to Setting Tab
```html
<button wire:click="$set('activeTab', 'setting')">
    Setting
</button>
```

#### Step 2: Fill Configuration Form
- **Alamat Email Gmail**: Email Gmail atau Google Workspace
- **Password Gmail / App Password**: App password 16 karakter
- **Nama Pengirim**: Nama yang akan muncul sebagai sender
- **Aktifkan konfigurasi**: Checkbox untuk mengaktifkan konfigurasi

#### Step 3: Save Configuration
```php
// Method yang dipanggil
public function save()
{
    // Validation logic
    // Password handling
    // Database storage
}
```

**Password Handling:**
- Password disimpan terenkripsi di database
- Jika password sudah ada, field bisa dikosongkan untuk mempertahankan password existing
- Hanya update password jika field diisi

#### Step 4: Test Connection
```php
// Method untuk test koneksi
public function testConnection()
{
    // Apply configuration
    // Test SMTP connection
    // Return result
}
```

**Expected Results:**
- ✅ **Success**: "Koneksi Gmail SMTP berhasil"
- ❌ **Failed**: Error message spesifik (authentication failed, connection timeout, etc.)

### 3. Email Testing

#### Step 1: Navigate to Test Email Tab
```html
<button wire:click="$set('activeTab', 'test')">
    Test Email
</button>
```

#### Step 2: Fill Test Email Form
- **Email Tujuan Test**: Email address untuk menerima test email
- **Subject**: Subject email test
- **Pesan**: Content email test

#### Step 3: Send Test Email
```php
// Method untuk kirim email test
public function sendTestEmail()
{
    // Configuration validation
    // Email sending
    // Logging
    // Return result
}
```

**Email Template:**
```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test Email</title>
    <style>
        /* Professional email styling */
    </style>
</head>
<body>
    <div class="header">
        <h2>Test Email dari USBYPKP</h2>
    </div>
    <div class="content">
        <p><strong>Waktu:</strong> 25/09/2025 19:09:42</p>
        <p><strong>Pesan:</strong> Ini adalah email test...</p>
        <hr>
        <p><em>Ini adalah email test untuk verifikasi konfigurasi Gmail SMTP.</em></p>
    </div>
    <div class="footer">
        <p>&copy; 2025 USBYPKP System. All rights reserved.</p>
    </div>
</body>
</html>
```

**Expected Results:**
- ✅ **Success**: "Email test berhasil dikirim ke [email]. Silakan periksa inbox dan folder spam."
- ❌ **Failed**: Error message dengan detail teknis

### 4. Debug & Troubleshooting

#### Step 1: Enable Debug Mode
```html
<button wire:click="toggleDebug">
    Debug Info
</button>
```

#### Step 2: Analyze Debug Information
```json
{
    "success": true,
    "message": "Debug informasi SMTP",
    "config": {
        "mailer": "smtp",
        "host": "smtp.gmail.com",
        "port": 587,
        "encryption": "tls",
        "username": "remunerasi@usbypkp.ac.id",
        "from_address": "remunerasi@usbypkp.ac.id",
        "from_name": "Remunerasi USBYPKP"
    },
    "db_config": {
        "email": "remunerasi@usbypkp.ac.id",
        "from_name": "Remunerasi USBYPKP",
        "has_password": true
    },
    "transport_class": "Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport"
}
```

#### Step 3: Common Issues & Solutions

**Issue 1: Config Values Null**
```json
{
    "mailer": null,
    "host": null,
    "port": null
}
```
**Solution**: Check `applyConfig()` method and ensure Laravel 12 compatibility.

**Issue 2: Wrong From Address**
```json
{
    "from_address": "hello@example.com"
}
```
**Solution**: Verify `mail.from` configuration is properly set.

**Issue 3: LogTransport Instead of SMTP**
```json
{
    "transport_class": "Illuminate\\Mail\\Transport\\LogTransport"
}
```
**Solution**: Ensure `mail.default` is set to 'smtp' and force reload mailer instances.

### 5. Email Logs Monitoring

#### Access Email Logs
1. Navigate to menu: Superadmin → Email Log
2. View list of email activities
3. Filter by status, date, or recipient

#### Log Information
- **From Email**: Sender email address
- **To Email**: Recipient email address
- **Subject**: Email subject
- **Status**: pending, sent, failed
- **Sent At**: Timestamp when email was sent
- **Error Message**: Error details if failed

## Troubleshooting

### Common Issues & Solutions

#### 1. Authentication Failed
**Error Message**: "535-5.7.8 Username and Password not accepted"

**Causes:**
- Wrong username or password
- Using regular password instead of App Password
- 2FA not enabled
- App Password not generated correctly

**Solutions:**
1. Enable 2FA on Google Account
2. Generate new App Password
3. Use App Password (16 characters) instead of regular password
4. Verify email address is correct

#### 2. Connection Timeout
**Error Message**: "Connection timed out"

**Causes:**
- Network connectivity issues
- Firewall blocking SMTP port
- Gmail SMTP server unreachable

**Solutions:**
1. Check internet connection
2. Verify port 587 is not blocked
3. Test telnet to smtp.gmail.com:587
4. Check firewall settings

#### 3. SSL/TLS Handshake Failed
**Error Message**: "SSL handshake failed"

**Causes:**
- OpenSSL version issues
- Certificate problems
- Encryption mismatch

**Solutions:**
1. Update OpenSSL
2. Verify PHP SSL configuration
3. Ensure TLS encryption is used
4. Check system certificates

#### 4. Configuration Not Applied
**Debug Info Shows**: Null values for config

**Causes:**
- Laravel configuration caching
- Service container caching
- Wrong configuration structure

**Solutions:**
1. Clear configuration cache: `php artisan config:clear`
2. Clear application cache: `php artisan cache:clear`
3. Restart application
4. Verify Laravel 12 configuration structure

#### 5. Email Goes to Spam
**Issue**: Test email received but in spam folder

**Causes:**
- Missing SPF/DKIM records
- Poor email reputation
- Spam-like content
- Missing authentication

**Solutions:**
1. Set up SPF record for domain
2. Configure DKIM signing
3. Use professional email template
4. Verify domain authentication
5. Check email content for spam triggers

### Debug Steps

#### Step 1: Check Configuration
```bash
# Check Laravel configuration
php artisan tinker
>>> config('mail')
>>> exit
```

#### Step 2: Test SMTP Connection Manually
```bash
# Test telnet connection
telnet smtp.gmail.com 587

# Expected response
220 smtp.gmail.com ESMTP
```

#### Step 3: Check Logs
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check email logs
php artisan tinker
>>> App\Models\EmailLog::latest()->get()
>>> exit
```

#### Step 4: Verify Transport Class
```php
// In controller or service
$mailer = app('mailer');
$transport = $mailer->getSymfonyTransport();
echo get_class($transport);
// Should output: Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport
```

### Performance Optimization

#### 1. Configuration Caching
```bash
# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 2. Database Optimization
```php
// Add indexes to email logs table
Schema::table('email_logs', function (Blueprint $table) {
    $table->index('status');
    $table->index('created_at');
    $table->index('to_email');
});
```

#### 3. Queue Email Sending
```php
// Use queue for email sending
Mail::to($toEmail)->queue($mailable);
```

## API Reference

### GmailSmtpService

#### Methods

##### `getActiveConfig(): SmtpSetting|null`
Get active SMTP configuration from database.

**Returns:**
- `SmtpSetting|null`: Active configuration or null if not found

**Example:**
```php
$service = new GmailSmtpService();
$config = $service->getActiveConfig();
```

##### `testConnection(): array`
Test SMTP connection with current configuration.

**Returns:**
```php
[
    'success' => bool,
    'message' => string
]
```

**Example:**
```php
$result = $service->testConnection();
if ($result['success']) {
    echo "Connection successful";
} else {
    echo "Connection failed: " . $result['message'];
}
```

##### `testConnectionWithData($config): array`
Test SMTP connection with provided configuration data.

**Parameters:**
- `$config`: Configuration object with gmail_email and gmail_password

**Returns:**
```php
[
    'success' => bool,
    'message' => string
]
```

**Example:**
```php
$testConfig = new stdClass();
$testConfig->gmail_email = 'test@gmail.com';
$testConfig->gmail_password = 'app-password';
$testConfig->gmail_from_name = 'Test System';

$result = $service->testConnectionWithData($testConfig);
```

##### `sendTestEmail($toEmail, $subject, $message): array`
Send test email with logging.

**Parameters:**
- `$toEmail` (string): Recipient email address
- `$subject` (string): Email subject
- `$message` (string): Email content

**Returns:**
```php
[
    'success' => bool,
    'message' => string,
    'log_id' => int|null
]
```

**Example:**
```php
$result = $service->sendTestEmail(
    'recipient@example.com',
    'Test Email',
    'This is a test message'
);
```

##### `sendEmail($toEmail, $subject, $message, $attachments = []): array`
Send email with logging and attachment support.

**Parameters:**
- `$toEmail` (string): Recipient email address
- `$subject` (string): Email subject
- `$message` (string): Email content (HTML)
- `$attachments` (array): Array of attachments

**Returns:**
```php
[
    'success' => bool,
    'message' => string,
    'log_id' => int|null
]
```

**Example:**
```php
$attachments = [
    [
        'path' => '/path/to/file.pdf',
        'name' => 'document.pdf',
        'mime' => 'application/pdf'
    ]
];

$result = $service->sendEmail(
    'recipient@example.com',
    'Document Attached',
    '<p>Please find attached document</p>',
    $attachments
);
```

##### `debugSmtpConfig(): array`
Get debug information for SMTP configuration.

**Returns:**
```php
[
    'success' => bool,
    'message' => string,
    'config' => array,
    'db_config' => array,
    'transport_class' => string
]
```

**Example:**
```php
$debug = $service->debugSmtpConfig();
echo "Transport Class: " . $debug['transport_class'];
echo "Host: " . $debug['config']['host'];
```

##### `applyConfig($config = null): void`
Apply SMTP configuration to Laravel config system.

**Parameters:**
- `$config`: Configuration object or null to use active config

**Example:**
```php
$service->applyConfig(); // Use active config
// or
$service->applyConfig($customConfig); // Use specific config
```

##### `saveConfig($data): array`
Save SMTP configuration to database.

**Parameters:**
- `$data`: Array with configuration data

**Returns:**
```php
[
    'success' => bool,
    'message' => string,
    'config' => SmtpSetting|null
]
```

**Example:**
```php
$data = [
    'gmail_email' => 'system@usbypkp.ac.id',
    'gmail_password' => 'new-app-password',
    'gmail_from_name' => 'USBYPKP System',
    'is_active' => true
];

$result = $service->saveConfig($data);
```

##### `getEmailStats(): array`
Get email statistics.

**Returns:**
```php
[
    'total' => int,
    'sent' => int,
    'failed' => int,
    'pending' => int,
    'today' => int,
    'this_week' => int,
    'this_month' => int
]
```

**Example:**
```php
$stats = $service->getEmailStats();
echo "Success rate: " . ($stats['sent'] / $stats['total'] * 100) . "%";
```

##### `getRecentEmails($limit = 10): Collection`
Get recent email logs.

**Parameters:**
- `$limit` (int): Number of records to retrieve

**Returns:**
- `Collection`: Collection of EmailLog models

**Example:**
```php
$recentEmails = $service->getRecentEmails(5);
foreach ($recentEmails as $email) {
    echo $email->to_email . " - " . $email->status;
}
```

##### `clearOldLogs($days = 90): array`
Clear old email logs.

**Parameters:**
- `$days` (int): Keep logs newer than this many days

**Returns:**
```php
[
    'success' => bool,
    'message' => string,
    'deleted_count' => int
]
```

**Example:**
```php
$result = $service->clearOldLogs(30);
echo "Deleted " . $result['deleted_count'] . " old logs";
```

### SmtpSetting Model

#### Properties
- `id`: Primary key
- `gmail_email`: Gmail email address
- `gmail_password`: Encrypted Gmail password
- `gmail_from_name`: Sender name
- `is_active`: Configuration status
- `created_at`: Creation timestamp
- `updated_at`: Update timestamp

#### Methods

##### `getActive(): SmtpSetting|null`
Get active SMTP configuration.

**Example:**
```php
$config = SmtpSetting::getActive();
if ($config) {
    echo "Active config: " . $config->gmail_email;
}
```

### EmailLog Model

#### Properties
- `id`: Primary key
- `from_email`: Sender email address
- `to_email`: Recipient email address
- `subject`: Email subject
- `message`: Email content
- `status`: Email status (pending, sent, failed)
- `error_message`: Error message if failed
- `sent_at`: Sent timestamp
- `created_at`: Creation timestamp
- `updated_at`: Update timestamp

#### Scopes

##### `sent()`: Builder
Get sent emails.

**Example:**
```php
$sentEmails = EmailLog::sent()->get();
```

##### `failed()`: Builder
Get failed emails.

**Example:**
```php
$failedEmails = EmailLog::failed()->get();
```

##### `pending()`: Builder
Get pending emails.

**Example:**
```php
$pendingEmails = EmailLog::pending()->get();
```

## Database Schema

### smtp_settings Table

```sql
CREATE TABLE smtp_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gmail_email VARCHAR(255) NOT NULL,
    gmail_password TEXT NOT NULL,
    gmail_from_name VARCHAR(255) NOT NULL DEFAULT 'USBYPKP System',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Field Descriptions
- `id`: Unique identifier (auto-increment)
- `gmail_email`: Gmail or Google Workspace email address
- `gmail_password`: Encrypted App Password (16 characters)
- `gmail_from_name`: Display name for email sender
- `is_active`: Whether this configuration is active
- `created_at`: Record creation timestamp
- `updated_at`: Record last update timestamp

#### Indexes
- Primary key on `id`
- Recommended: Add index on `is_active` for faster queries

### email_logs Table

```sql
CREATE TABLE email_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_email VARCHAR(255) NOT NULL,
    to_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
    error_message TEXT NULL,
    sent_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Field Descriptions
- `id`: Unique identifier (auto-increment)
- `from_email`: Sender email address
- `to_email`: Recipient email address
- `subject`: Email subject line
- `message`: Email content (HTML)
- `status`: Email delivery status
- `error_message`: Error details if delivery failed
- `sent_at`: Timestamp when email was sent
- `created_at`: Log creation timestamp
- `updated_at`: Log last update timestamp

#### Indexes
- Primary key on `id`
- Recommended indexes for performance:
```sql
CREATE INDEX idx_email_logs_status ON email_logs(status);
CREATE INDEX idx_email_logs_created_at ON email_logs(created_at);
CREATE INDEX idx_email_logs_to_email ON email_logs(to_email);
```

## Security Considerations

### 1. Password Security

#### Encryption
- Password disimpan terenkripsi di database menggunakan Laravel's encryption
- Hanya decrypt saat digunakan di memory
- Tidak pernah menyimpan plain text password

#### App Password Usage
- Gunakan App Password alih-alih regular password
- App password memiliki scope terbatas hanya untuk email access
- Bisa di-revoke kapan saja dari Google Account

#### Password Handling
```php
// Secure password handling
public function saveConfig($data)
{
    // Only update password if provided
    if (!empty($data['gmail_password'])) {
        $passwordToSave = encrypt($data['gmail_password']);
    } else {
        // Keep existing password
        $passwordToSave = $config->gmail_password;
    }
    
    // Store encrypted password
    $config->gmail_password = $passwordToSave;
    $config->save();
}
```

### 2. Configuration Security

#### Environment Separation
- Development, staging, dan production menggunakan konfigurasi terpisah
- Tidak ada hard-coded credentials di source code
- Gunakan environment variables untuk sensitive data

#### Access Control
- Hanya superadmin yang bisa mengakses SMTP configuration
- Role-based access control (RBAC) implementation
- Activity logging untuk semua configuration changes

### 3. Email Security

#### SPF/DKIM Records
```dns
; SPF Record
@ IN TXT "v=spf1 include:_spf.google.com ~all"

; DKIM Record (jika diperlukan)
selector._domainkey IN TXT "v=DKIM1; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA..."
```

#### DMARC Policy
```dns
; DMARC Record
_dmarc IN TXT "v=DMARC1; p=quarantine; rua=mailto:dmarc@usbypkp.ac.id"
```

### 4. Network Security

#### Firewall Configuration
- Buka port 587 untuk outbound connection ke Gmail SMTP
- Block inbound SMTP connections ke server
- Monitor SMTP traffic untuk suspicious activity

#### SSL/TLS Configuration
- Gunakan TLS encryption untuk SMTP connection
- Verify SSL certificates
- Keep OpenSSL updated

### 5. Application Security

#### Input Validation
```php
// Example validation rules
protected $rules = [
    'gmailEmail' => 'required|email',
    'gmailPassword' => 'required|string|min:16',
    'gmailFromName' => 'required|string|max:255',
    'testEmail' => 'required|email'
];
```

#### SQL Injection Prevention
- Gunakan Laravel Eloquent ORM
- Parameterized queries untuk raw SQL
- Input sanitization

#### XSS Prevention
- Escape output di Blade templates
- Use Laravel's `{{ }}` syntax
- Sanitize user input

## Best Practices

### 1. Configuration Management

#### Use Environment Variables
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=${MAIL_USERNAME}
MAIL_PASSWORD=${MAIL_PASSWORD}
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS}"
MAIL_FROM_NAME="${MAIL_FROM_NAME}"
```

#### Configuration Caching
```bash
# Production environment
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Development environment
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2. Email Sending Best Practices

#### Use Queue for Email Sending
```php
// In controller
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

Mail::to($user->email)->queue(new WelcomeEmail($user));
```

#### Implement Rate Limiting
```php
// In service
public function sendEmail($toEmail, $subject, $message)
{
    // Check rate limit
    if ($this->isRateLimited($toEmail)) {
        throw new Exception('Email rate limit exceeded');
    }
    
    // Send email
    // ...
}
```

#### Email Template Best Practices
- Use responsive design
- Include plain text alternative
- Avoid spam trigger words
- Include unsubscribe link
- Use professional branding

### 3. Monitoring & Alerting

#### Set Up Monitoring
```php
// In service
public function monitorEmailHealth()
{
    $stats = $this->getEmailStats();
    
    // Alert if failure rate > 10%
    $failureRate = ($stats['failed'] / $stats['total']) * 100;
    if ($failureRate > 10) {
        $this->sendAlert('High email failure rate: ' . $failureRate . '%');
    }
}
```

#### Log Analysis
```bash
# Analyze email logs
grep "SMTP Test Email Failed" storage/logs/laravel.log | tail -n 50

# Check delivery rates
php artisan tinker
>>> App\Models\EmailLog::where('created_at', '>', now()->subDays(7))->groupBy('status')->selectRaw('status, count(*) as count')->get()
```

### 4. Performance Optimization

#### Database Optimization
```php
// Add indexes for better performance
Schema::table('email_logs', function (Blueprint $table) {
    $table->index(['status', 'created_at']);
    $table->index('to_email');
});
```

#### Caching Strategy
```php
// Cache configuration
public function getActiveConfig()
{
    return Cache::remember('active_smtp_config', 3600, function () {
        return SmtpSetting::where('is_active', true)->first();
    });
}
```

#### Queue Configuration
```env
QUEUE_CONNECTION=database
QUEUE_TABLE=jobs
QUEUE_FAILED_TABLE=failed_jobs
```

### 5. Backup & Recovery

#### Configuration Backup
```bash
# Backup SMTP settings
mysqldump -u user -p database_name smtp_settings > smtp_settings_backup.sql

# Backup email logs
mysqldump -u user -p database_name email_logs > email_logs_backup.sql
```

#### Recovery Procedures
```php
// Restore configuration
public function restoreConfiguration($backupData)
{
    $config = SmtpSetting::find(1);
    $config->update([
        'gmail_email' => $backupData['gmail_email'],
        'gmail_password' => encrypt($backupData['gmail_password']),
        'gmail_from_name' => $backupData['gmail_from_name'],
        'is_active' => $backupData['is_active']
    ]);
    
    // Clear cache
    Artisan::call('config:clear');
}
```

## Changelog

### Version 1.0.0 (2025-09-25)
#### Added
- Initial SMTP Gmail setup system
- Laravel 12 compatible configuration structure
- Real-time connection testing
- Email sending with HTML templates
- Comprehensive logging system
- Debug information panel
- User-friendly Livewire interface
- Password encryption and security
- Email activity monitoring

#### Technical Features
- **GmailSmtpService**: Core service for SMTP operations
- **SmtpSetting Model**: Database model for configuration
- **EmailLog Model**: Logging system for email activities
- **SmtpSetup Component**: Livewire component for UI
- **Laravel 12 Compatibility**: Proper configuration structure
- **Force Reload**: Mailer instance management
- **Error Handling**: Comprehensive error management
- **Security**: Password encryption and secure handling

#### Bug Fixes
- Fixed undefined ping() method error in SMTP connection test
- Fixed configuration not being applied correctly in Laravel 12
- Fixed from address showing default value instead of configured value
- Fixed LogTransport being used instead of SMTP transport
- Fixed config values showing null in debug information

#### Improvements
- Better error messages and user feedback
- Professional HTML email templates
- Comprehensive debug information
- Real-time configuration testing
- Improved security practices
- Better performance with configuration caching
- Enhanced logging and monitoring capabilities

### Future Enhancements (Planned)
- Multiple SMTP provider support
- Email template management system
- Advanced analytics and reporting
- Email bounce handling
- Automated failover system
- API endpoints for external integration
- Advanced security features
- Performance monitoring dashboard

---

## Support

For technical support or questions about the SMTP Gmail Setup system:

1. **Documentation**: Refer to this documentation first
2. **Debug Information**: Use the built-in debug panel
3. **Logs**: Check Laravel logs and email logs
4. **Community**: Contact development team

### Contact Information
- **Development Team**: dev@usbypkp.ac.id
- **System Administrator**: admin@usbypkp.ac.id
- **Support**: support@usbypkp.ac.id

### Resources
- [Laravel Documentation](https://laravel.com/docs)
- [Gmail SMTP Configuration](https://support.google.com/mail/answer/7126229)
- [Google App Passwords](https://myaccount.google.com/apppasswords)
- [SPF/DKIM Setup Guide](https://tools.google.com/dmarc/)

---

*This documentation is part of the USBYPKP System and is subject to change without notice. Last updated: September 25, 2025*
