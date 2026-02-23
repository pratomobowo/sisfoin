<?php

namespace Tests\Feature\Livewire\Sdm;

use App\Livewire\Sdm\UnitShiftCalendar;
use App\Livewire\Sdm\UnitShiftDetail;
use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\User;
use App\Models\WorkShift;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\Feature\Sync\Concerns\CreatesActivityLogTable;
use Tests\TestCase;

class UnitShiftSecurityAndOverlapTest extends TestCase
{
    use CreatesActivityLogTable;

    protected function setUp(): void
    {
        parent::setUp();

        $migrationBasePath = is_file(getcwd().'/database/migrations/0001_01_01_000000_create_users_table.php')
            ? getcwd().'/database/migrations'
            : base_path('database/migrations');

        Artisan::call('migrate:fresh', [
            '--path' => $migrationBasePath.'/0001_01_01_000000_create_users_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_13_230642_add_nip_to_users_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_26_170000_create_employees_table_from_sevima.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2026_01_19_185853_create_work_shifts_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2026_01_19_201039_create_employee_shift_assignments_table.php',
            '--realpath' => true,
        ]);

        $this->ensureActivityLogTableExists();
    }

    public function test_unit_shift_detail_blocks_editing_assignment_from_other_unit(): void
    {
        $actor = User::create([
            'name' => 'Actor',
            'email' => 'actor@example.com',
            'password' => 'password',
            'nip' => 'ACTOR-1',
        ]);

        $outsideUser = User::create([
            'name' => 'Outside User',
            'email' => 'outside@example.com',
            'password' => 'password',
            'nip' => 'OUT-1',
        ]);

        Employee::create([
            'id_pegawai' => 'EMP-OUT',
            'nip' => 'OUT-1',
            'nama' => 'Outside Employee',
            'satuan_kerja' => 'UNIT B',
            'status_aktif' => 'Aktif',
        ]);

        $shift = WorkShift::firstOrFail();

        $assignment = EmployeeShiftAssignment::create([
            'user_id' => $outsideUser->id,
            'work_shift_id' => $shift->id,
            'start_date' => '2026-02-01',
            'end_date' => null,
            'created_by' => $actor->id,
        ]);

        $this->actingAs($actor);

        Livewire::test(UnitShiftDetail::class, ['unit' => 'UNIT A'])
            ->call('openModal', null, $assignment->id)
            ->assertSet('showModal', false)
            ->assertSet('editingId', null);
    }

    public function test_unit_shift_detail_blocks_deleting_assignment_from_other_unit(): void
    {
        $actor = User::create([
            'name' => 'Actor Delete',
            'email' => 'actor-delete@example.com',
            'password' => 'password',
            'nip' => 'ACT-DEL-1',
        ]);

        $outsideUser = User::create([
            'name' => 'Outside Delete',
            'email' => 'outside-delete@example.com',
            'password' => 'password',
            'nip' => 'OUT-DEL-1',
        ]);

        Employee::create([
            'id_pegawai' => 'EMP-OUT-DEL',
            'nip' => 'OUT-DEL-1',
            'nama' => 'Outside Employee Delete',
            'satuan_kerja' => 'UNIT B',
            'status_aktif' => 'Aktif',
        ]);

        $shift = WorkShift::firstOrFail();

        $assignment = EmployeeShiftAssignment::create([
            'user_id' => $outsideUser->id,
            'work_shift_id' => $shift->id,
            'start_date' => '2026-02-01',
            'end_date' => null,
            'created_by' => $actor->id,
        ]);

        $this->actingAs($actor);

        Livewire::test(UnitShiftDetail::class, ['unit' => 'UNIT A'])
            ->call('delete', $assignment->id);

        $this->assertDatabaseHas('employee_shift_assignments', [
            'id' => $assignment->id,
        ]);
    }

