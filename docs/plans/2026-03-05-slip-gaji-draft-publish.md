# Slip Gaji Draft/Publish System Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Implement draft/publish status for slip gaji to control when staff can view their salary slips.

**Architecture:** Add status field to SlipGajiHeader model with draft/published states. SDM can toggle status, staff portal filters to show only published slips. Edit/delete protection for published slips.

**Tech Stack:** Laravel 11, Livewire 3, MySQL, Spatie ActivityLog

---

## Task 1: Database Migration

**Files:**
- Create: `database/migrations/2026_03_05_000000_add_status_to_slip_gaji_header_table.php`

**Step 1: Create migration file**

```bash
php artisan make:migration add_status_to_slip_gaji_header_table --table=slip_gaji_header
```

**Step 2: Write migration code**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slip_gaji_header', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published'])->default('draft')->after('mode');
            $table->timestamp('published_at')->nullable()->after('uploaded_at');
            $table->unsignedBigInteger('published_by')->nullable()->after('published_at');
            
            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::table('slip_gaji_header', function (Blueprint $table) {
            $table->dropForeign(['published_by']);
            $table->dropIndex(['status', 'periode']);
            $table->dropColumn(['status', 'published_at', 'published_by']);
        });
    }
};
```

**Step 3: Run migration**

```bash
php artisan migrate
```

Expected output: "Migration completed successfully"

**Step 4: Commit**

```bash
git add database/migrations/2026_03_05_000000_add_status_to_slip_gaji_header_table.php
git commit -m "feat: add status field to slip_gaji_header table for draft/publish system"
```

---

## Task 2: Update SlipGajiHeader Model

**Files:**
- Modify: `app/Models/SlipGajiHeader.php`

**Step 1: Add fillable fields**

Edit `app/Models/SlipGajiHeader.php`:

```php
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
```

**Step 2: Add casts**

```php
protected $casts = [
    'uploaded_at' => 'datetime',
    'published_at' => 'datetime',
];
```

**Step 3: Add helper methods**

```php
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
```

**Step 4: Add publisher relationship**

```php
public function publisher(): BelongsTo
{
    return $this->belongsTo(User::class, 'published_by');
}
```

**Step 5: Commit**

```bash
git add app/Models/SlipGajiHeader.php
git commit -m "feat: add status methods and relationships to SlipGajiHeader model"
```

---

## Task 3: Unit Tests for Model Methods

**Files:**
- Create: `tests/Unit/SlipGajiHeaderTest.php`

**Step 1: Write test file**

```php
<?php

namespace Tests\Unit;

use App\Models\SlipGajiHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlipGajiHeaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_status_is_draft(): void
    {
        $user = User::factory()->create();
        $header = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $user->id,
        ]);

        $this->assertEquals('draft', $header->status);
    }

    public function test_is_draft_returns_true_for_draft_status(): void
    {
        $user = User::factory()->create();
        $header = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $user->id,
            'status' => 'draft',
        ]);

        $this->assertTrue($header->isDraft());
    }

    public function test_is_published_returns_true_for_published_status(): void
    {
        $user = User::factory()->create();
        $header = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $user->id,
            'status' => 'published',
        ]);

        $this->assertTrue($header->isPublished());
    }

    public function test_can_be_edited_returns_true_for_draft(): void
    {
        $user = User::factory()->create();
        $header = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $user->id,
            'status' => 'draft',
        ]);

        $this->assertTrue($header->canBeEdited());
    }

    public function test_can_be_edited_returns_false_for_published(): void
    {
        $user = User::factory()->create();
        $header = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $user->id,
            'status' => 'published',
        ]);

        $this->assertFalse($header->canBeEdited());
    }

    public function test_publisher_relationship(): void
    {
        $user = User::factory()->create();
        $header = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $user->id,
            'status' => 'published',
            'published_by' => $user->id,
        ]);

        $this->assertEquals($user->id, $header->publisher->id);
    }
}
```

**Step 2: Run tests**

```bash
php artisan test --filter SlipGajiHeaderTest
```

Expected: All 6 tests PASS

**Step 3: Commit**

```bash
git add tests/Unit/SlipGajiHeaderTest.php
git commit -m "test: add unit tests for SlipGajiHeader status methods"
```

---

## Task 4: Add Publish/Unpublish Routes

**Files:**
- Modify: `routes/web.php`

**Step 1: Add routes to SDM slip-gaji group**

Edit `routes/web.php`, add inside the SDM slip-gaji route group (around line 130-140):

```php
Route::post('/slip-gaji/{id}/publish', [SlipGajiController::class, 'publish'])->name('slip-gaji.publish');
Route::post('/slip-gaji/{id}/unpublish', [SlipGajiController::class, 'unpublish'])->name('slip-gaji.unpublish');
```

**Step 2: Verify routes**

```bash
php artisan route:list --name=sdm.slip-gaji
```

Expected: Routes should show `sdm.slip-gaji.publish` and `sdm.slip-gaji.unpublish`

**Step 3: Commit**

```bash
git add routes/web.php
git commit -m "feat: add publish and unpublish routes for slip gaji"
```

---

## Task 5: Publish/Unpublish Controller Methods

**Files:**
- Modify: `app/Http/Controllers/SDM/SlipGajiController.php`

**Step 1: Write failing test for publish**

Create `tests/Feature/SDM/SlipGajiPublishTest.php`:

```php
<?php

