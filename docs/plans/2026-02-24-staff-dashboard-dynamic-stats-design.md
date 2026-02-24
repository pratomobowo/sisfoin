# Staff Dashboard Dynamic Stats Design

## Goal

Menghapus nilai hardcoded pada ringkasan cepat dashboard staff dan menggantinya dengan metrik real-time berbasis data aktual user login.

## Scope

- Route `staff.dashboard` diarahkan ke controller khusus staff dashboard.
- Metrik dashboard staff:
  - Hari hadir bulan ini (attendance real)
  - Gaji bulan ini/terbaru tersedia (payroll real)
  - Pengumuman belum dibaca (announcement real)
- Blade staff dashboard menggunakan data dari controller, bukan angka literal.
- Regression test dasar untuk route mapping + render stats fallback.

## Non-Goals

- Tidak melakukan redesign UI besar pada dashboard staff.
- Tidak mengubah business flow modul attendance/penggajian/pengumuman lain.
- Tidak menambah endpoint API baru.

## Current Findings

1. `staff.dashboard` saat ini masih redirect ke route dashboard umum, sehingga tidak punya data khusus staff.
2. `resources/views/staff/dashboard.blade.php` menampilkan angka hardcoded:
   - 22 hari hadir
   - Rp 4.500.000
   - 3 pengumuman baru
3. Model data sudah tersedia dan cukup untuk ringkasan real:
   - `App\Models\Employee\Attendance`
   - `App\Models\SlipGajiDetail`
   - `App\Models\Employee\Announcement`

## Design Decisions

### 1) Dedicated Staff Dashboard Route

- Ubah route `staff.dashboard` dari closure redirect menjadi controller action:
  - `App\Http\Controllers\Employee\DashboardController@index`
- Ini menjaga responsibility data aggregation tetap di server-side controller.

### 2) Dashboard Stats Aggregation in Controller

- `DashboardController@index` menghitung `quickStats`:
  - `attendanceDaysThisMonth`: count attendance bulan berjalan dengan status `present`, `on_time`, `early_arrival`, `late` untuk user login.
  - `latestNetSalary`: nominal `penerimaan_bersih` dari slip staff (`status = Tersedia`) terbaru berdasarkan periode/header.
  - `unreadAnnouncements`: count pengumuman `active()` yang belum dibaca user (`unreadBy($user)`).

### 3) Safe Fallbacks

- Jika user tidak punya NIP atau belum ada slip tersedia, `latestNetSalary = null`.
- Jika announcement belum ada data, count tetap `0`.
- View render fallback user-friendly (`Belum tersedia` / `0`) tanpa error.

### 4) View Binding Strategy

- `resources/views/staff/dashboard.blade.php` tetap mempertahankan struktur kartu lama.
- Hanya mengganti literal hardcoded dengan binding variabel `quickStats`.

## Testing Strategy

- Tambah test baru `tests/Feature/Staff/StaffDashboardStatsTest.php` untuk:
  1. memastikan route `staff.dashboard` terhubung ke `DashboardController@index`.
  2. memastikan halaman dashboard staff render dengan fallback values aman saat data belum ada.
  3. memastikan angka hardcoded lama tidak lagi dipakai secara literal di output.

## Expected Outcomes

- Dashboard staff menampilkan ringkasan berbasis data real.
- Risiko misleading informasi karena angka hardcoded hilang.
- Route staff dashboard konsisten dengan ownership controller staff module.