    public function test_unit_shift_calendar_blocks_quick_edit_for_user_outside_unit(): void
    {
        $actor = User::create([
            'name' => 'Actor Calendar',
            'email' => 'actor-calendar@example.com',
            'password' => 'password',
            'nip' => 'ACT-CAL-1',
        ]);

        $outsideUser = User::create([
            'name' => 'Outside Calendar',
            'email' => 'outside-calendar@example.com',
            'password' => 'password',
            'nip' => 'OUT-CAL-1',
        ]);

        Employee::create([
            'id_pegawai' => 'EMP-OUT-CAL',
            'nip' => 'OUT-CAL-1',
            'nama' => 'Outside Employee Calendar',
            'satuan_kerja' => 'UNIT B',
            'status_aktif' => 'Aktif',
        ]);

        $shift = WorkShift::firstOrFail();

        $this->actingAs($actor);

        Livewire::test(UnitShiftCalendar::class, ['unit' => 'UNIT A'])
            ->set('selectedCell', $outsideUser->id.'_2026-02-12')
            ->set('quickEditShiftId', (string) $shift->id)
            ->set('quickEditEndDate', '2026-02-12')
            ->call('saveQuickEdit');

        $this->assertDatabaseCount('employee_shift_assignments', 0);
    }

    public function test_unit_shift_detail_blocks_overlap_assignment_save(): void
    {
        $actor = User::create([
            'name' => 'Actor',
            'email' => 'actor2@example.com',
            'password' => 'password',
            'nip' => 'UNITA-1',
        ]);

        Employee::create([
            'id_pegawai' => 'EMP-1',
            'nip' => 'UNITA-1',
            'nama' => 'Unit A Employee',
            'satuan_kerja' => 'UNIT A',
            'status_aktif' => 'Aktif',
        ]);

        $shift = WorkShift::firstOrFail();

        EmployeeShiftAssignment::create([
            'user_id' => $actor->id,
            'work_shift_id' => $shift->id,
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-10',
            'created_by' => $actor->id,
        ]);

        $this->actingAs($actor);

        Livewire::test(UnitShiftDetail::class, ['unit' => 'UNIT A'])
            ->set('user_id', (string) $actor->id)
            ->set('work_shift_id', (string) $shift->id)
            ->set('start_date', '2026-02-05')
            ->set('end_date', '2026-02-12')
            ->call('save')
            ->assertHasErrors(['start_date']);

        $this->assertDatabaseCount('employee_shift_assignments', 1);
    }

    public function test_unit_shift_calendar_quick_edit_replaces_permanent_overlap(): void
    {
        $actor = User::create([
            'name' => 'Actor',
            'email' => 'actor3@example.com',
            'password' => 'password',
            'nip' => 'UNITA-2',
        ]);

        Employee::create([
            'id_pegawai' => 'EMP-2',
            'nip' => 'UNITA-2',
            'nama' => 'Unit A Employee 2',
            'satuan_kerja' => 'UNIT A',
            'status_aktif' => 'Aktif',
        ]);

        $defaultShift = WorkShift::where('code', 'NORMAL')->firstOrFail();

        $newShift = WorkShift::create([
            'name' => 'Shift Baru',
            'code' => 'BARU',
            'start_time' => '09:00:00',
            'end_time' => '15:00:00',
            'early_arrival_threshold' => '08:40:00',
            'late_tolerance_minutes' => 5,
            'work_hours' => 6,
            'color' => 'red',
            'is_default' => false,
            'is_active' => true,
        ]);

        EmployeeShiftAssignment::create([
            'user_id' => $actor->id,
            'work_shift_id' => $defaultShift->id,
            'start_date' => '2026-01-01',
            'end_date' => null,
            'created_by' => $actor->id,
        ]);

        $this->actingAs($actor);

        Livewire::test(UnitShiftCalendar::class, ['unit' => 'UNIT A'])
            ->set('selectedCell', $actor->id.'_2026-02-12')
            ->set('quickEditShiftId', (string) $newShift->id)
            ->set('quickEditEndDate', '2026-02-14')
            ->call('saveQuickEdit');

        $this->assertDatabaseCount('employee_shift_assignments', 1);
        $this->assertDatabaseHas('employee_shift_assignments', [
            'user_id' => $actor->id,
            'work_shift_id' => $newShift->id,
            'start_date' => '2026-02-12',
            'end_date' => '2026-02-14',
        ]);
    }
}
