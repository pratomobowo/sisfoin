# Staff Attendance and Announcement Hardening Design

## Goal

Menstabilkan modul staff attendance dan announcement agar tidak lagi bergantung pada dummy data, konsisten route-controller-view, dan aman terhadap edge case runtime.

## Scope

- Migrasi staff announcement dari dummy data controller ke data real model `App\Models\Employee\Announcement`.
- Implementasi `markAsRead` real ke pivot `announcement_reads`.
- Hardening attendance synthesis/status mapping untuk kondisi libur/weekend/hari lampau.
- Regression tests targeted untuk route/action/data-integrity area staff attendance + announcement.

## Non-Goals

- Tidak melakukan redesign UI besar.
- Tidak migrasi modul ke Livewire baru.
- Tidak mengubah struktur database.

## Findings

1. `AnnouncementController` masih memakai generator dummy (`generateDummyAnnouncementData`) untuk index/show.
2. `markAsRead` saat ini hanya return JSON sukses, belum menulis status baca aktual.
3. `AttendanceController` sudah pakai data real, namun parsing `working_days` dan mapping status sintetis perlu dipertegas agar lebih tahan edge case.

## Design Decisions

### 1) Announcement Data Source = Real Model

- `index()` akan query `Announcement::query()->active()` dengan urutan pinned dulu lalu terbaru.
- `show($id)` ambil data real announcement aktif/published yang sesuai akses staff.
- View tetap dipertahankan, controller menyiapkan shape data yang kompatibel.

### 2) markAsRead Writes to Persistence

- `markAsRead($id)` memanggil model `Announcement` + `markAsReadBy($user)`.
- Response JSON tetap kompatibel (`success`, `message`) tapi kini state benar-benar tersimpan.
- Jika announcement tidak ditemukan, return 404 JSON yang aman.

### 3) Attendance Hardening

- Normalisasi parsing `working_days` ke integer set (hindari mismatch tipe string/int).
- Pertahankan synthesis status untuk no-record day namun dengan guard konsisten:
  - libur/weekend -> off
  - hari lampau kerja tanpa record -> absent
- Summary counter dijaga agar tidak double-count tidak terduga.

### 4) Testing Strategy

- Tambah feature tests staff announcement:
  - index memakai data real (bukan dummy literal)
  - show menampilkan announcement sesuai ID real
  - markAsRead menulis pivot read status
- Tambah test attendance helper behavior yang paling riskan (working_days parsing + synthesized absent/off).

## Expected Outcomes

- Pengumuman staff konsisten dengan data database aktual.
- Aksi "tandai dibaca" benar-benar tercatat.
- Attendance module lebih stabil untuk edge case kalender/status.
- Ada regression guard agar masalah serupa tidak berulang.
