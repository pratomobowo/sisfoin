# Design: Slip Gaji Draft/Publish System

**Date:** March 5, 2026  
**Status:** Approved  

## Problem Statement

SDM dapat mengupload slip gaji sebelum tanggal gajian, tetapi slip gaji yang diupload langsung terlihat oleh staff di halaman penggajian. Diperlukan mekanisme untuk mengontrol kapan slip gaji dapat dilihat oleh staff.

## Solution Overview

Implementasi status draft/publish pada slip gaji dengan workflow:
- SDM upload slip gaji → otomatis berstatus draft
- SDM publish manual saat waktunya → staff dapat melihat slip gaji
- SDM dapat unpublish kapan saja → staff tidak dapat melihat slip gaji lagi
- Slip yang sudah published tidak dapat diedit/dihapus sampai di-unpublish

## Business Requirements

1. **Workflow:** Upload → Draft → Publish Manual oleh SDM
2. **Unpublish:** SDM dapat toggle antara draft dan published kapan saja
3. **Edit Protection:** Slip yang sudah publish harus di-unpublish dulu sebelum bisa diedit
4. **Audit Trail:** Tracking siapa yang publish/unpublish dan kapan

## Technical Design

### 1. Database Schema

**Migration:** `add_status_to_slip_gaji_header_table`

```php
Schema::table('slip_gaji_header', function (Blueprint $table) {
    $table->enum('status', ['draft', 'published'])->default('draft')->after('mode');
    $table->timestamp('published_at')->nullable()->after('uploaded_at');
    $table->unsignedBigInteger('published_by')->nullable()->after('published_at');
    
    $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');
    $table->index(['status', 'periode']);
});
```

**New Columns:**
- `status` - enum('draft', 'published'), default 'draft'
- `published_at` - timestamp kapan di-publish
- `published_by` - user ID yang melakukan publish

### 2. Model Changes

**SlipGajiHeader Model:**

```php
// Fillable
protected $fillable = [
    'periode',
    'mode',
    'file_original',
    'uploaded_by',
    'uploaded_at',
    'status',
    'published_at',
    'published_by',
];

// Casts
protected $casts = [
    'uploaded_at' => 'datetime',
    'published_at' => 'datetime',
];

// Helper Methods
public function isDraft(): bool
{
    return $this->status === 'draft';
}

public function isPublished(): bool
{
    return $this->status === 'published';
}

public function canBeEdited(): bool
{
    return $this->isDraft();
}

// Relationships
public function publisher(): BelongsTo
{
    return $this->belongsTo(User::class, 'published_by');
}
```

### 3. Controller Logic

**SlipGajiController - New Methods:**

**Publish:**
```php
public function publish($id)
{
    $header = SlipGajiHeader::findOrFail($id);
    
    if ($header->isPublished()) {
        return back()->with('error', 'Slip gaji sudah di-publish');
    }
    
    $header->update([
        'status' => 'published',
        'published_at' => now(),
        'published_by' => auth()->id(),
    ]);
    
    activity()
        ->causedBy(auth()->user())
        ->performedOn($header)
        ->log('Slip gaji dipublish');
    
    return back()->with('success', 'Slip gaji berhasil di-publish');
}
```

**Unpublish:**
```php
public function unpublish($id)
{
    $header = SlipGajiHeader::findOrFail($id);
    
    if ($header->isDraft()) {
        return back()->with('error', 'Slip gaji sudah dalam status draft');
    }
    
    $header->update([
        'status' => 'draft',
        'published_at' => null,
        'published_by' => null,
    ]);
    
    activity()
        ->causedBy(auth()->user())
        ->performedOn($header)
        ->log('Slip gaji di-unpublish');
    
    return back()->with('success', 'Slip gaji berhasil di-unpublish');
}
```

**Edit Protection:**
```php
// In update() and destroy() methods
if (!$header->canBeEdited()) {
    return back()->with('error', 'Slip gaji harus di-unpublish terlebih dahulu sebelum diedit/dihapus');
}
```

**Staff PayrollController Filter:**
```php
$query = SlipGajiDetail::query()
    ->where('nip', $nip)
    ->whereHas('header', function ($q) {
        $q->where('status', 'published');
    })
    ->with(['header', 'employee', 'dosen'])
    ->orderBy('created_at', 'desc');
```

