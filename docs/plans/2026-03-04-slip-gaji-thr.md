# Slip Gaji THR Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Menambahkan mode THR ke sistem slip gaji dengan 4 template PDF baru untuk berbagai tipe karyawan.

**Architecture:** Menambahkan enum `thr` ke field `mode` pada `slip_gaji_header`, membuat 4 template PDF THR baru dengan komponen yang berbeda untuk setiap tipe karyawan, dan memodifikasi service untuk mendukung template selection.

**Tech Stack:** Laravel 12, Blade Templates, MySQL, Browsershot (PDF generation)

---

## Task 1: Database Migration - Tambah Mode THR

**Files:**
- Create: `database/migrations/2026_03_04_000000_add_thr_mode_to_slip_gaji_header_table.php`

**Step 1: Buat migration file**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE slip_gaji_header MODIFY COLUMN mode ENUM('standard', 'gaji_13', 'thr') DEFAULT 'standard'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE slip_gaji_header MODIFY COLUMN mode ENUM('standard', 'gaji_13') DEFAULT 'standard'");
    }
};
```

**Step 2: Run migration**

Run: `php artisan migrate`
Expected: Migration success

**Step 3: Commit**

```bash
git add database/migrations/2026_03_04_000000_add_thr_mode_to_slip_gaji_header_table.php
git commit -m "feat: add thr mode to slip_gaji_header enum"
```

---

## Task 2: Update Upload Form - Tambah Opsi THR

**Files:**
- Modify: `resources/views/sdm/slip-gaji/upload.blade.php:151-155`

**Step 1: Update select options**

Find:
```html
<select id="mode" name="mode" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
    <option value="standard">Slip Gaji Standard</option>
    <option value="gaji_13">Slip Gaji 13</option>
</select>
```

Replace with:
```html
<select id="mode" name="mode" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
    <option value="standard">Slip Gaji Standard</option>
    <option value="gaji_13">Slip Gaji 13</option>
    <option value="thr">Slip Gaji THR</option>
</select>
```

**Step 2: Commit**

```bash
git add resources/views/sdm/slip-gaji/upload.blade.php
git commit -m "feat: add thr option to slip gaji upload form"
```

---

## Task 3: Update SlipGajiService - Template Selection THR

**Files:**
- Modify: `app/Services/SlipGajiService.php:258-269`

**Step 1: Update template selection logic**

Find:
```php
        // For gaji_13 mode, use special templates
        if ($mode === 'gaji_13') {
            // Karyawan Magang uses Karyawan Kontrak template for gaji 13
            if ($detail->status === 'KARYAWAN_MAGANG') {
                $template = 'sdm.slip-gaji.pdf-templates.karyawan-kontrak-gaji13';
            } else {
                $template = 'sdm.slip-gaji.pdf-templates.'.$status.'-gaji13';
            }
        } else {
            // Standard mode
            $template = 'sdm.slip-gaji.pdf-templates.'.$status;
        }
```

Replace with:
```php
        // For gaji_13 mode, use special templates
        if ($mode === 'gaji_13') {
            // Karyawan Magang uses Karyawan Kontrak template for gaji 13
            if ($detail->status === 'KARYAWAN_MAGANG') {
                $template = 'sdm.slip-gaji.pdf-templates.karyawan-kontrak-gaji13';
            } else {
                $template = 'sdm.slip-gaji.pdf-templates.'.$status.'-gaji13';
            }
        } elseif ($mode === 'thr') {
            // THR mode templates
            // Karyawan Magang uses Karyawan Kontrak template for THR
            // Dosen Guru Besar uses Dosen Tetap template for THR
            // Dosen PK tidak mendapat THR
            if ($detail->status === 'KARYAWAN_MAGANG') {
                $template = 'sdm.slip-gaji.pdf-templates.karyawan-kontrak-thr';
            } elseif ($detail->status === 'DOSEN_GURU_BESAR') {
                $template = 'sdm.slip-gaji.pdf-templates.dosen-tetap-thr';
            } elseif ($detail->status === 'DOSEN_PK') {
                throw new \Exception('Dosen PK tidak mendapat slip THR');
            } else {
                $template = 'sdm.slip-gaji.pdf-templates.'.$status.'-thr';
            }
        } else {
            // Standard mode
            $template = 'sdm.slip-gaji.pdf-templates.'.$status;
        }
