<?php

namespace Tests\Feature\Livewire;

use App\Models\User;
use App\Models\MesinFinger;
use Livewire\Livewire;
use Tests\TestCase;

class MesinFingerManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a superadmin user
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');
        
        $this->actingAs($superadmin);
        setActiveRole('super-admin');
    }

    public function test_superadmin_can_view_mesin_finger_management_component()
    {
        $response = $this->get('/superadmin/mesin-finger');
        $response->assertStatus(200);
    }

    public function test_mesin_finger_management_displays_mesins()
    {
        // Create test mesin finger data
        MesinFinger::factory()->create([
            'nama_mesin' => 'Test Mesin 1',
            'ip_address' => '192.168.1.100',
            'port' => 4370,
            'status' => 'active'
        ]);

        Livewire::test('superadmin.mesin-finger-management')
            ->assertSee('Test Mesin 1')
            ->assertSee('192.168.1.100:4370')
            ->assertSee('active');
    }

    public function test_superadmin_can_open_create_modal()
    {
        Livewire::test('superadmin.mesin-finger-management')
            ->call('create')
            ->assertSet('showModal', true)
            ->assertSet('editMode', false)
            ->assertSee('Tambah Mesin Fingerprint');
    }

    public function test_superadmin_can_open_edit_modal()
    {
        $mesin = MesinFinger::factory()->create([
            'nama_mesin' => 'Test Mesin Edit',
            'ip_address' => '192.168.1.101',
            'port' => 4370,
            'status' => 'inactive'
        ]);

        Livewire::test('superadmin.mesin-finger-management')
            ->call('edit', $mesin->id)
            ->assertSet('showModal', true)
            ->assertSet('editMode', true)
            ->assertSet('nama_mesin', 'Test Mesin Edit')
            ->assertSee('Edit Mesin Fingerprint');
    }

    public function test_superadmin_can_open_delete_confirmation_modal()
    {
        $mesin = MesinFinger::factory()->create([
            'nama_mesin' => 'Test Mesin Delete',
            'ip_address' => '192.168.1.102',
            'port' => 4370
        ]);

        Livewire::test('superadmin.mesin-finger-management')
            ->call('confirmDelete', $mesin->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('mesinToDelete->id', $mesin->id)
            ->assertSee('Hapus Mesin Fingerprint');
    }

    public function test_search_functionality_works()
    {
        // Create test data
        MesinFinger::factory()->create([
            'nama_mesin' => 'Mesin A',
            'ip_address' => '192.168.1.100'
        ]);

        MesinFinger::factory()->create([
            'nama_mesin' => 'Mesin B',
            'ip_address' => '192.168.1.101'
        ]);

        $component = Livewire::test('superadmin.mesin-finger-management')
            ->assertSee('Mesin A')
            ->assertSee('Mesin B');

        // Test search by name
        $component->set('search', 'Mesin A')
            ->assertSee('Mesin A')
            ->assertDontSee('Mesin B');

        // Test search by IP
        $component->set('search', '192.168.1.101')
            ->assertDontSee('Mesin A')
            ->assertSee('Mesin B');
    }

    public function test_status_filter_works()
    {
        // Create test data
        MesinFinger::factory()->create([
            'nama_mesin' => 'Active Mesin',
            'status' => 'active'
        ]);

        MesinFinger::factory()->create([
            'nama_mesin' => 'Inactive Mesin',
            'status' => 'inactive'
        ]);

        $component = Livewire::test('superadmin.mesin-finger-management')
            ->assertSee('Active Mesin')
            ->assertSee('Inactive Mesin');

        // Test status filter
        $component->set('statusFilter', 'active')
            ->assertSee('Active Mesin')
            ->assertDontSee('Inactive Mesin');

        $component->set('statusFilter', 'inactive')
            ->assertDontSee('Active Mesin')
            ->assertSee('Inactive Mesin');
    }

    public function test_can_open_data_modal()
    {
        $mesin = MesinFinger::factory()->create([
            'nama_mesin' => 'Data Test Mesin',
            'ip_address' => '192.168.1.103',
            'port' => 4370
        ]);

        Livewire::test('superadmin.mesin-finger-management')
            ->call('showDataModal', $mesin->id)
            ->assertSet('showDataModal', true)
            ->assertSet('selectedMesin->id', $mesin->id)
            ->assertSee('Data Mesin Fingerprint - Data Test Mesin')
            ->assertSee('Data Absensi')
            ->assertSee('Data User')
            ->assertSee('Status Device');
    }

    public function test_can_switch_tabs_in_data_modal()
    {
        $mesin = MesinFinger::factory()->create();

        $component = Livewire::test('superadmin.mesin-finger-management')
            ->call('showDataModal', $mesin->id)
            ->assertSet('activeTab', 'attendance');

        // Test switching tabs
        $component->call('switchTab', 'users')
            ->assertSet('activeTab', 'users');

        $component->call('switchTab', 'device')
            ->assertSet('activeTab', 'device');

        $component->call('switchTab', 'attendance')
            ->assertSet('activeTab', 'attendance');
    }
}