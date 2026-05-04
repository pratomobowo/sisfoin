<?php

namespace Tests\Feature\Livewire\Superadmin;

use App\Livewire\Superadmin\RoleForm;
use App\Livewire\Superadmin\RoleManagement;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleFormPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');
        $this->actingAs($superadmin);
        setActiveRole('super-admin');
    }

    public function test_role_form_saves_only_selected_partial_permissions(): void
    {
        Livewire::test(RoleForm::class)
            ->set('name', 'payroll-view-only')
            ->set('display_name', 'Payroll View Only')
            ->set('selectedPermissions', ['payroll.view'])
            ->call('save')
            ->assertHasNoErrors();

        $role = Role::where('name', 'payroll-view-only')->firstOrFail();

        $this->assertTrue($role->hasPermissionTo('payroll.view'));
        $this->assertFalse($role->hasPermissionTo('payroll.edit'));
        $this->assertFalse($role->hasPermissionTo('payroll.delete'));
    }

    public function test_role_form_loads_selected_permissions_when_editing(): void
    {
        $role = Role::create(['name' => 'partial-payroll', 'guard_name' => 'web']);
        $role->givePermissionTo('payroll.view', 'payroll.download');

        Livewire::test(RoleForm::class, ['roleId' => $role->id])
            ->assertSet('selectedPermissions', ['payroll.view', 'payroll.download']);
    }

    public function test_staff_role_keeps_only_view_permissions_and_profile_edit(): void
    {
        $staff = Role::where('name', 'staff')->firstOrFail();

        Livewire::test(RoleForm::class, ['roleId' => $staff->id])
            ->set('selectedPermissions', ['payroll.view', 'payroll.edit', 'profile.edit'])
            ->call('save')
            ->assertHasNoErrors();

        $staff->refresh();

        $this->assertTrue($staff->hasPermissionTo('payroll.view'));
        $this->assertTrue($staff->hasPermissionTo('profile.edit'));
        $this->assertFalse($staff->hasPermissionTo('payroll.edit'));
    }

    public function test_module_selection_helpers_report_coverage(): void
    {
        $component = Livewire::test(RoleForm::class)
            ->set('selectedPermissions', ['payroll.view', 'payroll.download']);

        $this->assertSame(
            ['selected' => 2, 'total' => 5],
            $component->instance()->getModuleSelectionCount('payroll_management')
        );
        $this->assertFalse($component->instance()->isModuleFullySelected('payroll_management'));

        $component->call('toggleModulePermissions', 'payroll_management');

        $this->assertTrue($component->instance()->isModuleFullySelected('payroll_management'));
    }

    public function test_bulk_assign_permissions_adds_only_selected_permissions(): void
    {
        $role = Role::create(['name' => 'bulk-partial-role', 'guard_name' => 'web']);

        Livewire::test(RoleManagement::class)
            ->set('selectedRoles', [$role->id])
            ->set('bulkPermissionModule', 'payroll_management')
            ->set('bulkSelectedPermissions', ['payroll.view'])
            ->call('bulkAssignPermissions')
            ->assertHasNoErrors();

        $role->refresh();

        $this->assertTrue($role->hasPermissionTo('payroll.view'));
        $this->assertFalse($role->hasPermissionTo('payroll.edit'));
        $this->assertFalse($role->hasPermissionTo('payroll.delete'));
    }
}