```

**Step 2: Commit**

```bash
git add app/Services/SlipGajiService.php
git commit -m "feat: add thr template selection logic in SlipGajiService"
```

---

## Task 4: Create Template - Karyawan Tetap THR

**Files:**
- Create: `resources/views/sdm/slip-gaji/pdf-templates/karyawan-tetap-thr.blade.php`

**Step 1: Create template file**

Berdasarkan file `komponen_slip_gaji_thr.md`:
- Penerimaan: Gaji Pokok, Tunj. Perbaikan Penghasilan, Tunj. Golongan, Tunj. Masa Kerja, Tunj. Keluarga, Tunj. Kemahalan, Tunj. Kesehatan, Tunj. Rumah, Tunj. PMB, Tunj. Pendidikan, Transport
- Potongan: Pajak

Template mengikuti struktur `dosen-tetap-gaji13.blade.php` dengan layout yang sama.

**Step 2: Commit**

```bash
git add resources/views/sdm/slip-gaji/pdf-templates/karyawan-tetap-thr.blade.php
git commit -m "feat: add karyawan tetap thr pdf template"
```

---

## Task 5: Create Template - Karyawan Kontrak THR

**Files:**
- Create: `resources/views/sdm/slip-gaji/pdf-templates/karyawan-kontrak-thr.blade.php`

**Step 1: Create template file**

Berdasarkan file `komponen_slip_gaji_thr.md`:
- Penerimaan: Honor Tetap, Tunj. Golongan, Tunj. Masa Kerja, Tunj. PMB, Tunj. Transport
- Potongan: Pajak

Template juga digunakan untuk Karyawan Magang.

**Step 2: Commit**

```bash
git add resources/views/sdm/slip-gaji/pdf-templates/karyawan-kontrak-thr.blade.php
git commit -m "feat: add karyawan kontrak thr pdf template"
```

---

## Task 6: Create Template - Dosen Tetap THR

**Files:**
- Create: `resources/views/sdm/slip-gaji/pdf-templates/dosen-tetap-thr.blade.php`

**Step 1: Create template file**

Berdasarkan file `komponen_slip_gaji_thr.md`:
- Penerimaan: Gaji Pokok, TPP, Tunj. Golongan, Tunj. Masa Kerja, Tunj. Fungsional, Tunj. Rumah, Tunj. Pendidikan, Tunj. PMB
- Potongan: Pajak

Template juga digunakan untuk Dosen Guru Besar.

**Step 2: Commit**

```bash
git add resources/views/sdm/slip-gaji/pdf-templates/dosen-tetap-thr.blade.php
git commit -m "feat: add dosen tetap thr pdf template"
```

---

## Task 7: Create Template - Dosen DPK THR

**Files:**
- Create: `resources/views/sdm/slip-gaji/pdf-templates/dosen-dpk-thr.blade.php`

**Step 1: Create template file**

Berdasarkan file `komponen_slip_gaji_thr.md`:
- Penerimaan: Tunj. Dosen Bantuan Kopertis
- Potongan: Pajak

**Step 2: Commit**

```bash
git add resources/views/sdm/slip-gaji/pdf-templates/dosen-dpk-thr.blade.php
git commit -m "feat: add dosen dpk thr pdf template"
```

---

## Task 8: Final Testing & Verification

**Step 1: Run migration (if not run yet)**

Run: `php artisan migrate`
Expected: Nothing to migrate or migration success

**Step 2: Clear cache**

Run: `php artisan cache:clear && php artisan view:clear`
Expected: Cache cleared

**Step 3: Final commit**

```bash
git add -A
git commit -m "feat: complete slip gaji thr implementation"
```

---

## Summary

| Status Karyawan | Template THR | Notes |
|-----------------|--------------|-------|
| KARYAWAN_TETAP | karyawan-tetap-thr | - |
| KARYAWAN_KONTRAK | karyawan-kontrak-thr | - |
| KARYAWAN_MAGANG | karyawan-kontrak-thr | Uses kontrak template |
| DOSEN_TETAP | dosen-tetap-thr | - |
| DOSEN_GURU_BESAR | dosen-tetap-thr | Uses dosen tetap template |
| DOSEN_DPK | dosen-dpk-thr | - |
| DOSEN_PK | N/A | Tidak mendapat THR (error) |