### 4. UI/UX Changes

**SDM Slip Gaji Management:**

**Status Badge:**
```blade
<span class="px-2 py-1 text-xs font-medium rounded-full 
    {{ $header->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
    {{ $header->status === 'published' ? 'Published' : 'Draft' }}
</span>
```

**Action Buttons:**
- Draft: Tombol "Publish" (warna hijau)
- Published: Tombol "Unpublish" (warna kuning)

**Edit/Delete Protection:**
- Published: Disable tombol Edit & Delete
- Tooltip: "Unpublish terlebih dahulu untuk mengedit"

**Confirmation Modal:**
- Publish: "Yakin ingin publish slip gaji ini? Staff akan dapat melihat slip gaji."
- Unpublish: "Yakin ingin unpublish? Staff tidak akan dapat melihat slip gaji ini lagi."

**Filter/Tab:**
- Filter status: Semua / Draft / Published

### 5. Routes

```php
// routes/web.php - SDM routes
Route::post('/slip-gaji/{id}/publish', [SlipGajiController::class, 'publish'])->name('slip-gaji.publish');
Route::post('/slip-gaji/{id}/unpublish', [SlipGajiController::class, 'unpublish'])->name('slip-gaji.unpublish');
```

### 6. Testing Strategy

**Unit Tests (Model):**
- `test_default_status_is_draft()`
- `test_is_draft_returns_true_for_draft_status()`
- `test_is_published_returns_true_for_published_status()`
- `test_can_be_edited_returns_true_for_draft()`
- `test_can_be_edited_returns_false_for_published()`

**Feature Tests (Controller):**
- `test_sdm_can_publish_draft_slip()`
- `test_sdm_can_unpublish_published_slip()`
- `test_sdm_cannot_publish_already_published_slip()`
- `test_sdm_cannot_unpublish_draft_slip()`
- `test_sdm_cannot_edit_published_slip()`
- `test_sdm_cannot_delete_published_slip()`
- `test_publish_activity_is_logged()`
- `test_unpublish_activity_is_logged()`

**Integration Tests (Staff Portal):**
- `test_staff_can_only_see_published_slips()`
- `test_staff_cannot_see_draft_slips()`
- `test_staff_cannot_download_draft_slip_pdf()`
- `test_staff_can_download_published_slip_pdf()`

## Implementation Roadmap

**Phase 1: Database & Model (30 min)**
- Create migration `add_status_to_slip_gaji_header_table`
- Update `SlipGajiHeader` model
- Add helper methods

**Phase 2: Backend Logic (1 hour)**
- Add `publish()` and `unpublish()` methods to controller
- Update `update()` and `destroy()` with protection
- Filter published slips in staff portal

**Phase 3: UI Implementation (1.5 hours)**
- Update Livewire component
- Add status badge and action buttons
- Add confirmation modals
- Disable edit/delete for published slips
- Add status filter/tab

**Phase 4: Testing (1 hour)**
- Unit tests
- Feature tests
- Integration tests

**Phase 5: Documentation & Deployment (30 min)**
- Update documentation if needed
- Run migration in production
- Verify functionality

## Success Criteria

- ✅ SDM can upload slip gaji as draft
- ✅ SDM can publish draft slip gaji
- ✅ SDM can unpublish published slip gaji
- ✅ Staff can only see published slip gaji
- ✅ Staff cannot see draft slip gaji
- ✅ Published slip gaji cannot be edited until unpublished
- ✅ All publish/unpublish actions are logged
- ✅ Tests pass

## Risk Mitigation

**Risk:** SDM accidentally unpublish slip that staff is viewing  
**Mitigation:** Confirmation modal with clear warning message

**Risk:** Staff tries to access published slip after unpublish  
**Mitigation:** Graceful error handling, redirect with message

**Risk:** Data inconsistency during unpublish  
**Mitigation:** Database transaction for unpublish operation

## Future Enhancements

- Scheduled publish (auto-publish at specific date/time)
- Bulk publish/unpublish for multiple slips
- Email notification to staff when slip is published
- Approval workflow (draft → pending approval → published)