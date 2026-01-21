# Dokumentasi Komponen Confirmation Modal

## Table of Contents
1. [Overview](#overview)
2. [Installation](#installation)
3. [Props](#props)
4. [Usage Examples](#usage-examples)
5. [Modal Types](#modal-types)
6. [Advanced Features](#advanced-features)
7. [Best Practices](#best-practices)

---

## Overview

`<x-superadmin.confirmation-modal>` adalah komponen Blade yang reusable untuk menampilkan modal konfirmasi. Komponen ini dirancang untuk memberikan pengalaman pengguna yang konsisten di seluruh aplikasi saat membutuhkan konfirmasi sebelum melakukan aksi tertentu.

### Fitur Utama:
- ✅ **Reusable**: Dapat digunakan untuk berbagai tipe konfirmasi
- ✅ **Multiple Types**: Support untuk danger, warning, dan info
- ✅ **Customizable**: Title, message, button text dapat dikustomisasi
- ✅ **Livewire Integration**: Full support untuk Livewire actions
- ✅ **Responsive Design**: Mobile-friendly layout
- ✅ **Accessibility**: Keyboard navigation, ARIA labels
- ✅ **Animation**: Smooth transitions dengan Alpine.js

---

## Installation

Komponen ini sudah tersedia di:
```
resources/views/components/superadmin/confirmation-modal.blade.php
```

Tidak ada instalasi tambahan yang diperlukan selain mengikuti struktur project yang sudah ada.

---

## Props

Komponen ini menerima props berikut:

| Prop | Tipe | Default | Required | Deskripsi |
|------|------|---------|----------|-----------|
| `id` | string | `'confirmation-modal'` | No | Unique ID untuk modal instance |
| `title` | string | `'Konfirmasi Hapus'` | No | Judul modal |
| `message` | string | `'Apakah Anda yakin?'` | No | Pesan konfirmasi (support HTML) |
| `confirmText` | string | `'Hapus'` | No | Teks tombol konfirmasi |
| `cancelText` | string | `'Batal'` | No | Teks tombol batal |
| `type` | string | `'danger'` | No | Tipe modal: `danger`, `warning`, `info` |
| `confirmAction` | string | `null` | No | Nama Livewire action yang akan dipanggil |
| `confirmParams` | array | `[]` | No | Parameter untuk Livewire action |
| `show` | boolean | `false` | No | Initial show state |
| `maxWidth` | string | `'sm'` | No | Modal size: `sm`, `md`, `lg`, `xl`, `2xl` |
| `icon` | string | `null` | No | Custom icon (optional) |

---

## Usage Examples

### Basic Usage

```blade
<x-superadmin.confirmation-modal 
    id="delete-modal"
    title="Hapus Data"
    message="Apakah Anda yakin ingin menghapus data ini?"
    type="danger"
    confirmText="Hapus"
    cancelText="Batal"
    :show="$showDeleteModal"
    confirmAction="deleteData"
/>
```

### Dengan Parameter

```blade
<x-superadmin.confirmation-modal 
    id="delete-user-modal"
    title="Hapus Pengguna"
    message="Yakin menghapus pengguna <strong>{{ $user->name }}</strong>?"
    type="danger"
    confirmText="Hapus"
    cancelText="Batal"
    :show="$showDeleteModal"
    confirmAction="deleteUser"
    :confirmParams="['id' => $user->id]"
    maxWidth="sm"
/>
```

### Warning Type

```blade
<x-superadmin.confirmation-modal 
    id="deactivate-modal"
    title="Nonaktifkan Akun"
    message="Nonaktifkan akun <strong>{{ $user->name }}</strong>?<br><small>Akun tidak akan dapat digunakan sementara.</small>"
    type="warning"
    confirmText="Nonaktifkan"
    cancelText="Batal"
    :show="$showDeactivateModal"
    confirmAction="deactivateUser"
    maxWidth="md"
/>
```

### Info Type

```blade
<x-superadmin.confirmation-modal 
    id="info-modal"
    title="Informasi"
    message="Proses ini akan memakan waktu beberapa menit."
    type="info"
    confirmText="Lanjutkan"
    cancelText="Kembali"
    :show="$showInfoModal"
    confirmAction="continueProcess"
    maxWidth="lg"
/>
```

---

## Modal Types

### 1. Danger Type
- **Usage**: Aksi destruktif seperti hapus data
- **Colors**: Red theme dengan exclamation triangle icon
- **Icon**: exclamation-triangle

```blade
type="danger"
```

### 2. Warning Type
- **Usage**: Aksi yang perlu perhatian seperti nonaktifkan
- **Colors**: Yellow theme dengan exclamation circle icon
- **Icon**: exclamation-circle

```blade
type="warning"
```

### 3. Info Type
- **Usage**: Informasi umum atau konfirmasi non-destruktif
- **Colors**: Blue theme dengan information circle icon
- **Icon**: information-circle

```blade
type="info"
```

---

## Advanced Features

### Custom Icons

Anda dapat menggunakan custom icons dengan prop `icon`:

```blade
<x-superadmin.confirmation-modal 
    id="custom-modal"
    title="Custom Icon"
    message="Modal dengan custom icon"
    type="info"
    icon="check-circle"  // Menggunakan icon dari Heroicons
    :show="$showCustomModal"
/>
```

### Multiple Instances di Satu Halaman

Karena menggunakan unique ID, Anda dapat menggunakan multiple instances:

```blade
<!-- Modal 1 -->
<x-superadmin.confirmation-modal 
    id="delete-modal"
    title="Hapus Data"
    :show="$showDeleteModal"
    confirmAction="deleteData"
/>

<!-- Modal 2 -->
<x-superadmin.confirmation-modal 
    id="deactivate-modal"
    title="Nonaktifkan Data"
    :show="$showDeactivateModal"
    confirmAction="deactivateData"
/>
```

### Dynamic Message dengan HTML

Gunakan `{!! !!}` untuk HTML content di message:

```blade
<x-superadmin.confirmation-modal 
    id="complex-modal"
    title="Konfirmasi Kompleks"
    message="{!! "
        <div class='text-left'>
            <p><strong>Item:</strong> {{ $item->name }}</p>
            <p><strong>Kategori:</strong> {{ $item->category }}</p>
            <p class='text-red-600 mt-2'>Aksi ini tidak dapat dibatalkan!</p>
        </div>
    " !!}"
    :show="$showComplexModal"
/>
```

---

## Best Practices

### 1. Livewire Component Integration

```php
// Di Livewire component
public $showDeleteModal = false;
public $itemToDelete;

public function confirmDelete($id)
{
    $this->itemToDelete = $this->service->findById($id);
    $this->showDeleteModal = true;
}

public function delete()
{
    if ($this->itemToDelete) {
        $this->service->delete($this->itemToDelete->id);
        $this->showDeleteModal = false;
        $this->itemToDelete = null;
        
        session()->flash('success', 'Data berhasil dihapus');
    }
}
```

### 2. Consistent Naming

Gunakan naming convention yang konsisten:

```blade
<!-- ✅ Good -->
id="deleteUserModal"
show="$showDeleteUserModal"
confirmAction="deleteUser"

<!-- ❌ Avoid -->
id="modal1"
show="$isModalOpen"
confirmAction="doSomething"
```

### 3. Proper State Management

```php
// ✅ Good practice
public function delete()
{
    try {
        if ($this->itemToDelete) {
            $this->service->delete($this->itemToDelete->id);
            $this->showDeleteModal = false;
            $this->itemToDelete = null;
            // ... success message
        }
    } catch (\Exception $e) {
        // ... error handling
    }
}

// ❌ Bad practice (lupa reset state)
public function delete()
{
    $this->service->delete($this->itemToDelete->id);
    $this->showDeleteModal = false;
    // Lupa reset $itemToDelete
}
```

### 4. Security Considerations

```blade
<!-- ✅ Aman - escaped input -->
message="Yakin menghapus <strong>{{ $user->name }}</strong>?"

<!-- ❌ Berbahaya - raw HTML dari user input -->
message="{!! $userInput !!}" // Hanya untuk trusted content
```

### 5. Accessibility

Komponen ini sudah include:
- Keyboard navigation (ESC to close, Tab navigation)
- ARIA labels dan roles
- Focus management
- Screen reader support

---

## Troubleshooting

### Modal tidak muncul
Pastikan:
1. Prop `:show` menggunakan boolean yang benar
2. ID modal unik di halaman
3. Tidak ada JavaScript errors

### Livewire action tidak terpanggil
Pastikan:
1. Nama action di `confirmAction` benar
2. Method ada di Livewire component
3. Parameter di `confirmParams` valid

### Styling tidak berfungsi
Pastikan:
1. Menggunakan type yang valid (`danger`, `warning`, `info`)
2. Tailwind CSS sudah ter-load dengan benar

---

## Migration dari Modal Lama

### Sebelum (hard-coded modal):
```blade
@if($showDeleteModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-sm">
            <div class="p-6">
                <!-- Hard-coded content -->
            </div>
        </div>
    </div>
@endif
```

### Sesudah (reusable component):
```blade
<x-superadmin.confirmation-modal 
    id="showDeleteModal"
    title="Hapus Pengguna"
    message="Yakin menghapus <strong>{{ $userToDelete->name }}</strong>?"
    type="danger"
    confirmText="Hapus"
    cancelText="Batal"
    :show="$showDeleteModal"
    confirmAction="delete"
    maxWidth="sm"
/>
```

---

## Conclusion

Komponen `<x-superadmin.confirmation-modal>` menyediakan solusi yang konsisten dan reusable untuk kebutuhan modal konfirmasi di aplikasi. Dengan mengikuti best practices yang telah ditentukan, Anda dapat memastikan pengalaman pengguna yang konsisten dan maintainability code yang baik.

Untuk pertanyaan atau bantuan tambahan, silakan hubungi tim development.
