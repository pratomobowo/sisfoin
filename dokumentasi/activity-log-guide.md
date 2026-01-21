# Dokumentasi Activity Log System

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Database Structure](#database-structure)
4. [Model Structure](#model-structure)
5. [Livewire Component](#livewire-component)
6. [Features](#features)
7. [Implementation Guide](#implementation-guide)
8. [API Reference](#api-reference)
9. [Best Practices](#best-practices)
10. [Troubleshooting](#troubleshooting)

---

## Overview

Activity Log System adalah fitur komprehensif untuk monitoring dan tracking semua aktivitas yang terjadi di dalam aplikasi. Sistem ini memungkinkan administrator untuk melihat, menganalisis, dan mengekspor log aktivitas pengguna serta sistem.

### 🎯 Tujuan Utama
- **Security Monitoring**: Melacak aktivitas mencurigakan atau unauthorized access
- **Audit Trail**: Mencatat semua perubahan data untuk kepatuhan compliance
- **Performance Analysis**: Menganalisis pola penggunaan dan bottleneck
- **User Behavior Tracking**: Memahami bagaimana pengguna berinteraksi dengan sistem
- **Debugging**: Membantu troubleshooting dengan detail log yang lengkap

### ✨ Fitur Utama
- **Real-time Monitoring**: Live updates dengan Livewire
- **Advanced Filtering**: Filter berdasarkan tanggal, tipe, pengguna, dan kategori
- **Export Functionality**: Export log dalam berbagai format (CSV, JSON, Excel)
- **Search Capabilities**: Full-text search pada deskripsi dan metadata
- **Responsive Design**: Mobile-friendly interface
- **Auto-refresh**: Update otomatis setiap 5 menit
- **Keyboard Shortcuts**: Shortcut keys untuk aksi cepat

---

## Architecture

Activity Log System dibangun dengan arsitektur yang modular dan scalable:

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   Database      │
│                 │    │                 │    │                 │
│ • Livewire      │◄──►│ • Controllers   │◄──►│ • activity_log  │
│ • Blade Views   │    │ • Services      │    │ • users         │
│ • Alpine.js     │    │ • Models        │    │ • roles         │
│ • Tailwind CSS  │    │ • Events        │    │ • permissions   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Komponen Utama

#### 1. **Frontend Layer**
- **Livewire Component**: `ActivityLogs.php` - Handle real-time interactions
- **Blade Views**: `activity-log.blade.php` - UI components
- **Reusable Components**: Superadmin components untuk consistency

#### 2. **Backend Layer**
- **Model**: `ActivityLog.php` - Eloquent model dengan scopes dan relationships
- **Controllers**: Tidak diperlukan (handled oleh Livewire)
- **Services**: Export services dan数据处理

#### 3. **Database Layer**
- **activity_log table**: Storage untuk semua log entries
- **Relationships**: Polymorphic relationships ke models lain
- **Indexes**: Optimized queries dengan proper indexing

---

## Database Structure

### activity_log Table Schema

```sql
CREATE TABLE activity_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    log_name VARCHAR(255) NULL,
    description TEXT NULL,
    subject_type VARCHAR(255) NULL,
    subject_id BIGINT UNSIGNED NULL,
    causer_type VARCHAR(255) NULL,
    causer_id BIGINT UNSIGNED NULL,
    properties JSON NULL,
    batch_uuid VARCHAR(255) NULL,
    event VARCHAR(255) NULL,
    action VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_log_name (log_name),
    INDEX idx_causer (causer_type, causer_id),
    INDEX idx_subject (subject_type, subject_id),
    INDEX idx_created_at (created_at),
    INDEX idx_event (event),
    INDEX idx_action (action)
);
```

### Field Descriptions

| Field | Type | Description |
|-------|------|-------------|
| `id` | BIGINT | Primary key |
| `log_name` | VARCHAR | Kategori log (users, roles, system, etc.) |
| `description` | TEXT | Deskripsi aktivitas yang terjadi |
| `subject_type` | VARCHAR | Model class yang terkena dampak |
| `subject_id` | BIGINT | ID dari subject model |
| `causer_type` | VARCHAR | Model class yang menyebabkan aktivitas |
| `causer_id` | BIGINT | ID dari causer model |
| `properties` | JSON | Additional properties dan changes |
| `batch_uuid` | VARCHAR | UUID untuk batch operations |
| `event` | VARCHAR | Event type (created, updated, deleted, etc.) |
| `action` | VARCHAR | Action type yang lebih spesifik |
| `ip_address` | VARCHAR | IP address pengguna |
| `user_agent` | TEXT | User agent string |
| `metadata` | JSON | Additional metadata |
| `created_at` | TIMESTAMP | Timestamp pembuatan log |
| `updated_at` | TIMESTAMP | Timestamp update terakhir |

---

## Model Structure

### ActivityLog Model

**Lokasi**: `app/Models/ActivityLog.php`

```php
class ActivityLog extends Model implements Activity
{
    use HasFactory;

    protected $table = 'activity_log';
    
    protected $fillable = [
        'log_name', 'description', 'subject_type', 'subject_id',
        'causer_type', 'causer_id', 'properties', 'batch_uuid',
        'event', 'action', 'ip_address', 'user_agent', 'metadata'
    ];

    protected $casts = [
        'properties' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function subject(): MorphTo
    public function causer(): MorphTo
    public function user(): BelongsTo

    // Scopes
    public function scopeInLog(Builder $query, ...$logNames)
    public function scopeCausedBy(Builder $query, Model $causer)
    public function scopeForEvent(Builder $query, string $event)
    public function scopeForSubject(Builder $query, Model $subject)
    public function scopeLogName($query, $logName)
    public function scopeDateRange($query, $from, $to)
    public function scopeSearch($query, $search)

    // Accessors
    public function getFormattedPropertiesAttribute()
    public function getFormattedMetadataAttribute()
}
```

### Key Methods

#### **Relationships**
```php
// Get the subject model
$activity->subject; // Returns the model that was affected

// Get the causer model  
$activity->causer; // Returns the model that caused the activity

// Get the user (shortcut for causer)
$activity->user; // Returns User model
```

#### **Scopes**
```php
// Filter by log names
ActivityLog::inLog('users', 'roles')->get();

// Filter by causer
ActivityLog::causedBy($user)->get();

// Filter by event
ActivityLog::forEvent('created')->get();

// Filter by subject
ActivityLog::forSubject($user)->get();

// Date range filter
ActivityLog::dateRange('2024-01-01', '2024-12-31')->get();

// Search functionality
ActivityLog::search('login attempt')->get();
```

#### **Accessors**
```php
// Get formatted properties
$formattedProperties = $activity->formatted_properties;

// Get formatted metadata
$formattedMetadata = $activity->formatted_metadata;
```

---

## Livewire Component

### ActivityLogs Component

**Lokasi**: `app/Livewire/Superadmin/ActivityLogs.php`

```php
class ActivityLogs extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Properties
    public $page = 1;
    public $search = '';
    public $perPage = 10;
    public $filterType = '';
    public $dateFrom = '';
    public $dateTo = '';

    public function render()
    {
        $query = ActivityLogModel::with('causer')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%'.$this->search.'%')
                        ->orWhere('subject_type', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterType, function ($query) {
                $query->where('log_name', $this->filterType);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc');

        $activityLogs = $query->paginate($this->perPage);

        // Get unique log names for filter dropdown
        $logNames = ActivityLogModel::distinct()->pluck('log_name')->filter()->toArray();

        return view('livewire.superadmin.activity-log', [
            'activityLogs' => $activityLogs,
            'logNames' => $logNames,
            'activities' => $activityLogs,
        ]);
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterType = '';
        $this->dateFrom = '';
        $this->dateTo = '';
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

    // Export functionality
    public function exportLogs()
    {
        // Implementation for exporting logs
        $this->dispatch('showToast', 'Log berhasil di-export!', 'success');
    }
}
```

### Key Features

#### **Real-time Search**
```php
// Live search with debounce
<input wire:model.live.debounce.300ms="search" />
```

#### **Dynamic Filtering**
```php
// Filter by log type
<select wire:model.live="filterType">
    <option value="">Semua Tipe</option>
    @foreach($logNames as $name)
        <option value="{{ $name }}">{{ ucfirst($name) }}</option>
    @endforeach
</select>

// Date range filters
<input type="date" wire:model.live="dateFrom" />
<input type="date" wire:model.live="dateTo" />
```

#### **Pagination**
```php
// Using reusable pagination component
<x-superadmin.pagination 
    :currentPage="$activities->currentPage()"
    :lastPage="$activities->lastPage()"
    :total="$activities->total()"
    :perPage="$activities->perPage()"
    perPageWireModel="perPage"
    previousPageWireModel="previousPage"
    nextPageWireModel="nextPage"
    gotoPageWireModel="gotoPage"
/>
```

---

## Features

### 1. Real-time Monitoring

#### **Live Updates**
- Auto-refresh setiap 5 menit untuk data terbaru
- Real-time search dengan debounce 300ms
- Dynamic filter updates tanpa page reload

#### **Implementation**
```javascript
// Auto refresh functionality
setInterval(function() {
    if (typeof Livewire !== 'undefined') {
        Livewire.emit('refreshData');
    }
}, 300000); // 5 minutes
```

### 2. Advanced Filtering

#### **Filter Types**
- **Search**: Full-text search pada description, subject, dan user
- **Log Type**: Filter berdasarkan kategori log (users, roles, system)
- **Date Range**: Filter berdasarkan rentang tanggal
- **User**: Filter berdasarkan pengguna tertentu

#### **Active Filters Display**
```blade
<!-- Visual feedback untuk aktif filters -->
@if($search || $filterType || $dateFrom || $dateTo)
    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-medium text-blue-800">Filter Aktif:</span>
            </div>
            <button wire:click="resetFilters" class="text-sm text-blue-600 hover:text-blue-800">
                Hapus Semua
            </button>
        </div>
        <div class="flex flex-wrap gap-2 mt-2">
            @if($search)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Pencarian: "{{ $search }}"
                </span>
            @endif
            <!-- Other filters... -->
        </div>
    </div>
@endif
```

### 3. Export Functionality

#### **Export Options**
- **CSV Format**: Standard comma-separated values
- **JSON Format**: Structured data with metadata
- **Excel Format**: Formatted spreadsheet with styling
- **PDF Format**: Professional report format

#### **Implementation**
```php
public function exportLogs()
{
    $logs = ActivityLog::with('causer')
        ->when($this->search, function ($query) {
            $query->where('description', 'like', '%'.$this->search.'%');
        })
        ->when($this->filterType, function ($query) {
            $query->where('log_name', $this->filterType);
        })
        ->when($this->dateFrom, function ($query) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        })
        ->when($this->dateTo, function ($query) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        })
        ->orderBy('created_at', 'desc')
        ->get();

    // Generate CSV
    $csv = $this->generateCsv($logs);
    
    return response()->streamDownload(function() use ($csv) {
        echo $csv;
    }, 'activity-logs-'.now()->format('Y-m-d').'.csv');
}
```

### 4. Properties Viewer

#### **Modal for Detailed Properties**
```javascript
function openPropertiesModal(activityId, properties) {
    document.getElementById('propertiesContent').textContent = JSON.stringify(properties, null, 2);
    document.getElementById('propertiesModal').classList.remove('hidden');
}

function closePropertiesModal() {
    document.getElementById('propertiesModal').classList.add('hidden');
}
```

#### **Usage in Table**
```blade
@if($activity->properties && count($activity->properties) > 0)
    <button type="button"
            onclick="openPropertiesModal({{ $activity->id }}, {{ json_encode($activity->properties) }})"
            title="Lihat Properties" 
            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-blue-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
    </button>
@endif
```

### 5. Keyboard Shortcuts

#### **Available Shortcuts**
- **Escape**: Close modals
- **Ctrl/Cmd + E**: Export logs
- **Ctrl/Cmd + F**: Focus search input
- **Ctrl/Cmd + R**: Refresh data

#### **Implementation**
```javascript
document.addEventListener('keydown', function(e) {
    // Escape to close modals
    if (e.key === 'Escape') {
        const modal = document.getElementById('propertiesModal');
        if (modal && !modal.classList.contains('hidden')) {
            closePropertiesModal();
        }
    }
    
    // Ctrl/Cmd + E for export
    if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
        e.preventDefault();
        if (typeof Livewire !== 'undefined') {
            Livewire.emit('exportLogs');
        }
    }
});
```

### 6. Toast Notifications

#### **Toast System**
```javascript
window.showToast = function(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg text-white font-medium shadow-lg transform transition-all duration-300 translate-x-full ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
};
```

---

## Implementation Guide

### 1. Setup Activity Log

#### **Install Dependencies**
```bash
# Install Spatie Activity Log package
composer require spatie/laravel-activitylog

# Publish migrations
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"

# Run migrations
php artisan migrate
```

#### **Configure Model**
```php
// In your User model
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use LogsActivity;

    protected static $logName = 'users';
    protected static $logAttributes = ['name', 'email', 'role'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
}
```

### 2. Create Custom Activity Logs

#### **Manual Logging**
```php
// Log user actions
activity()
    ->useLog('users')
    ->performedOn($user)
    ->causedBy(auth()->user())
    ->withProperties(['old' => $oldValues, 'new' => $newValues])
    ->log('User profile updated');

// Log system events
activity()
    ->useLog('system')
    ->event('maintenance')
    ->withProperties(['duration' => '2 hours', 'affected_users' => 150])
    ->log('System maintenance completed');
```

#### **Automatic Logging**
```php
// Enable automatic logging for model changes
class Product extends Model
{
    use LogsActivity;
    
    protected static $logName = 'products';
    protected static $logAttributes = ['name', 'price', 'stock'];
    
    // This will automatically log create, update, delete events
}
```

### 3. Create Livewire Component

#### **Component Setup**
```bash
php artisan make:livewire Superadmin/ActivityLogs
```

#### **Component Implementation**
```php
// app/Livewire/Superadmin/ActivityLogs.php
class ActivityLogs extends Component
{
    use WithPagination;
    
    public $search = '';
    public $filterType = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 10;
    
    public function render()
    {
        $logs = ActivityLog::query()
            ->with('causer')
            ->when($this->search, fn($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->filterType, fn($q) => $q->where('log_name', $this->filterType))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate($this->perPage);
            
        return view('livewire.superadmin.activity-logs', [
            'logs' => $logs,
            'logNames' => ActivityLog::distinct()->pluck('log_name')
        ]);
    }
}
```

### 4. Create View

#### **View Structure**
```blade
<!-- resources/views/livewire/superadmin/activity-logs.blade.php -->
<div class="space-y-6">
    <!-- Header with export button -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Activity Logs</h2>
                <p class="text-sm text-gray-600">Monitor system activities</p>
            </div>
            <button wire:click="exportLogs" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                Export Logs
            </button>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input wire:model.live="search" type="text" placeholder="Search..." class="border rounded-lg px-3 py-2">
            <select wire:model.live="filterType" class="border rounded-lg px-3 py-2">
                <option value="">All Types</option>
                @foreach($logNames as $name)
                    <option value="{{ $name }}">{{ ucfirst($name) }}</option>
                @endforeach
            </select>
            <input wire:model.live="dateFrom" type="date" class="border rounded-lg px-3 py-2">
            <input wire:model.live="dateTo" type="date" class="border rounded-lg px-3 py-2">
        </div>
    </div>
    
    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Activity</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td>{{ $log->causer->name ?? 'System' }}</td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            @if($log->properties)
                                <button onclick="showProperties({{ json_encode($log->properties) }})">View</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Pagination -->
        {{ $logs->links() }}
    </div>
</div>
```

### 5. Add Route

#### **Web Route**
```php
// routes/web.php
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/superadmin/activity-logs', ActivityLogs::class)->name('superadmin.activity-logs');
});
```

### 6. Add Menu Item

#### **Navigation Menu**
```blade
<!-- In your admin navigation -->
<li>
    <a href="{{ route('superadmin.activity-logs') }}" 
       class="{{ request()->routeIs('superadmin.activity-logs*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Activity Logs
    </a>
</li>
```

---

## API Reference

### Model Methods

#### **Query Scopes**
```php
// Filter by log name(s)
ActivityLog::inLog('users', 'roles')->get();
ActivityLog::logName('users')->get();

// Filter by causer
ActivityLog::causedBy($user)->get();

// Filter by event
ActivityLog::forEvent('created')->get();
ActivityLog::forEvent('updated')->get();

// Filter by subject
ActivityLog::forSubject($user)->get();

// Date range filtering
ActivityLog::dateRange('2024-01-01', '2024-12-31')->get();

// Search functionality
ActivityLog::search('user login')->get();
```

#### **Relationships**
```php
// Get the subject model
$activity->subject; // Returns the affected model

// Get the causer model
$activity->causer; // Returns the model that caused the activity

// Get the user (shortcut)
$activity->user; // Returns User model if causer is user
```

#### **Accessors**
```php
// Get formatted properties as JSON
$formattedProperties = $activity->formatted_properties;

// Get formatted metadata as JSON
$formattedMetadata = $activity->formatted_metadata;

// Get specific property value
$oldValue = $activity->getExtraProperty('old');
$newValue = $activity->getExtraProperty('new');
```

### Livewire Component Methods

#### **Properties**
```php
public $search = '';           // Search query
public $filterType = '';       // Log type filter
public $dateFrom = '';        // Start date filter
public $dateTo = '';          // End date filter
public $perPage = 10;         // Items per page
```

#### **Methods**
```php
// Reset all filters
public function resetFilters()

// Pagination methods
public function gotoPage($page)
public function nextPage()
public function previousPage()
public function updatedPerPage()

// Export functionality
public function exportLogs()
```

#### **Events**
```php
// Listen for refresh events
protected $listeners = ['refreshData' => '$refresh'];

// Dispatch events
$this->dispatch('showToast', 'Message', 'success');
```

### JavaScript Functions

#### **Modal Management**
```javascript
// Open properties modal
function openPropertiesModal(activityId, properties)

// Close properties modal
function closePropertiesModal()
```

#### **Toast Notifications**
```javascript
// Show toast notification
window.showToast(message, type)

// Available types: 'success', 'error', 'warning', 'info'
```

#### **Keyboard Shortcuts**
```javascript
// Available shortcuts:
// Escape: Close modals
// Ctrl/Cmd + E: Export logs
// Ctrl/Cmd + F: Focus search
// Ctrl/Cmd + R: Refresh data
```

---

## Best Practices

### 1. Performance Optimization

#### **Database Indexing**
```sql
-- Add indexes for frequently queried fields
CREATE INDEX idx_activity_log_created_at ON activity_log(created_at);
CREATE INDEX idx_activity_log_log_name ON activity_log(log_name);
CREATE INDEX idx_activity_log_causer ON activity_log(causer_type, causer_id);
```

#### **Query Optimization**
```php
// Use eager loading to avoid N+1 queries
$logs = ActivityLog::with('causer', 'subject')->get();

// Use pagination for large datasets
$logs = ActivityLog::paginate(50);

// Use appropriate date filtering
$logs = ActivityLog::whereDate('created_at', '>=', $startDate)
    ->whereDate('created_at', '<=', $endDate)
    ->get();
```

#### **Caching**
```php
// Cache log names for filter dropdown
$logNames = Cache::remember('activity_log_names', 3600, function () {
    return ActivityLog::distinct()->pluck('log_name');
});
```

### 2. Security Considerations

#### **Data Sanitization**
```php
// Sanitize user input for search
$search = htmlspecialchars($request->input('search'), ENT_QUOTES, 'UTF-8');

// Validate date inputs
$validated = $request->validate([
    'dateFrom' => 'nullable|date',
    'dateTo' => 'nullable|date|after_or_equal:dateFrom',
]);
```

#### **Access Control**
```php
// Ensure only authorized users can access logs
public function mount()
{
    if (!auth()->user()->hasRole('superadmin')) {
        abort(403, 'Unauthorized access');
    }
}

// Use policies for fine-grained control
public function view(User $user, ActivityLog $log)
{
    return $user->hasRole('superadmin') || 
           $log->causer_id === $user->id;
}
```

#### **Log Retention**
```php
// Set up log retention policy
public function cleanupOldLogs()
{
    // Delete logs older than 1 year
    ActivityLog::where('created_at', '<', now()->subYear())->delete();
    
    // Archive logs older than 6 months
    ActivityLog::where('created_at', '<', now()->subMonths(6))
        ->whereNotIn('log_name', ['security', 'critical'])
        ->delete();
}
```

### 3. User Experience

#### **Responsive Design**
```css
/* Mobile-first approach */
@media (max-width: 640px) {
    .activity-table {
        font-size: 0.875rem;
    }
    
    .activity-filters {
        grid-template-columns: 1fr;
    }
}

/* Tablet styles */
@media (min-width: 641px) and (max-width: 1024px) {
    .activity-filters {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Desktop styles */
@media (min-width: 1025px) {
    .activity-filters {
        grid-template-columns: repeat(4, 1fr);
    }
}
```

#### **Loading States**
```blade
<!-- Loading spinner -->
<div wire:loading wire:target="exportLogs" class="inline-block">
    <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
</div>
```

#### **Empty States**
```blade
<!-- Empty state when no logs found -->
@forelse($logs as $log)
    <!-- Log item -->
@empty
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No activity logs found</h3>
        <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or date range.</p>
    </div>
@endforelse
```

### 4. Monitoring and Alerting

#### **Critical Activity Alerts**
```php
// Monitor for suspicious activities
public function checkSuspiciousActivities()
{
    $failedLogins = ActivityLog::where('description', 'like', '%failed login%')
        ->where('created_at', '>=', now()->subHours(1))
        ->count();
    
    if ($failedLogins > 10) {
        // Send alert to admin
        Notification::route('mail', 'admin@example.com')
            ->notify(new SuspiciousActivityAlert($failedLogins));
    }
}
```

#### **Performance Monitoring**
```php
// Log slow queries
DB::listen(function ($query) {
    if ($query->time > 1000) { // More than 1 second
        activity()
            ->useLog('performance')
            ->withProperties([
                'sql' => $query->sql,
                'time' => $query->time,
                'bindings' => $query->bindings
            ])
            ->log('Slow query detected');
    }
});
```

---

## Troubleshooting

### Common Issues and Solutions

#### 1. **Pagination Not Working**

**Problem**: "Property [$page] not found on component"

**Solution**: Add the page property to your Livewire component:
```php
class ActivityLogs extends Component
{
    use WithPagination;
    
    public $page = 1; // Add this line
    
    // ... rest of the component
}
```

#### 2. **Search Not Working**

**Problem**: Search returns no results or incorrect results

**Solution**: Check your search implementation:
```php
// Make sure search is case-insensitive and searches multiple fields
public function render()
{
    $query = ActivityLog::with('causer')
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%'.$this->search.'%')
                    ->orWhere('subject_type', 'like', '%'.$this->search.'%')
                    ->orWhereHas('causer', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%')
                           ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
            });
        })
        // ... rest of the query
}
```

#### 3. **Export Functionality Not Working**

**Problem**: Export button doesn't trigger download

**Solution**: Check your export method implementation:
```php
public function exportLogs()
{
    $logs = ActivityLog::with('causer')
        ->when($this->search, function ($query) {
            $query->where('description', 'like', '%'.$this->search.'%');
        })
        // ... other filters
        ->get();
    
    $csv = $this->generateCsv($logs);
    
    return response()->streamDownload(function() use ($csv) {
        echo $csv;
    }, 'activity-logs-'.date('Y-m-d').'.csv', [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="activity-logs-'.date('Y-m-d').'.csv"',
    ]);
}
```

#### 4. **Modal Not Opening**

**Problem**: Properties modal doesn't open when clicking the button

**Solution**: Check your JavaScript implementation:
```javascript
// Make sure the modal functions are properly defined
function openPropertiesModal(activityId, properties) {
    const modal = document.getElementById('propertiesModal');
    const content = document.getElementById('propertiesContent');
    
    if (modal && content) {
        content.textContent = JSON.stringify(properties, null, 2);
        modal.classList.remove('hidden');
    }
}

function closePropertiesModal() {
    const modal = document.getElementById('propertiesModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Add click outside to close
document.getElementById('propertiesModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePropertiesModal();
    }
});
```

#### 5. **Performance Issues**

**Problem**: Page loads slowly with many logs

**Solution**: Implement performance optimizations:
```php
// Use pagination
public $perPage = 25;

// Use eager loading
$logs = ActivityLog::with('causer')->paginate($this->perPage);

// Add indexes to database
// Add proper indexes for frequently queried fields

// Use caching for log names
$logNames = Cache::remember('activity_log_names', 3600, function () {
    return ActivityLog::distinct()->pluck('log_name');
});
```

#### 6. **Date Filter Issues**

**Problem**: Date range filter not working correctly

**Solution**: Ensure proper date formatting and validation:
```php
// In your Livewire component
public function updatedDateFrom()
{
    $this->validate([
        'dateFrom' => 'nullable|date',
        'dateTo' => 'nullable|date|after_or_equal:dateFrom',
    ]);
}

public function updatedDateTo()
{
    $this->validate([
        'dateFrom' => 'nullable|date',
        'dateTo' => 'nullable|date|after_or_equal:dateFrom',
    ]);
}

// In your query
->when($this->dateFrom, function ($query) {
    $query->whereDate('created_at', '>=', $this->dateFrom);
})
->when($this->dateTo, function ($query) {
    $query->whereDate('created_at', '<=', $this->dateTo);
})
```

### Debugging Tips

#### **Enable Debug Mode**
```php
// In your Livewire component
public function render()
{
    // Debug query
    $query = ActivityLog::with('causer');
    
    if ($this->search) {
        $query->where('description', 'like', '%'.$this->search.'%');
    }
    
    // Log the SQL query
    \Log::info('Activity Log Query: ' . $query->toSql());
    \Log::info('Bindings: ' . json_encode($query->getBindings()));
    
    $logs = $query->paginate($this->perPage);
    
    return view('livewire.superadmin.activity-logs', compact('logs'));
}
```

#### **Check Browser Console**
```javascript
// Add console logging for debugging
document.addEventListener('livewire:init', function() {
    Livewire.hook('message.failed', (message, component) => {
        console.error('Livewire message failed:', message);
    });
    
    Livewire.hook('message.received', (message, component) => {
        console.log('Livewire message received:', message);
    });
});
```

#### **Monitor Database Performance**
```php
// Enable query logging
DB::enableQueryLog();

// Run your queries
$logs = ActivityLog::with('causer')->get();

// Get the queries
$queries = DB::getQueryLog();

// Log slow queries
foreach ($queries as $query) {
    if ($query['time'] > 100) {
        \Log::warning('Slow query detected: ' . $query['query'] . ' (' . $query['time'] . 'ms)');
    }
}
```

### Maintenance Tasks

#### **Regular Cleanup**
```php
// Schedule regular cleanup
protected function schedule(Schedule $schedule)
{
    // Clean up old logs (older than 1 year)
    $schedule->call(function () {
        ActivityLog::where('created_at', '<', now()->subYear())->delete();
    })->monthly();
    
    // Archive logs (older than 6 months)
    $schedule->call(function () {
        ActivityLog::where('created_at', '<', now()->subMonths(6))
            ->whereNotIn('log_name', ['security', 'critical'])
            ->delete();
    })->weekly();
}
```

#### **Database Optimization**
```sql
-- Regular table optimization
OPTIMIZE TABLE activity_log;

-- Update statistics
ANALYZE TABLE activity_log;

-- Check table size
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size in MB"
FROM information_schema.TABLES 
WHERE table_schema = DATABASE() 
    AND table_name = 'activity_log';
```

#### **Monitor Storage Usage**
```php
// Check storage usage
public function checkStorageUsage()
{
    $totalLogs = ActivityLog::count();
    $totalSize = ActivityLog::sum(
        DB::raw('LENGTH(description) + LENGTH(properties) + LENGTH(metadata)')
    );
    
    $averageSize = $totalLogs > 0 ? $totalSize / $totalLogs : 0;
    
    \Log::info('Activity Log Storage Usage:', [
        'total_logs' => $totalLogs,
        'total_size_bytes' => $totalSize,
        'average_size_bytes' => $averageSize,
        'total_size_mb' => round($totalSize / 1024 / 1024, 2)
    ]);
    
    // Alert if storage is getting high
    if ($totalSize > 100 * 1024 * 1024) { // 100MB
        Notification::route('mail', 'admin@example.com')
            ->notify(new StorageUsageAlert($totalSize));
    }
}
```

---

## Conclusion

Activity Log System adalah fitur powerful yang menyediakan comprehensive monitoring dan audit capabilities untuk aplikasi Anda. Dengan proper implementation dan maintenance, sistem ini akan:

✅ **Enhance Security**: Detect and prevent unauthorized activities
✅ **Ensure Compliance**: Meet regulatory requirements for audit trails
✅ **Improve Performance**: Identify bottlenecks and optimization opportunities
✅ **Facilitate Debugging**: Provide detailed logs for troubleshooting
✅ **Support Decision Making**: Offer insights into user behavior and system usage

### Key Takeaways

1. **Start Simple**: Begin with basic logging and gradually add advanced features
2. **Focus on Performance**: Implement proper indexing and caching strategies
3. **Prioritize Security**: Ensure proper access controls and data sanitization
4. **Monitor Regularly**: Set up alerts and perform regular maintenance
5. **Document Everything**: Keep detailed documentation for future reference

### Next Steps

1. **Implement**: Follow the implementation guide to set up the system
2. **Customize**: Adapt the system to your specific requirements
3. **Test**: Thoroughly test all features and edge cases
4. **Monitor**: Set up monitoring and alerting
5. **Maintain**: Perform regular maintenance and optimization

Dengan Activity Log System yang well-implemented, Anda akan memiliki valuable tool untuk monitoring, debugging, dan improving aplikasi Anda.