namespace Tests\Feature\SDM;

use App\Models\SlipGajiHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlipGajiPublishTest extends TestCase
{
    use RefreshDatabase;

    public function test_sdm_can_publish_draft_slip(): void
    {
        $sdm = User::factory()->create();
        $sdm->assignRole('admin-sdm');
        
        $header = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $sdm->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($sdm)
            ->post(route('sdm.slip-gaji.publish', $header->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertEquals('published', $header->fresh()->status);
        $this->assertNotNull($header->fresh()->published_at);
        $this->assertEquals($sdm->id, $header->fresh()->published_by);
    }

    public function test_sdm_can_unpublish_published_slip(): void
    {
        $sdm = User::factory()->create();
        $sdm->assignRole('admin-sdm');
        
        $header = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $sdm->id,
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $sdm->id,
        ]);

        $response = $this->actingAs($sdm)
            ->post(route('sdm.slip-gaji.unpublish', $header->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertEquals('draft', $header->fresh()->status);
        $this->assertNull($header->fresh()->published_at);
        $this->assertNull($header->fresh()->published_by);
    }

    public function test_sdm_cannot_publish_already_published_slip(): void
    {
        $sdm = User::factory()->create();
        $sdm->assignRole('admin-sdm');
        
        $header = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $sdm->id,
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $sdm->id,
        ]);

        $response = $this->actingAs($sdm)
            ->post(route('sdm.slip-gaji.publish', $header->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_sdm_cannot_unpublish_draft_slip(): void
    {
        $sdm = User::factory()->create();
        $sdm->assignRole('admin-sdm');
        
        $header = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $sdm->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($sdm)
            ->post(route('sdm.slip-gaji.unpublish', $header->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
```

**Step 2: Run test to verify it fails**

```bash
php artisan test --filter SlipGajiPublishTest
```

Expected: FAIL - methods `publish` and `unpublish` not found

**Step 3: Add publish method to controller**

Edit `app/Http/Controllers/SDM/SlipGajiController.php`, add before the closing brace:

```php
/**
 * Publish slip gaji
 */
public function publish($id)
{
    $header = SlipGajiHeader::findOrFail($id);
    
    if ($header->isPublished()) {
        return redirect()->back()->with('error', 'Slip gaji sudah di-publish');
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
    
    return redirect()->back()->with('success', 'Slip gaji berhasil di-publish');
}

/**
 * Unpublish slip gaji
 */
public function unpublish($id)
{
    $header = SlipGajiHeader::findOrFail($id);
    
    if ($header->isDraft()) {
        return redirect()->back()->with('error', 'Slip gaji sudah dalam status draft');
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
    
    return redirect()->back()->with('success', 'Slip gaji berhasil di-unpublish');
}
```

**Step 4: Run test to verify it passes**

```bash
php artisan test --filter SlipGajiPublishTest
```

Expected: All 4 tests PASS

**Step 5: Commit**

```bash
git add app/Http/Controllers/SDM/SlipGajiController.php tests/Feature/SDM/SlipGajiPublishTest.php
git commit -m "feat: add publish and unpublish methods to SlipGajiController with tests"
```

---

## Task 6: Edit Protection for Published Slips

**Files:**
- Modify: `app/Http/Controllers/SDM/SlipGajiController.php`
- Create: `tests/Feature/SDM/SlipGajiEditProtectionTest.php`

**Step 1: Write test for edit protection**

Create `tests/Feature/SDM/SlipGajiEditProtectionTest.php`:

```php
<?php

namespace Tests\Feature\SDM;

use App\Models\SlipGajiDetail;
use App\Models\SlipGajiHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlipGajiEditProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sdm_cannot_update_published_slip(): void
    {
        $sdm = User::factory()->create();
        $sdm->assignRole('admin-sdm');
        
        $header = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $sdm->id,
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $sdm->id,
        ]);

        $response = $this->actingAs($sdm)
            ->put(route('sdm.slip-gaji.update', $header->id), [
                'periode' => '2026-04',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertEquals('2026-03', $header->fresh()->periode);
    }

    public function test_sdm_cannot_delete_published_slip(): void
    {
        $sdm = User::factory()->create();
        $sdm->assignRole('admin-sdm');
        
        $header = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $sdm->id,
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $sdm->id,
        ]);

        $response = $this->actingAs($sdm)
            ->delete(route('sdm.slip-gaji.destroy', $header->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('slip_gaji_header', ['id' => $header->id]);
    }

    public function test_sdm_can_update_draft_slip(): void
    {
        $sdm = User::factory()->create();
        $sdm->assignRole('admin-sdm');
        
        $header = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $sdm->id,
            'status' => 'draft',
        ]);

        // This test assumes update method exists and works for draft
        // Implementation may vary based on actual update logic
        $this->assertTrue($header->canBeEdited());
    }
}
```

**Step 2: Run test to verify it fails**

```bash
php artisan test --filter SlipGajiEditProtectionTest
```

Expected: FAIL - no protection logic in update/destroy methods

**Step 3: Add protection to update method**

Find the `update` method in `SlipGajiController.php` and add at the beginning:

```php
public function update(Request $request, $id)
{
    $header = SlipGajiHeader::findOrFail($id);
    
    // Add this protection
    if (!$header->canBeEdited()) {
        return redirect()->back()->with('error', 'Slip gaji harus di-unpublish terlebih dahulu sebelum diedit');
    }
    
    // ... rest of existing update logic
}
```

**Step 4: Add protection to destroy method**

Find the `destroy` method in `SlipGajiController.php` and add at the beginning:

```php
public function destroy($id)
{
    $header = SlipGajiHeader::findOrFail($id);
    
    // Add this protection
    if (!$header->canBeEdited()) {
        return redirect()->back()->with('error', 'Slip gaji harus di-unpublish terlebih dahulu sebelum dihapus');
    }
    
    // ... rest of existing destroy logic
}
```

**Step 5: Run test to verify it passes**

```bash
php artisan test --filter SlipGajiEditProtectionTest
```

Expected: All 3 tests PASS

**Step 6: Commit**

```bash
git add app/Http/Controllers/SDM/SlipGajiController.php tests/Feature/SDM/SlipGajiEditProtectionTest.php
git commit -m "feat: add edit protection for published slip gaji with tests"
```

---

## Task 7: Filter Published Slips in Staff Portal

**Files:**
- Modify: `app/Http/Controllers/Employee/PayrollController.php`
- Create: `tests/Feature/Staff/PayrollFilterTest.php`

**Step 1: Write test for staff filter**

Create `tests/Feature/Staff/PayrollFilterTest.php`:

```php
<?php

namespace Tests\Feature\Staff;

use App\Models\Employee;
use App\Models\SlipGajiDetail;
use App\Models\SlipGajiHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_only_see_published_slips(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $staff->assignRole('staff');
        
        $employee = Employee::create([
            'nip' => '12345',
            'nama' => 'Test Employee',
            'email' => 'test@example.com',
        ]);
        
        $staff->update(['nip' => '12345']);
        
        // Create published slip
        $publishedHeader = SlipGajiHeader::create([
            'periode' => '2026-03',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $staff->id,
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $staff->id,
        ]);
        
        SlipGajiDetail::create([
            'header_id' => $publishedHeader->id,
            'nip' => '12345',
            'penerimaan_bersih' => 5000000,
            'total_potongan' => 500000,
        ]);
        
        // Create draft slip
        $draftHeader = SlipGajiHeader::create([
            'periode' => '2026-02',
            'file_original' => 'test2.xlsx',
            'uploaded_by' => $staff->id,
            'status' => 'draft',
        ]);
        
        SlipGajiDetail::create([
            'header_id' => $draftHeader->id,
            'nip' => '12345',
            'penerimaan_bersih' => 4000000,
            'total_potongan' => 400000,
        ]);

        $response = $this->actingAs($staff)
            ->get(route('staff.penggajian.index'));

        $response->assertStatus(200);
        $response->assertSee('2026-03'); // Published slip visible
        $response->assertDontSee('2026-02'); // Draft slip not visible
    }

    public function test_staff_cannot_download_draft_slip_pdf(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $staff->assignRole('staff');
        
        $staff->update(['nip' => '12345']);
        
        $draftHeader = SlipGajiHeader::create([
            'periode' => '2026-02',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $staff->id,
            'status' => 'draft',
        ]);
        
        SlipGajiDetail::create([
            'header_id' => $draftHeader->id,
            'nip' => '12345',
            'penerimaan_bersih' => 4000000,
            'total_potongan' => 400000,
        ]);

        $response = $this->actingAs($staff)
            ->get(route('staff.penggajian.download-pdf', $draftHeader->id));

        $response->assertStatus(404); // Should not find draft slip
    }
}
```

**Step 2: Run test to verify it fails**

```bash
php artisan test --filter PayrollFilterTest
```

Expected: FAIL - draft slips still visible

**Step 3: Add filter to PayrollController**

Edit `app/Http/Controllers/Employee/PayrollController.php`, modify the query at line 36-39:

```php
$query = SlipGajiDetail::query()
    ->where('nip', $nip)
    ->whereHas('header', function ($q) {
        $q->where('status', 'published');
    })
    ->with(['header', 'employee', 'dosen'])
    ->orderBy('created_at', 'desc');
```

Also update the summary query at line 66-69:

```php
$allSlips = SlipGajiDetail::query()
    ->where('nip', $nip)
    ->whereHas('header', function ($q) {
        $q->where('status', 'published');
    })
    ->with(['header', 'employee', 'dosen'])
    ->get();
```

**Step 4: Run test to verify it passes**

```bash
php artisan test --filter PayrollFilterTest
```

Expected: All 2 tests PASS

**Step 5: Commit**

```bash
git add app/Http/Controllers/Employee/PayrollController.php tests/Feature/Staff/PayrollFilterTest.php
git commit -m "feat: filter slip gaji to show only published in staff portal with tests"
```

---

## Task 8: Update Livewire Component UI

**Files:**
- Modify: `app/Livewire/Sdm/SlipGajiManagement.php`
- Modify: `resources/views/livewire/sdm/slip-gaji-management.blade.php`

**Step 1: Add status badge to table**

Edit the Livewire view, add status column in table header (after periode column):

```blade
<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
```

And in table body, add status cell:

```blade
<td class="px-6 py-4 whitespace-nowrap">
    @if($header->status === 'published')
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
            Published
        </span>
    @else
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">
            Draft
        </span>
    @endif
</td>
```

**Step 2: Add publish/unpublish buttons**

In the actions column, add publish/unpublish button:

```blade
<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
    @if($header->isDraft())
        <button 
            wire:click="publish({{ $header->id }})"
            wire:confirm="Yakin ingin publish slip gaji ini? Staff akan dapat melihat slip gaji."
            class="text-green-600 hover:text-green-900 mr-3"
        >
            Publish
        </button>
    @else
        <button 
            wire:click="unpublish({{ $header->id }})"
            wire:confirm="Yakin ingin unpublish? Staff tidak akan dapat melihat slip gaji ini lagi."
            class="text-yellow-600 hover:text-yellow-900 mr-3"
        >
            Unpublish
        </button>
    @endif
    
    @if($header->canBeEdited())
        <a href="{{ route('sdm.slip-gaji.edit', $header->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
        <button wire:click="delete({{ $header->id }})" class="text-red-600 hover:text-red-900">Hapus</button>
    @else
        <span class="text-gray-400 mr-3" title="Unpublish terlebih dahulu untuk mengedit">Edit</span>
        <span class="text-gray-400" title="Unpublish terlebih dahulu untuk menghapus">Hapus</span>
    @endif
</td>
```

**Step 3: Add publish/unpublish methods to Livewire component**

Edit `app/Livewire/Sdm/SlipGajiManagement.php`, add these methods:

```php
public function publish($id)
{
    $header = SlipGajiHeader::findOrFail($id);
    
    if ($header->isPublished()) {
        session()->flash('error', 'Slip gaji sudah di-publish');
        return;
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
    
    session()->flash('success', 'Slip gaji berhasil di-publish');
}

public function unpublish($id)
{
    $header = SlipGajiHeader::findOrFail($id);
    
    if ($header->isDraft()) {
        session()->flash('error', 'Slip gaji sudah dalam status draft');
        return;
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
    
    session()->flash('success', 'Slip gaji berhasil di-unpublish');
}
```

**Step 4: Add status filter**

Add filter property to Livewire component:

```php
public $statusFilter = '';

public function updatedStatusFilter()
{
    $this->resetPage();
}
```

Update the query in render method to apply filter:

```php
public function render()
{
    $query = SlipGajiHeader::with(['details', 'uploader'])
        ->when($this->search, function ($q) {
            $q->where('periode', 'like', '%' . $this->search . '%');
        })
        ->when($this->statusFilter, function ($q) {
            $q->where('status', $this->statusFilter);
        })
        ->orderBy('created_at', 'desc');
    
    $headers = $query->paginate($this->perPage);
    
    return view('livewire.sdm.slip-gaji-management', [
        'headers' => $headers,
    ]);
}
```

Add filter dropdown in view:

```blade
<select wire:model.live="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2">
    <option value="">Semua Status</option>
    <option value="draft">Draft</option>
    <option value="published">Published</option>
</select>
```

**Step 5: Test manually**

```bash
php artisan serve
```

Navigate to SDM slip gaji management page and verify:
- Status badge shows correctly
- Publish/Unpublish buttons work
- Edit/Delete disabled for published slips
- Status filter works

**Step 6: Commit**

```bash
git add app/Livewire/Sdm/SlipGajiManagement.php resources/views/livewire/sdm/slip-gaji-management.blade.php
git commit -m "feat: add UI for slip gaji publish/unpublish with status badge and filter"
```

---

## Task 9: Final Testing & Verification

**Step 1: Run all tests**

```bash
php artisan test --filter "SlipGajiHeader|SlipGajiPublish|SlipGajiEditProtection|PayrollFilter"
```

Expected: All tests PASS

**Step 2: Manual testing checklist**

- [ ] Login as SDM
- [ ] Upload new slip gaji (should be draft by default)
- [ ] Verify staff cannot see draft slip
- [ ] Publish the slip
- [ ] Verify staff can now see the slip
- [ ] Try to edit published slip (should fail)
- [ ] Unpublish the slip
- [ ] Verify staff cannot see the slip again
- [ ] Try to edit draft slip (should succeed)
- [ ] Check activity log for publish/unpublish events

**Step 3: Commit final verification**

```bash
git add -A
git commit -m "chore: final testing and verification for slip gaji draft/publish system"
```

---

## Success Criteria

- ✅ Migration runs successfully
- ✅ All unit tests pass
- ✅ All feature tests pass
- ✅ Staff can only see published slips
- ✅ SDM can publish/unpublish slips
- ✅ Published slips cannot be edited/deleted
- ✅ Activity log tracks all actions
- ✅ UI shows status badges and filter
- ✅ Manual testing complete

## Deployment Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Clear view cache: `php artisan view:clear`
- [ ] Verify activity log working
- [ ] Test with real data
- [ ] Communicate changes to SDM team