# Dokumentasi Menu Manajemen Pengguna

## Table of Contents
1. [Overview](#overview)
2. [Arsitektur & Struktur](#arsitektur--struktur)
3. [Komponen Reusable](#komponen-reusable)
4. [Style & Design System](#style--design-system)
5. [Tata Letak (Layout)](#tata-letak-layout)
6. [Fungsi & Fitur](#fungsi--fitur)
7. [Business Logic](#business-logic)
8. [Database Schema](#database-schema)
9. [Security & Authorization](#security--authorization)
10. [Performance & Optimization](#performance--optimization)
11. [Testing](#testing)
12. [Best Practices](#best-practices)

---

## Overview

Menu Manajemen Pengguna adalah modul sistem yang bertanggung jawab untuk mengelola data pengguna (users) dalam aplikasi. Modul ini menyediakan fitur lengkap untuk CRUD (Create, Read, Update, Delete) pengguna, manajemen peran (roles), import data massal, dan integrasi dengan sistem fingerprint.

### Fitur Utama:
- ✅ Manajemen data pengguna lengkap
- ✅ Sistem peran dan permission (Role-Based Access Control)
- ✅ Import data pengguna dari tabel Karyawan dan Dosen
- ✅ Integrasi dengan sistem fingerprint authentication
- ✅ Search dan filtering advance
- ✅ Pagination dan responsive design
- ✅ Activity logging untuk audit trail

---

## Arsitektur & Struktur

### File Structure
```
app/
├── Livewire/
│   └── Superadmin/
│       ├── UserManagement.php      # Livewire component untuk halaman index
│       ├── UserForm.php            # Livewire component untuk form create/edit
│       ├── UserImport.php          # Livewire component untuk import data
│       └── FingerprintAutocomplete.php # Livewire component untuk autocomplete fingerprint
├── Services/
│   └── UserService.php             # Business logic dan data access
├── Http/
│   └── Requests/
│       ├── StoreUserRequest.php    # Validation rules untuk create user
│       └── UpdateUserRequest.php   # Validation rules untuk update user
└── Models/
    ├── User.php                    # User model dengan relationships
    └── Role.php                    # Role model

resources/
├── views/
│   ├── superadmin/
│   │   └── users/
│   │       ├── index.blade.php     # Halaman utama manajemen pengguna
│   │       ├── create.blade.php    # Halaman tambah pengguna
│   │       ├── edit.blade.php      # Halaman edit pengguna
│   │       └── show.blade.php      # Halaman detail pengguna
│   └── livewire/
│       └── superadmin/
│           ├── user-management.blade.php    # View untuk UserManagement component
│           ├── user-form.blade.php          # View untuk UserForm component
│           ├── user-import.blade.php        # View untuk UserImport component
│           └── fingerprint-autocomplete.blade.php # View untuk FingerprintAutocomplete
└── components/
    └── superadmin/
        ├── page-header.blade.php    # Reusable header component
        └── breadcrumb-topbar.blade.php # Breadcrumb component
```

### Arsitektur Pattern
- **Repository Pattern**: `UserService` sebagai layer untuk business logic
- **Form Request Validation**: `StoreUserRequest` dan `UpdateUserRequest` untuk validation
- **Livewire Components**: Untuk interactive UI dengan real-time updates
- **Blade Components**: Untuk reusable UI elements
- **Service Layer**: Pemisahan business logic dari presentation layer

---

## Komponen Reusable

### 1. Page Header Component (`<x-superadmin.page-header>`)

**Lokasi**: `resources/views/components/superadmin/page-header.blade.php`

**Deskripsi**: Komponen header yang reusable untuk semua halaman dengan styling konsisten dan fitur back button.

**Props**:
```php
@props([
    'title' => '',              // Judul header (required)
    'description' => '',        // Deskripsi header (optional)
    'showBackButton' => false,  // Tampilkan back button (default: false)
    'backRoute' => null,        // Route untuk back button
    'backText' => 'Kembali',    // Text untuk back button
    'actions' => null,          // Custom actions HTML (optional)
])
```

**Usage Examples**:
```blade
<!-- Halaman Tambah Pengguna -->
<x-superadmin.page-header 
    title="Tambah Pengguna Baru"
    description="Formulir untuk menambah pengguna baru ke sistem"
    :showBackButton="true"
    backRoute="{{ route('superadmin.users.index') }}"
    backText="Kembali"
/>

<!-- Halaman Edit Pengguna -->
<x-superadmin.page-header 
    title="Edit Pengguna"
    description="Ubah informasi pengguna yang sudah ada"
    :showBackButton="true"
    backRoute="{{ route('superadmin.users.index') }}"
    backText="Kembali"
    :actions="[...]"
/>
```

**Features**:
- ✅ Responsive layout (flex column di mobile, row di desktop)
- ✅ Consistent styling (bg-white, rounded-xl, shadow-sm, border, p-6)
- ✅ Optional back button dengan customizable route dan text
- ✅ Custom actions parameter untuk additional buttons
- ✅ Dynamic title dan description

### 2. Breadcrumb Component (`<x-superadmin.breadcrumb-topbar>`)

**Lokasi**: `resources/views/components/superadmin/breadcrumb-topbar.blade.php`

**Deskripsi**: Komponen breadcrumb untuk navigasi hierarkis.

**Props**:
```php
@props([
    'items' => []    // Array of breadcrumb items
])
```

**Item Structure**:
```php
$items = [
    ['title' => 'Manajemen Pengguna', 'url' => route('superadmin.users.index')],
    ['title' => 'Edit Pengguna'] // Current page (no URL)
];
```

### 3. Pagination Component (`<x-superadmin.pagination>`)

**Lokasi**: `resources/views/components/superadmin/pagination.blade.php`

**Deskripsi**: Komponen pagination yang reusable dan fully customizable untuk semua halaman yang membutuhkan pagination. Komponen ini mendukung Livewire integration dengan real-time updates.

**Props**:
```php
@props([
    'currentPage' => 1,           // Current page number
    'lastPage' => 1,             // Total pages
    'total' => 0,                // Total records
    'perPage' => 10,             // Records per page
    'perPageOptions' => [10, 25, 50, 100], // Available per page options
    'showPageInfo' => true,      // Show "Showing X to Y of Z"
    'showPerPage' => true,        // Show per page dropdown
    'alignment' => 'justify-between', // Tailwind alignment classes
    // Wire model names untuk Livewire integration
    'perPageWireModel' => 'perPage',
    'previousPageWireModel' => 'previousPage',
    'nextPageWireModel' => 'nextPage',
    'gotoPageWireModel' => 'gotoPage',
])
```

**Usage Examples**:
```blade
<!-- User Management Page -->
<x-superadmin.pagination 
    :currentPage="$users->currentPage()"
    :lastPage="$users->lastPage()"
    :total="$users->total()"
    :perPage="$users->perPage()"
    :perPageOptions="[10, 25, 50, 100]"
    :showPageInfo="true"
    :showPerPage="true"
    alignment="justify-between"
    perPageWireModel="perPage"
    previousPageWireModel="previousPage"
    nextPageWireModel="nextPage"
    gotoPageWireModel="gotoPage"
/>

<!-- Activity Logs Page (tanpa per page dropdown) -->
<x-superadmin.pagination 
    :currentPage="$activities->currentPage()"
    :lastPage="$activities->lastPage()"
    :total="$activities->total()"
    :perPage="$activities->perPage()"
    :perPageOptions="[10, 25, 50, 100]"
    :showPageInfo="true"
    :showPerPage="false"
    alignment="justify-between"
    perPageWireModel="perPage"
    previousPageWireModel="previousPage"
    nextPageWireModel="nextPage"
    gotoPageWireModel="gotoPage"
/>
```

**Features**:
- ✅ **Real-time Updates**: Menggunakan `wire:model.live` untuk immediate response saat mengubah perPage
- ✅ **Smart Page Numbers**: Menampilkan nomor halaman dengan ellipsis untuk jumlah halaman yang besar
- ✅ **Customizable Options**: Bisa mengatur opsi perPage (10, 25, 50, 100, atau custom)
- ✅ **Flexible Layout**: Bisa mengatur alignment (left, center, right, justify-between)
- ✅ **Optional Elements**: Bisa menampilkan/menyembunyikan page info dan perPage dropdown
- ✅ **Livewire Integration**: Full support untuk Livewire dengan customizable wire model names
- ✅ **Responsive Design**: Layout yang menyesuaikan untuk mobile dan desktop
- ✅ **Error Handling**: Robust handling untuk perPageOptions input (string atau array)

**Livewire Component Requirements**:
```php
// Di Livewire component, pastikan memiliki property dan method berikut:
public $perPage = 10;

public function updatedPerPage()
{
    $this->resetPage(); // Kembali ke halaman 1 saat perPage berubah
}

public function nextPage()
{
    $this->setPage($this->page + 1);
}

public function previousPage()
{
    $this->setPage(max(1, $this->page - 1));
}

public function gotoPage($page)
{
    $this->setPage($page);
}
```

**Advanced Features**:
- **Ellipsis Logic**: Untuk jumlah halaman > 5, menampilkan ellipsis di tengah
- **Page Info Display**: "Menampilkan X hingga Y dari Z hasil"
- **Disabled States**: Tombol previous/next disabled saat di halaman pertama/terakhir
- **Current Page Highlight**: Halaman aktif ditampilkan dengan styling berbeda
- **Hover Effects**: Smooth transitions untuk semua interactive elements

**Best Practices**:
1. **Single Source of Truth**: Hanya gunakan satu pagination component per halaman untuk menghindari konflik
2. **Consistent Naming**: Gunakan wire model names yang konsisten across components
3. **Proper Reset**: Selalu panggil `resetPage()` saat `perPage` berubah
4. **Live Binding**: Gunakan `wire:model.live` untuk real-time updates
5. **Error Prevention**: Komponen sudah handle string/array conversion untuk `perPageOptions`

---

## Style & Design System

### Color Palette
- **Primary**: Blue-600 (`#2563eb`) untuk buttons dan links
- **Success**: Green-600 (`#16a34a`) untuk success states
- **Warning**: Yellow-600 (`#ca8a04`) untuk warning states  
- **Danger**: Red-600 (`#dc2626`) untuk delete actions
- **Neutral**: Gray-600 (`#4b5563`) untuk text dan borders

### Typography
- **Header Title**: `text-2xl font-bold text-gray-900`
- **Header Description**: `text-sm text-gray-600`
- **Table Header**: `text-sm font-medium text-gray-900`
- **Body Text**: `text-sm text-gray-700`

### Spacing System
- **Header Padding**: `p-6` (24px)
- **Card Gap**: `gap-6` (24px)
- **Form Gap**: `gap-6` (24px)
- **Button Padding**: `px-4 py-2` (16px x 8px)
- **Table Padding**: `px-6 py-4` (24px x 16px)

### Component Styles

#### Cards
```css
.bg-white.rounded-xl.shadow-sm.border.border-gray-200
```

#### Buttons
```css
/* Primary */
.bg-blue-600.hover:bg-blue-700.text-white.px-4.py-2.rounded-lg.transition-colors

/* Secondary */
.border.border-gray-300.text-gray-700.hover:bg-gray-50.px-4.py-2.rounded-lg.transition-colors

/* Danger */
.bg-red-600.hover:bg-red-700.text-white.px-4.py-2.rounded-lg.transition-colors
```

#### Tables
```css
.min-w-full.divide-y.divide-gray-200
```

#### Form Elements
```css
/* Inputs */
.border.border-gray-300.rounded-lg.shadow-sm.focus:ring-blue-500.focus:border-blue-500

/* Selects */
.border.border-gray-300.rounded-md.shadow-sm.focus:ring-blue-500.focus:border-blue-500
```

### Responsive Breakpoints
- **Mobile**: Default (stacked layout)
- **Tablet**: `sm:` breakpoint (768px)
- **Desktop**: `lg:` breakpoint (1024px)

---

## Tata Letak (Layout)

### Layout Structure
```
Superadmin Layout
├── Topbar (fixed)
│   ├── Logo & App Name
│   └── Breadcrumb Navigation
├── Main Content (fluid container)
│   ├── Page Header Section (optional)
│   │   ├── Title & Description
│   │   └── Action Buttons
│   ├── Flash Messages (optional)
│   └── Content Section
│       ├── Card Wrapper
│       └── Component Content
└── Scripts Section
```

### Layout Implementation

#### 1. Superadmin Layout (`layouts.superadmin.blade.php`)
```blade
@extends('layouts.app')

@section('breadcrumb')
    <x-superadmin.breadcrumb-topbar :items="getBreadcrumb()" />
@endsection

@section('content')
<div class="container-fluid px-4">

    <!-- Page Header -->
    @hasSection('page-header')
        <div class="mb-6">
            @yield('page-header')
        </div>
    @endif

    <!-- Flash Messages -->
    @if(session()->has('success') || session()->has('error') || session()->has('info') || session()->has('warning'))
        <div class="mb-6">
            <!-- Flash message components -->
        </div>
    @endif

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            @hasSection('main-content')
                @yield('main-content')
            @else
                <x-card>
                    @yield('content-body')
                </x-card>
            @endif
        </div>
    </div>
</div>
@endsection
```

#### 2. Page Layout Pattern

**Halaman Index (Listing)**:
```blade
@extends('layouts.superadmin')

@section('page-title', 'Manajemen Pengguna')

@section('main-content')
    <livewire:superadmin.user-management />
@endsection
```

**Halaman Create/Edit (Form)**:
```blade
@extends('layouts.superadmin')

@section('page-title', 'Tambah/Edit Pengguna')

@section('page-header')
    <x-superadmin.page-header 
        title="..."
        description="..."
        :showBackButton="true"
        backRoute="..."
        backText="Kembali"
    />
@endsection

@section('main-content')
    <x-card>
        <!-- Form content -->
    </x-card>
@endsection
```

**Halaman Show (Detail)**:
```blade
@extends('layouts.superadmin')

@section('page-title', 'Detail Pengguna')

@section('page-header')
    <x-superadmin.page-header 
        title="Detail Pengguna"
        description="..."
        :showBackButton="true"
        backRoute="..."
        backText="Kembali"
        :actions="[...]"
    />
@endsection

@section('main-content')
    <x-card>
        <!-- Detail content -->
    </x-card>
@endsection
```

---

## Fungsi & Fitur

### 1. Manajemen Pengguna (CRUD)

#### Create User
- **Endpoint**: `POST /superadmin/users`
- **Validation**: `StoreUserRequest`
- **Features**:
  - Form validation untuk name, email, password
  - Optional fields: NIP, employee_type, employee_id
  - Role assignment dengan checkboxes
  - Auto-generate password hash
  - Email verification auto-set

#### Read Users
- **Endpoint**: `GET /superadmin/users`
- **Features**:
  - Pagination dengan customizable per page (10, 25, 50, 100)
  - Search by name, email, NIP
  - Filter by role
  - Filter by employee type
  - Filter by fingerprint status
  - Sort by created_at (desc)

#### Update User
- **Endpoint**: `PUT /superadmin/users/{id}`
- **Validation**: `UpdateUserRequest`
- **Features**:
  - Partial update (hanya field yang diisi)
  - Password optional (kosongkan jika tidak ingin ubah)
  - Role re-assignment
  - Prevent self-deletion

#### Delete User
- **Endpoint**: `DELETE /superadmin/users/{id}`
- **Features**:
  - Soft delete dengan confirmation modal
  - Prevent self-deletion
  - Cascade delete relationships
  - Activity logging

### 2. Role Management

#### Role Assignment
- **Many-to-Many relationship** antara User dan Role
- **Sync roles** functionality untuk replace semua roles
- **Display roles** dengan color-coded badges
- **Role filtering** di user listing

#### Role Types
- `super-admin`: Full system access
- `admin`: Administrative access
- `staff`: Regular staff access
- Custom roles dapat ditambahkan

### 3. Import Data Massal

#### Import Features
- **Source**: Tables `karyawan` dan `dosen`
- **Criteria**: Records dengan email tidak kosong
- **Process**:
  1. Preview data (count total, valid, new, existing)
  2. Create new users dengan default password `password123`
  3. Update existing users (sync name only)
  4. Assign `staff` role secara default
  5. Set email verification

#### Import Statistics
```php
$importCounts = [
    'karyawan_total' => 0,     // Total karyawan dengan email
    'karyawan_valid' => 0,    // Karyawan valid untuk import
    'karyawan_new' => 0,      // Karyawan yang akan dibuat baru
    'karyawan_existing' => 0, // Karyawan yang sudah ada
    'dosen_total' => 0,       // Total dosen dengan email
    'dosen_valid' => 0,      // Dosen valid untuk import
    'dosen_new' => 0,        // Dosen yang akan dibuat baru
    'dosen_existing' => 0,   // Dosen yang sudah ada
];
```

### 4. Fingerprint Integration

#### Fingerprint Features
- **Enable/disable fingerprint access** per user
- **Set fingerprint PIN** untuk authentication
- **Fingerprint status display** di user listing
- **Autocomplete user selection** by fingerprint PIN

#### Fingerprint Methods
```php
// Enable fingerprint access
$user->enableFingerprintAccess($pin);

// Disable fingerprint access
$user->disableFingerprintAccess();

// Set fingerprint PIN
$user->setFingerprintPin($pin);

// Find user by PIN
User::byFingerprintPin($pin)->where('fingerprint_enabled', true)->first();
```

### 5. Search & Filtering

#### Search Options
- **Text search**: name, email, NIP
- **Role filter**: dropdown selection
- **Employee type filter**: Karyawan/Dosen
- **Fingerprint status filter**: Enabled/Disabled
- **Combined filters**: multiple filters dapat digunakan bersamaan

#### Pagination
- **Bootstrap theme** untuk pagination styling
- **Customizable per page**: 10, 25, 50, 100
- **Page navigation**: Previous, Next, Go to page
- **Page info**: Display "Showing X to Y of Z results"

### 6. Activity Logging

#### Logged Activities
- User creation: `User created`
- User update: `User updated`
- User deletion: `User deleted`
- Role assignment: `Roles assigned to user`
- Fingerprint actions: `Fingerprint access enabled/disabled`, `Fingerprint PIN set`

#### Log Structure
```php
Log::info('User created', [
    'user_id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    'created_by' => auth()->id(),
]);
```

---

## Business Logic

### UserService Layer

#### Core Methods

##### Data Retrieval
```php
// Get users dengan pagination dan filtering
public function getUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator

// Get user by ID dengan relationships
public function getUserById(int $id): ?User

// Get all available roles
public function getAllRoles(): \Illuminate\Database\Eloquent\Collection

// Get user statistics
public function getUserStatistics(): array
```

##### User Management
```php
// Create user dengan validation
public function createUser(array $data): User

// Update user dengan validation
public function updateUser(int $id, array $data): User

// Delete user dengan protection
public function deleteUser(int $id): bool

// Bulk delete users
public function bulkDeleteUsers(array $userIds): array
```

##### Role Management
```php
// Get user roles
public function getUserRoles(int $id): \Illuminate\Database\Eloquent\Collection

// Assign roles to user
public function assignRoles(int $id, array $roles): User

// Bulk assign roles
public function bulkAssignRoles(array $userIds, array $roles): array
```

##### Fingerprint Management
```php
// Enable fingerprint access
public function enableFingerprintAccess(int $id, ?string $pin = null): User

// Disable fingerprint access
public function disableFingerprintAccess(int $id): User

// Set fingerprint PIN
public function setFingerprintPin(int $id, string $pin): User

// Find user by fingerprint PIN
public function findUserByFingerprintPin(string $pin): ?User

// Get users with fingerprint enabled
public function getUsersWithFingerprintEnabled(array $filters = []): \Illuminate\Database\Eloquent\Collection
```

##### Search & Utilities
```php
// Search users by multiple criteria
public function searchUsers(string $query, array $filters = []): \Illuminate\Database\Eloquent\Collection
```

### Validation Rules

#### StoreUserRequest
```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
        'nip' => ['nullable', 'string', 'max:50'],
        'employee_type' => ['nullable', 'in:employee,dosen'],
        'employee_id' => ['nullable', 'string', 'max:50'],
        'roles' => ['required', 'array'],
        'roles.*' => ['exists:roles,name'],
        'is_active' => ['boolean'],
    ];
}
```

#### UpdateUserRequest
```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user_id],
        'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        'nip' => ['nullable', 'string', 'max:50'],
        'employee_type' => ['nullable', 'in:employee,dosen'],
        'employee_id' => ['nullable', 'string', 'max:50'],
        'roles' => ['required', 'array'],
        'roles.*' => ['exists:roles,name'],
        'is_active' => ['boolean'],
    ];
}
```

### Database Transactions

Semua operasi write (create, update, delete) dibungkus dalam database transactions untuk ensure data consistency:

```php
return DB::transaction(function () use ($data) {
    // Business logic here
    // All operations succeed or all fail together
});
```

### Error Handling

#### Exception Types
- **Validation Exception**: Dilempar oleh Form Request validation
- **ModelNotFoundException**: User tidak ditemukan
- **QueryException**: Database operation failures
- **Exception**: General errors dengan user-friendly messages

#### Error Responses
```php
try {
    // Business logic
} catch (\Exception $e) {
    session()->flash('error', 'Gagal menghapus pengguna: ' . $e->getMessage());
    return false;
}
```

---

## Database Schema

### Core Tables

#### users Table
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    nip VARCHAR(50) NULL,
    employee_type ENUM('employee', 'dosen') NULL,
    employee_id VARCHAR(50) NULL,
    fingerprint_enabled BOOLEAN DEFAULT FALSE,
    fingerprint_pin VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### roles Table
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### model_has_roles Table (Many-to-Many)
```sql
CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

### Related Tables

#### employees Table (Data Source)
```sql
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nik VARCHAR(20) NOT NULL,
    nama_lengkap VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    -- ... other employee fields
);
```

#### dosens Table (Data Source)
```sql
CREATE TABLE dosens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nidn VARCHAR(20) NOT NULL,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    -- ... other lecturer fields
);
```

#### fingerprint_user_mappings Table
```sql
CREATE TABLE fingerprint_user_mappings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    fingerprint_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Indexes
```sql
-- Performance indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_name ON users(name);
CREATE INDEX idx_users_nip ON users(nip);
CREATE INDEX idx_users_employee_type ON users(employee_type);
CREATE INDEX idx_users_fingerprint_enabled ON users(fingerprint_enabled);
CREATE INDEX idx_users_fingerprint_pin ON users(fingerprint_pin);
CREATE INDEX idx_users_created_at ON users(created_at);
```

---

## Security & Authorization

### Authentication
- **Laravel Authentication**: Menggunakan built-in Laravel auth system
- **Session-based**: Session management untuk user state
- **Remember Me**: Optional remember functionality
- **Password Hashing**: Bcrypt hashing untuk password security

### Authorization (RBAC)

#### Role-Based Access Control
- **Spatie Laravel Permission**: Package untuk RBAC implementation
- **Role Hierarchy**: Support untuk role inheritance
- **Permission Management**: Fine-grained permission control

#### Role Definitions
```php
// Super Admin - Full system access
$superAdminRole = Role::create(['name' => 'super-admin']);

// Admin - Administrative access
$adminRole = Role::create(['name' => 'admin']);

// Staff - Regular staff access
$staffRole = Role::create(['name' => 'staff']);
```

#### Permission Checks
```php
// Gate definitions
Gate::define('users.view', function ($user) {
    return $user->hasAnyRole(['super-admin', 'admin']);
});

Gate::define('users.create', function ($user) {
    return $user->hasAnyRole(['super-admin', 'admin']);
});

Gate::define('users.edit', function ($user, $targetUser) {
    return $user->hasAnyRole(['super-admin', 'admin']) || 
           $user->id === $targetUser->id;
});

Gate::define('users.delete', function ($user, $targetUser) {
    return $user->hasAnyRole(['super-admin', 'admin']) && 
           $user->id !== $targetUser->id;
});
```

### Route Protection
```php
// Middleware protection
Route::prefix('superadmin')
    ->middleware(['auth', 'role:super-admin|admin'])
    ->group(function () {
        Route::resource('users', UserController::class);
    });
```

### Input Validation & Sanitization

#### Form Request Validation
- **Server-side validation**: Semua input divalidasi di server
- **Rule definitions**: Strict validation rules untuk setiap field
- **Custom messages**: User-friendly error messages

#### XSS Prevention
- **Laravel Escaping**: Automatic HTML entity escaping
- **Blade Syntax**: `{{ }}` untuk escaped output, `{!! !!}` untuk raw output
- **CSRF Protection**: Built-in CSRF token validation

#### SQL Injection Prevention
- **Eloquent ORM**: Parameterized queries
- **Query Builder**: Safe query building
- **Raw SQL**: Limited usage dengan proper escaping

### Data Protection

#### Sensitive Data
- **Password Hashing**: Bcrypt hashing dengan salt
- **Fingerprint PIN**: Encrypted storage
- **Personal Information**: Minimal exposure di logs

#### Audit Trail
- **Activity Logging**: Semua sensitive operations di-log
- **User Tracking**: Log user ID untuk setiap action
- **IP Logging**: Optional IP address logging

---

## Performance & Optimization

### Database Optimization

#### Query Optimization
```php
// Eager loading untuk prevent N+1 queries
User::with('roles')->get();

// Selective column loading
User::select(['id', 'name', 'email', 'created_at'])->get();

// Proper indexing strategy
Schema::table('users', function (Blueprint $table) {
    $table->index(['email', 'is_active']);
    $table->index(['employee_type', 'created_at']);
});
```

#### Caching Strategy
```php
// Cache user statistics
$stats = Cache::remember('user_statistics', 3600, function () {
    return $this->getUserStatistics();
});

// Cache role data
$roles = Cache::remember('all_roles', 86400, function () {
    return $this->getAllRoles();
});
```

### Frontend Optimization

#### Livewire Performance
```php
// Lazy loading untuk large datasets
public function getUsers(): LengthAwarePaginator
{
    return User::paginate($this->perPage);
}

// Debounce search input
protected $rules = [
    'search' => 'min:2'
];

// Computed properties untuk expensive operations
public function getFilteredUsersProperty()
{
    return $this->users->filter(...);
}
```

#### Asset Optimization
- **Vite Build**: Optimized JavaScript and CSS bundling
- **Lazy Loading**: Components loaded on demand
- **Minification**: Automatic asset minification

### Memory Management

#### Large Dataset Handling
```php
// Chunk processing untuk import/export
DB::table('employees')->chunk(1000, function ($employees) {
    foreach ($employees as $employee) {
        // Process chunk
    }
});

// Pagination untuk prevent memory overload
$users = User::paginate(50);
```

#### Garbage Collection
- **Session Cleanup**: Automatic expired session cleanup
- **Cache Management**: TTL-based cache expiration
- **Log Rotation**: Automatic log file rotation

---

## Testing

### Unit Tests

#### UserService Test
```php
class UserServiceTest extends TestCase
{
    protected $userService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = app(UserService::class);
    }
    
    public function test_create_user()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'roles' => ['staff']
        ];
        
        $user = $this->userService->createUser($data);
        
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
        
        $this->assertTrue($user->hasRole('staff'));
    }
    
    public function test_delete_user_prevents_self_deletion()
    {
        $this->expectException(\Exception::class);
        
        $this->userService->deleteUser(auth()->id());
    }
}
```

### Feature Tests

#### User Management Test
```php
class UserManagementTest extends TestCase
{
    public function test_admin_can_view_users_page()
    {
        $admin = User::factory()->create()->assignRole('admin');
        
        $response = $this->actingAs($admin)
            ->get(route('superadmin.users.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');
    }
    
    public function test_user_can_be_created()
    {
        $admin = User::factory()->create()->assignRole('admin');
        
        $response = $this->actingAs($admin)
            ->post(route('superadmin.users.store'), [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => ['staff']
            ]);
        
        $response->assertRedirect(route('superadmin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com'
        ]);
    }
}
```

#### Livewire Component Tests
```php
class UserManagementTest extends TestCase
{
    public function test_user_management_component_renders()
    {
        Livewire::test(UserManagement::class)
            ->assertSee('Manajemen Pengguna')
            ->assertViewHas('users');
    }
    
    public function test_search_filters_users()
    {
        Livewire::test(UserManagement::class)
            ->set('search', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith');
    }
}
```

### Test Coverage Goals
- **Unit Tests**: 80% coverage untuk service layer
- **Feature Tests**: 90% coverage untuk user flows
- **Livewire Tests**: 85% coverage untuk interactive components
- **Integration Tests**: 75% coverage untuk API endpoints

---

## Best Practices

### Code Organization

#### File Structure
- **Single Responsibility**: Setiap class memiliki satu tanggung jawab jelas
- **Consistent Naming**: PSR-4 autoloading standards
- **Logical Grouping**: Related files grouped together

#### Code Style
- **PSR-12**: PHP coding standards
- **Laravel Conventions**: Framework-specific patterns
- **Type Hinting**: Strict type declarations
- **Documentation**: PHPDoc blocks untuk methods

### Performance Best Practices

#### Database
```php
// ✅ Good: Use eager loading
$users = User::with('roles')->get();

// ❌ Bad: N+1 queries
$users = User::all();
foreach ($users as $user) {
    $user->roles; // N+1 query
}

// ✅ Good: Use pagination
$users = User::paginate(50);

// ❌ Bad: Load all records
$users = User::all();
```

#### Frontend
```php
// ✅ Good: Use computed properties
public function getFilteredUsersProperty()
{
    return $this->users->filter(...);
}

// ❌ Bad: Heavy processing in render
public function render()
{
    $filtered = $this->users->filter(...); // Heavy computation
    return view('...', ['filtered' => $filtered]);
}
```

### Security Best Practices

#### Input Validation
```php
// ✅ Good: Use form requests
public function store(StoreUserRequest $request)
{
    $validated = $request->validated();
    // Safe to use $validated
}

// ❌ Bad: Manual validation
public function store(Request $request)
{
    $request->validate([...]); // Easy to forget
}
```

#### Authorization
```php
// ✅ Good: Use gates and policies
$this->authorize('update', $user);

// ❌ Bad: Manual checks
if (auth()->user()->id !== $user->id) {
    abort(403);
}
```

### Maintainability Best Practices

#### Error Handling
```php
// ✅ Good: Specific exception handling
try {
    $user = $this->userService->createUser($data);
} catch (ValidationException $e) {
    return back()->withErrors($e->errors());
} catch (\Exception $e) {
    Log::error('User creation failed: ' . $e->getMessage());
    return back()->with('error', 'Failed to create user');
}

// ❌ Bad: Generic error handling
try {
    $user = $this->userService->createUser($data);
} catch (\Exception $e) {
    return back()->with('error', 'Something went wrong');
}
```

#### Logging
```php
// ✅ Good: Structured logging
Log::info('User created', [
    'user_id' => $user->id,
    'created_by' => auth()->id(),
    'ip_address' => request()->ip()
]);

// ❌ Bad: Unstructured logging
Log::info('User created by ' . auth()->id());
```

### Documentation Best Practices

#### Code Documentation
```php
/**
 * Create a new user with the given data.
 *
 * @param array $data User data including name, email, password, and roles
 * @return User The created user instance
 * @throws ValidationException If validation fails
 * @throws \Exception If database operation fails
 */
public function createUser(array $data): User
{
    // Implementation
}
```

#### API Documentation
- **Route Definitions**: Clear route naming conventions
- **Response Formats**: Consistent JSON structure
- **Error Codes**: Standard HTTP status codes
- **Versioning**: API versioning strategy

---

## Conclusion

Menu Manajemen Pengguna merupakan modul yang well-architected dengan separation of concerns yang jelas, reusable components, dan comprehensive feature set. Modul ini mengikuti best practices dalam hal:

- **Code Organization**: Clean architecture dengan service layer
- **UI/UX**: Consistent design system dengan reusable components
- **Performance**: Optimized queries dan caching strategies
- **Security**: Comprehensive authorization dan input validation
- **Testing**: Adequate test coverage untuk reliability
- **Maintainability**: Well-documented code dengan clear separation of concerns

Modul ini siap untuk production use dan dapat dengan mudah di-extend untuk menambahkan fitur baru sesuai kebutuhan bisnis.
