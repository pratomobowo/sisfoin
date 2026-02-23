## Context

Modul shift SDM sudah memiliki guard unit-scope dan overlap prevention dasar, namun masih ada beberapa risiko best-practice:

1. Mutasi quick edit di calendar belum atomik (delete + create tanpa transaction).
2. Validasi `quickEditShiftId` belum ketat untuk id invalid/non-active.
3. `setAsDefault` pada shift master belum cukup defensive terhadap id invalid/non-active.
4. Query mapping pegawai-user masih berpotensi N+1 pada unit besar.

User meminta paket hardening inti sekaligus optimasi query.

## Goals

1. Menjaga integritas data assignment shift pada quick edit di bawah concurrency normal.
2. Menutup celah validasi input shift id pada quick edit.
3. Menjaga invariants default shift (tidak bisa hilang karena operasi invalid).
4. Mengurangi query berulang pada pemetaan pegawai-user tanpa mengubah perilaku bisnis.
5. Menambah regression tests untuk behavior di atas.

## Non-Goals

1. Refactor arsitektur besar ke service/policy/query object.
2. Perubahan UX besar pada halaman shift.
3. Penambahan fitur shift baru.

## Chosen Approach

Dipilih pendekatan incremental pada komponen existing (low risk):

1. Hardening mutasi di `UnitShiftCalendar` dan `WorkShiftManager`.
2. Optimasi query di `UnitShiftDetail` dan `UnitShiftCalendar`.
3. TDD untuk menambah failing tests lalu implement minimal sampai pass.

Alasan:

- Dampak file terbatas, cocok untuk patch cepat dan aman.
- Risiko regresi lebih rendah dibanding refactor menyeluruh.
- Bisa langsung memberi peningkatan keamanan + performa.

## Design Details

### 1) Quick Edit Data Integrity

Lokasi: `app/Livewire/Sdm/UnitShiftCalendar.php`

- Bungkus operasi overlap deletion + create assignment dalam `DB::transaction(...)`.
- Validasi `quickEditShiftId`:
  - `''` tetap berarti reset ke default.
  - Jika tidak kosong, harus numeric, exists di `work_shifts`, dan `is_active = true`.
- Pertahankan guard `isUserInCurrentUnit` sebelum mutasi.

### 2) Work Shift Default Guard

Lokasi: `app/Livewire/Sdm/WorkShiftManager.php`

- `setAsDefault($id)`:
  - Fail-fast jika shift tidak ditemukan.
  - Tolak jika shift tidak aktif.
  - Hanya update default ketika target valid.
- Pastikan pesan flash error/success eksplisit.

### 3) Query Optimization (Reduce N+1)

Lokasi:

- `app/Livewire/Sdm/UnitShiftCalendar.php`
- `app/Livewire/Sdm/UnitShiftDetail.php`

Strategi:

- Ambil employee aktif pada unit sekali.
- Bentuk kandidat NIP (raw + trim trailing underscore) di memory.
- Ambil user terkait dengan satu query `whereIn('nip', ...)`.
- Bangun map `nip -> user` dan `trimmed_nip -> user` untuk lookup O(1).
- Bentuk output collection dengan mapping tunggal, tanpa query per employee.

### 4) Tests (TDD)

Tambah test feature Livewire untuk memastikan:

1. `saveQuickEdit` menolak shift id invalid/non-active.
2. `setAsDefault` menolak id invalid.
3. `setAsDefault` menolak shift non-active.
4. Invariant default shift tetap aman saat input invalid.
5. Existing shift security + overlap tests tetap hijau.

## Risks and Mitigations

1. Risk: perubahan mapping NIP memengaruhi edge case data legacy.
   - Mitigasi: pertahankan logika fallback `nip` + `rtrim('_')` seperti sebelumnya.
2. Risk: transaction menyebabkan lock contention kecil.
   - Mitigasi: scope transaction dibatasi hanya query overlap + create.
3. Risk: test setup makin lambat.
   - Mitigasi: fokus pada test filter Shift dan test baru saja.

## Verification Plan

Minimal command set:

1. `php artisan test tests/Feature/Livewire/Sdm/UnitShiftSecurityAndOverlapTest.php`
2. `php artisan test tests/Feature/Livewire/Sdm --filter=Shift`
3. `php artisan test --filter=WorkShiftManager`

Jika command (3) belum ada test target, buat test baru lalu jalankan ulang.

## Rollout Notes

Patch bersifat backward-compatible untuk UI existing. Bila ditemukan isu runtime, rollback cukup ke commit sebelum hardening karena tidak ada migrasi schema.
