# Dokumentasi Reusable Components

## Table of Contents
1. [Overview](#overview)
2. [Superadmin Components](#superadmin-components)
3. [General Components](#general-components)
4. [Helper Functions](#helper-functions)
5. [Best Practices](#best-practices)
6. [Usage Examples](#usage-examples)

---

## Overview

Project ini memiliki sistem reusable components yang well-structured untuk memastikan konsistensi UI/UX dan mempercepat development. Components terbagi menjadi dua kategori utama: **Superadmin Components** (khusus untuk admin panel) dan **General Components** (untuk penggunaan umum).

### Keuntungan Menggunakan Reusable Components:
- ✅ **Consistency**: Tampilan yang seragam di seluruh aplikasi
- ✅ **Maintainability**: Perubahan hanya perlu dilakukan di satu tempat
- ✅ **Development Speed**: Komponen siap pakai untuk fitur umum
- ✅ **Accessibility**: Built-in accessibility features
- ✅ **Responsive Design**: Mobile-friendly dari awal

---

## Superadmin Components

Components khusus untuk admin panel dengan namespace `x-superadmin.*`.

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
    :actions="
        <button class='bg-blue-600 text-white px-4 py-2 rounded-lg'>
            Custom Action
        </button>
    "
/>
```

**Features**:
- ✅ Responsive layout (flex column di mobile, row di desktop)
- ✅ Consistent styling (bg-white, rounded-xl, shadow-sm, border, p-6)
- ✅ Optional back button dengan customizable route dan text
- ✅ Custom actions parameter untuk additional buttons
- ✅ Dynamic title dan description

### 2. Breadcrumb Topbar Component (`<x-superadmin.breadcrumb-topbar>`)

**Lokasi**: `resources/views/components/superadmin/breadcrumb-topbar.blade.php`

**Deskripsi**: Komponen breadcrumb untuk navigasi hierarkis di topbar.

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

**Usage Example**:
```blade
<x-superadmin.breadcrumb-topbar :items="[
    ['title' => 'Dashboard', 'url' => route('dashboard')],
    ['title' => 'Manajemen Pengguna', 'url' => route('superadmin.users.index')],
    ['title' => 'Edit Pengguna']
]" />
```

**Auto-Generation**:
```blade
<!-- Breadcrumb otomatis dari URL (menggunakan helper function) -->
<x-superadmin.breadcrumb-topbar :items="getBreadcrumb()" />
```

**Features**:
- ✅ Automatic separator dengan SVG icon
- ✅ Clickable items untuk parent pages
- ✅ Current page highlighting (tanpa link)
- ✅ Responsive design
- ✅ Hover effects dan transitions

### 3. Flash Messages Component (`<x-superadmin.flash-messages>`)

**Lokasi**: `resources/views/components/superadmin/flash-messages.blade.php`

**Deskripsi**: Komponen untuk menampilkan flash messages (success, error, warning, info) dengan animations dan auto-dismiss.

**Props**: Tidak ada props (membaca dari session)

**Usage Example**:
```blade
<!-- Otomatis menampilkan semua flash messages dari session -->
<x-superadmin.flash-messages />
```

**Session Variables**:
```php
// Di controller
session()->flash('success', 'Data berhasil disimpan!');
session()->flash('error', 'Terjadi kesalahan!');
session()->flash('warning', 'Perhatian: data tidak lengkap!');
session()->flash('info', 'Informasi penting untuk Anda.');
```

**Features**:
- ✅ Support untuk 4 tipe messages: success, error, warning, info
- ✅ Auto-dismiss dengan smooth animations
- ✅ Manual dismiss button
- ✅ Color-coded dengan appropriate icons
- ✅ Responsive design
- ✅ Accessibility support (ARIA labels)

### 4. Pagination Component (`<x-superadmin.pagination>`)

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

**Livewire Component Requirements**:
```php
// Di Livewire component, pastikan memiliki property dan method berikut:
class UserManagement extends Component
{
    use WithPagination;
    
    public $page = 1;
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
}
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

### 5. Confirmation Modal Component (`<x-superadmin.confirmation-modal>`)

**Lokasi**: `resources/views/components/superadmin/confirmation-modal.blade.php`

**Deskripsi**: Komponen modal konfirmasi yang reusable untuk aksi-aksi krusial seperti delete, dengan berbagai tipe dan custom actions.

**Props**:
```php
@props([
    'id' => 'confirmation-modal',              // Unique ID untuk modal instance
    'title' => 'Konfirmasi Hapus',            // Modal title
    'message' => 'Apakah Anda yakin?',         // Confirmation message
    'confirmText' => 'Hapus',                   // Confirm button text
    'cancelText' => 'Batal',                   // Cancel button text
    'type' => 'danger',                         // Modal type: danger, warning, info
    'confirmAction' => null,                    // Livewire action name
    'confirmParams' => [],                      // Parameters untuk Livewire action
    'show' => false,                           // Initial show state
    'maxWidth' => 'sm',                         // Modal size: sm, md, lg, xl, 2xl
    'icon' => null,                            // Custom icon (optional)
])
```

**Usage Examples**:
```blade
<!-- Basic Delete Confirmation -->
<x-superadmin.confirmation-modal 
    id="delete-user-modal"
    title="Konfirmasi Hapus"
    message="Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan."
    confirmText="Hapus"
    cancelText="Batal"
    type="danger"
    :confirmAction="'deleteUser'"
    :confirmParams="[$userId]"
/>

<!-- Warning Confirmation -->
<x-superadmin.confirmation-modal 
    id="deactivate-user-modal"
    title="Konfirmasi Nonaktif"
    message="Apakah Anda yakin ingin menonaktifkan pengguna ini?"
    confirmText="Nonaktifkan"
    cancelText="Batal"
    type="warning"
    :confirmAction="'deactivateUser'"
    :confirmParams="[$userId]"
    maxWidth="md"
/>

<!-- Info Confirmation -->
<x-superadmin.confirmation-modal 
    id="reset-password-modal"
    title="Reset Password"
    message="Password akan di-reset ke nilai default. Pengguna akan diminta untuk mengganti password pada login berikutnya."
    confirmText="Reset Password"
    cancelText="Batal"
    type="info"
    :confirmAction="'resetPassword'"
    :confirmParams="[$userId]"
    icon="lock-closed"
/>
```

**Trigger Modal dari Button**:
```blade
<!-- Trigger modal dengan event -->
<button 
    onclick="Livewire.emit('open-modal', 'delete-user-modal')"
    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700"
>
    Hapus Pengguna
</button>

<!-- Atau dengan wire:click di Livewire -->
<button 
    wire:click="$set('showDeleteModal', true)"
    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700"
>
    Hapus Pengguna
</button>
```

**Modal Types**:
- **`danger`**: Red theme untuk aksi destruktif (delete, remove)
- **`warning`**: Yellow theme untuk aksi berhati-hati (deactivate, reset)
- **`info`**: Blue theme untuk informasi (reset password, send notification)

**Features**:
- ✅ **Multiple Types**: Support untuk danger, warning, dan info themes
- ✅ **Livewire Integration**: Bisa menjalankan Livewire methods dengan parameters
- ✅ **Customizable**: Title, message, button text, dan icon bisa di-custom
- ✅ **Responsive Design**: Modal sizes dari sm sampai 2xl
- ✅ **Accessibility**: Keyboard navigation, focus management, ARIA labels
- ✅ **Animations**: Smooth transitions dan hover effects
- ✅ **Event System**: Buka/tutup modal dengan events atau wire:click

---

## General Components

Components untuk penggunaan umum di seluruh aplikasi dengan namespace `x-*`.

### 1. Modal Component (`<x-modal>`)

**Lokasi**: `resources/views/components/modal.blade.php`

**Deskripsi**: Base modal component yang digunakan oleh komponen lain. Menyediakan modal functionality dengan accessibility features.

**Props**:
```php
@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])
```

**Usage Example**:
```blade
<x-modal name="custom-modal" :show="$showModal" maxWidth="lg">
    <div class="p-6">
        <h3 class="text-lg font-medium mb-4">Custom Modal Content</h3>
        <p>Ini adalah konten modal kustom.</p>
        <button onclick="Livewire.emit('close-modal', 'custom-modal')" 
                class="mt-4 bg-gray-500 text-white px-4 py-2 rounded">
            Close
        </button>
    </div>
</x-modal>
```

**Features**:
- ✅ **Accessibility**: Full keyboard navigation dan focus management
- ✅ **Event System**: Buka/tutup dengan `open-modal` dan `close-modal` events
- ✅ **Responsive**: Multiple size options (sm, md, lg, xl, 2xl)
- ✅ **Animations**: Smooth transitions dan backdrop effects
- ✅ **Focus Management**: Auto-focus pada interactive elements

### 2. Alert Component (`<x-alert>`)

**Lokasi**: `resources/views/components/alert.blade.php`

**Deskripsi**: Komponen alert untuk menampilkan pesan dengan berbagai tipe.

**Props**:
```php
@props([
    'type' => 'info',        // info, success, warning, error
    'message' => '',         // Alert message
    'dismissible' => false,  // Show dismiss button
])
```

**Usage Example**:
```blade
<x-alert type="success" message="Data berhasil disimpan!" />
<x-alert type="error" message="Terjadi kesalahan!" dismissible />
<x-alert type="warning" message="Peringatan!" />
<x-alert type="info" message="Informasi" dismissible />
```

### 3. Card Component (`<x-card>`)

**Lokasi**: `resources/views/components/card.blade.php`

**Deskripsi**: Wrapper component untuk content cards dengan consistent styling.

**Props**:
```php
@props([
    'padding' => 'normal',    // none, small, normal, large
    'shadow' => 'normal',     // none, small, normal, large
])
```

**Usage Example**:
```blade
<x-card>
    <h3 class="text-lg font-medium mb-4">Card Title</h3>
    <p>Card content goes here.</p>
</x-card>

<x-card padding="large" shadow="large">
    <h3 class="text-lg font-medium mb-4">Large Card</h3>
    <p>Card with large padding and shadow.</p>
</x-card>
```

### 4. Button Components

#### Primary Button (`<x-primary-button>`)
```blade
<x-primary-button>Submit</x-primary-button>
<x-primary-button disabled>Disabled</x-primary-button>
```

#### Secondary Button (`<x-secondary-button>`)
```blade
<x-secondary-button>Cancel</x-secondary-button>
```

#### Danger Button (`<x-danger-button>`)
```blade
<x-danger-button>Delete</x-danger-button>
```

#### Icon Button (`<x-icon-button>`)
```blade
<x-icon-button icon="trash" class="text-red-600" />
```

### 5. Form Components

#### Text Input (`<x-text-input>`)
```blade
<x-text-input label="Name" name="name" :value="$user->name" required />
<x-text-input label="Email" name="email" type="email" />
```

#### Select (`<x-select>`)
```blade
<x-select label="Role" name="role" :options="$roles" />
```

#### Form Field (`<x-form-field>`)
```blade
<x-form-field label="Username" name="username" required>
    <x-text-input name="username" />
</x-form-field>
```

### 6. Table Component (`<x-table>`)

**Lokasi**: `resources/views/components/table.blade.php`

**Deskripsi**: Responsive table component dengan consistent styling.

**Usage Example**:
```blade
<x-table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <x-icon-button icon="edit" />
                    <x-icon-button icon="trash" class="text-red-600" />
                </td>
            </tr>
        @endforeach
    </tbody>
</x-table>
```

---

## Helper Functions

Helper functions yang tersedia untuk memudahkan development.

### 1. `getBreadcrumb()` Function

**Lokasi**: `app/helpers.php`

**Deskripsi**: Generate breadcrumb items otomatis berdasarkan current URL atau custom items.

**Usage**:
```php
// Auto-generate dari URL
$breadcrumbs = getBreadcrumb();

// Custom breadcrumb items
$customBreadcrumbs = getBreadcrumb([
    ['title' => 'Dashboard', 'url' => route('dashboard')],
    ['title' => 'Custom Page']
]);
```

**Auto-Generation Logic**:
- Membaca URL segments
- Convert ke readable titles
- Generate hierarchical navigation
- Support untuk custom mappings

**Custom Mappings**:
```php
// Di helper function, title mappings:
$titleMappings = [
    'superadmin' => 'Superadmin',
    'users' => 'Manajemen Pengguna',
    'roles' => 'Manajemen Peran',
    'activity-logs' => 'Log Aktifitas',
    'create' => 'Tambah',
    'edit' => 'Edit',
    'show' => 'Detail'
];
```

### 2. `generateBreadcrumb()` Function

**Lokasi**: `app/helpers.php`

**Deskripsi**: Core function untuk generate breadcrumb dari URL structure.

**Usage**:
```php
// Generate breadcrumb otomatis
$breadcrumbs = generateBreadcrumb();

// Result:
// [
//     ['title' => 'Superadmin', 'url' => '/superadmin'],
//     ['title' => 'Manajemen Pengguna', 'url' => '/superadmin/users'],
//     ['title' => 'Edit Pengguna'] // Current page
// ]
```

### 3. Role Helper Functions

**Lokasi**: `app/helpers.php`

**Available Functions**:
```php
// Set active role
setActiveRole('admin');

// Get current active role
$role = getActiveRole();

// Check if user has specific role
$hasRole = hasRole('admin');

// Check if current active role matches
$isActive = isActiveRole('admin');

// Get all user roles
$roles = getUserRoles();

// Check if user can switch to role
$canSwitch = canSwitchToRole('staff');
```

---

## Best Practices

### 1. Component Usage Guidelines

#### **Do's**:
- ✅ Gunakan reusable components untuk fitur umum
- ✅ Ikuti props structure yang sudah ditentukan
- ✅ Gunakan semantic naming untuk component IDs
- ✅ Test components di berbagai screen sizes
- ✅ Consider accessibility saat menggunakan components

#### **Don'ts**:
- ❌ Jangan hard-code styles di dalam component usage
- ❌ Jangan override component styles unless necessary
- ❌ Jangan gunakan components untuk one-off use cases
- ❌ Jangan lupa include required props

### 2. Performance Optimization

#### **Component Best Practices**:
```blade
<!-- ✅ Good: Lazy loading untuk heavy components -->
@if($showModal)
    <x-superadmin.confirmation-modal />
@endif

<!-- ✅ Good: Conditional rendering -->
<x-superadmin.page-header :showBackButton="$shouldShowBack" />

<!-- ❌ Bad: Selalu render heavy components -->
<x-superadmin.confirmation-modal show="false" />
```

#### **Livewire Integration**:
```php
// ✅ Good: Use wire:model.live untuk real-time updates
public $search = '';

// Di view
<input wire:model.live.debounce.300ms="search" />

// ✅ Good: Proper pagination setup
public $page = 1;
public $perPage = 10;

public function updatedPerPage()
{
    $this->resetPage();
}
```

### 3. Accessibility Guidelines

#### **Modal Components**:
- Selalu gunakan proper focus management
- Include keyboard navigation (Escape, Tab)
- Provide ARIA labels dan roles
- Ensure modal traps focus saat dibuka

#### **Form Components**:
- Gunakan proper label associations
- Include error messages untuk invalid inputs
- Provide keyboard navigation
- Use semantic HTML elements

### 4. Consistency Guidelines

#### **Styling Consistency**:
- Gunakan spacing system yang konsisten (p-4, p-6, etc.)
- Ikuti color palette yang sudah ditentukan
- Gunakan consistent border radius (rounded-lg, rounded-xl)
- Maintain consistent typography scale

#### **Naming Conventions**:
- Component names: kebab-case (e.g., `page-header`)
- Props: camelCase (e.g., `showBackButton`)
- CSS classes: kebab-case (e.g., `bg-blue-600`)
- IDs: kebab-case dengan descriptive names

---

## Usage Examples

### 1. Complete Page Example

```blade
@extends('layouts.superadmin')

@section('page-title', 'Manajemen Pengguna')

@section('page-header')
    <x-superadmin.page-header 
        title="Manajemen Pengguna"
        description="Kelola data pengguna sistem"
        :actions="
            <a href='{{ route('superadmin.users.create') }}' 
               class='bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors'>
                Tambah Pengguna
            </a>
        "
    />
@endsection

@section('main-content')
    <!-- Flash Messages -->
    <x-superadmin.flash-messages />
    
    <!-- Content Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <!-- Table with pagination -->
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <!-- Table content -->
            </table>
        </div>
        
        <!-- Pagination -->
        <x-superadmin.pagination 
            :currentPage="$users->currentPage()"
            :lastPage="$users->lastPage()"
            :total="$users->total()"
            :perPage="$users->perPage()"
            :showPageInfo="true"
            :showPerPage="true"
            perPageWireModel="perPage"
            previousPageWireModel="previousPage"
            nextPageWireModel="nextPage"
            gotoPageWireModel="gotoPage"
        />
    </div>
@endsection

<!-- Confirmation Modal -->
<x-superadmin.confirmation-modal 
    id="delete-user-modal"
    title="Konfirmasi Hapus"
    message="Apakah Anda yakin ingin menghapus pengguna ini?"
    confirmText="Hapus"
    cancelText="Batal"
    type="danger"
    :confirmAction="'deleteUser'"
/>
```

### 2. Livewire Component Example

```php
<?php

namespace App\Livewire\Superadmin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public $page = 1;
    public $search = '';
    public $perPage = 10;
    public $showDeleteModal = false;
    public $selectedUserId = null;

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->paginate($this->perPage);

        return view('livewire.superadmin.user-management', [
            'users' => $users,
        ]);
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();
        
        session()->flash('success', 'Pengguna berhasil dihapus!');
        $this->showDeleteModal = false;
    }

    public function confirmDelete($userId)
    {
        $this->selectedUserId = $userId;
        $this->showDeleteModal = true;
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
}
```

### 3. Form with Validation Example

```blade
<x-superadmin.page-header 
    title="Tambah Pengguna"
    description="Formulir untuk menambah pengguna baru"
    :showBackButton="true"
    backRoute="{{ route('superadmin.users.index') }}"
/>

<x-superadmin.flash-messages />

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <form wire:submit="storeUser">
        <!-- Name Field -->
        <x-form-field label="Nama Lengkap" name="name" required>
            <x-text-input 
                wire:model="name" 
                name="name" 
                required
            />
            @error('name')
                <x-input-error :message="$message" />
            @enderror
        </x-form-field>

        <!-- Email Field -->
        <x-form-field label="Email" name="email" required>
            <x-text-input 
                wire:model="email" 
                name="email" 
                type="email"
                required
            />
            @error('email')
                <x-input-error :message="$message" />
            @enderror
        </x-form-field>

        <!-- Role Selection -->
        <x-form-field label="Peran" name="roles" required>
            <x-select 
                wire:model="roles" 
                name="roles" 
                :options="$roleOptions"
                multiple
            />
            @error('roles')
                <x-input-error :message="$message" />
            @enderror
        </x-form-field>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-3 mt-6">
            <a href="{{ route('superadmin.users.index') }}" 
               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Batal
            </a>
            <x-primary-button type="submit">
                Simpan Pengguna
            </x-primary-button>
        </div>
    </form>
</div>
```

---

## Conclusion

Reusable components system di project ini dirancang untuk:

1. **Consistency**: Memastikan tampilan yang seragam di seluruh aplikasi
2. **Efficiency**: Mempercepat development dengan komponen siap pakai
3. **Maintainability**: Memudahkan maintenance dan updates
4. **Accessibility**: Built-in support untuk a11y standards
5. **Scalability**: Mudah ditambah dan dikustomisasi

Dengan mengikuti guidelines dan best practices ini, development akan lebih cepat, konsisten, dan maintainable. Selalu gunakan reusable components yang tersedia sebelum membuat custom solutions.

---

## Additional Resources

- [Laravel Blade Components Documentation](https://laravel.com/docs/blade#components)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Livewire Documentation](https://laravel-livewire.com/docs)
- [Web Content Accessibility Guidelines (WCAG)](https://www.w3.org/WAI/WCAG21/quickref/)
