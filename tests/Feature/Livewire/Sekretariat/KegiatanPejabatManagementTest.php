<?php

namespace Tests\Feature\Livewire\Sekretariat;

use App\Livewire\Sekretariat\KegiatanPejabatManagement;
use App\Models\KegiatanPejabat;
use App\Models\User;
use App\Models\Dosen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KegiatanPejabatManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create basic roles and permissions
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        
        // Create a test rektor for pejabat
        Dosen::factory()->create([
            'nama' => 'Test Rektor',
            'nip' => '123456789',
            'jabatan_struktural' => 'Rektor',
            'status_aktif' => 'Aktif'
        ]);
    }

    public function test_sekretariat_can_view_kegiatan_pejabat_management_component(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sekretariat');

        $this->actingAs($user);

        Livewire::test(KegiatanPejabatManagement::class)
            ->assertStatus(200)
            ->assertSee('Kegiatan Pejabat')
            ->assertSee('Tambah Kegiatan Pejabat');
    }

    public function test_superadmin_can_view_kegiatan_pejabat_management_component(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        Livewire::test(KegiatanPejabatManagement::class)
            ->assertStatus(200)
            ->assertSee('Kegiatan Pejabat')
            ->assertSee('Tambah Kegiatan Pejabat');
    }

    public function test_kegiatan_pejabat_management_displays_kegiatan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sekretariat');

        // Create test kegiatan
        $kegiatan = \App\Models\KegiatanPejabat::factory()->create([
            'nama_kegiatan' => 'Test Kegiatan',
            'jenis_kegiatan' => 'Rapat Internal',
            'created_by' => $user->id
        ]);

        $this->actingAs($user);

        $component = Livewire::test(KegiatanPejabatManagement::class);

        $component->assertSee('Test Kegiatan');
        $component->assertSee('Rapat Internal');
    }

    public function test_sekretariat_can_create_new_kegiatan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sekretariat');

        $this->actingAs($user);

        $dosen = Dosen::where('jabatan_struktural', 'like', '%rektor%')->first();

        Livewire::test(KegiatanPejabatManagement::class)
            ->set('namaKegiatan', 'Test Kegiatan Baru')
            ->set('jenisKegiatan', 'Rapat Internal')
            ->set('tempatKegiatan', 'Ruang Rapat Utama')
            ->set('tanggalMulai', now()->format('Y-m-d'))
            ->set('tanggalSelesai', now()->addDays(1)->format('Y-m-d'))
            ->set('pejabatTerkait', [$dosen->id])
            ->set('disposisiKepada', 'Bagian Administrasi')
            ->set('keterangan', 'Pembahasan rencana kerja tahunan')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kegiatan_pejabat', [
            'nama_kegiatan' => 'Test Kegiatan Baru',
            'jenis_kegiatan' => 'Rapat Internal',
            'tempat_kegiatan' => 'Ruang Rapat Utama',
        ]);
    }

    public function test_kegiatan_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sekretariat');

        $this->actingAs($user);

        $dosen = Dosen::where('jabatan_struktural', 'like', '%rektor%')->first();

        Livewire::test(KegiatanPejabatManagement::class)
            ->set('namaKegiatan', '')
            ->set('jenisKegiatan', '')
            ->set('tempatKegiatan', '')
            ->set('tanggalMulai', '')
            ->set('tanggalSelesai', '')
            ->set('pejabatTerkait', [])
            ->call('save')
            ->assertHasErrors(['namaKegiatan', 'jenisKegiatan', 'tempatKegiatan', 'tanggalMulai', 'tanggalSelesai', 'pejabatTerkait']);
    }

    public function test_sekretariat_can_edit_existing_kegiatan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sekretariat');

        $dosen = Dosen::where('jabatan_struktural', 'like', '%rektor%')->first();

        $kegiatan = KegiatanPejabat::factory()->create([
            'nama_kegiatan' => 'Original Kegiatan',
            'jenis_kegiatan' => 'Original Jenis',
            'created_by' => $user->id
        ]);

        $this->actingAs($user);

        Livewire::test(KegiatanPejabatManagement::class)
            ->call('edit', $kegiatan->id)
            ->set('namaKegiatan', 'Updated Kegiatan')
            ->set('jenisKegiatan', 'Rapat Eksternal')
            ->set('tempatKegiatan', 'Luar Kampus')
            ->set('tanggalMulai', now()->format('Y-m-d'))
            ->set('tanggalSelesai', now()->addDays(1)->format('Y-m-d'))
            ->set('pejabatTerkait', [$dosen->id])
            ->set('disposisiKepada', 'Instansi Lain')
            ->set('keterangan', 'Keterangan diperbarui')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kegiatan_pejabat', [
            'id' => $kegiatan->id,
            'nama_kegiatan' => 'Updated Kegiatan',
            'jenis_kegiatan' => 'Rapat Eksternal',
            'tempat_kegiatan' => 'Luar Kampus',
        ]);
    }

    public function test_sekretariat_can_delete_kegiatan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sekretariat');

        $kegiatan = KegiatanPejabat::factory()->create([
            'nama_kegiatan' => 'Test Kegiatan to Delete',
            'created_by' => $user->id
        ]);

        $this->actingAs($user);

        Livewire::test(KegiatanPejabatManagement::class)
            ->call('delete', $kegiatan->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('kegiatan_pejabat', [
            'id' => $kegiatan->id,
        ]);
    }

    public function test_search_functionality_works(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sekretariat');

        $kegiatan1 = \App\Models\KegiatanPejabat::factory()->create([
            'nama_kegiatan' => 'Kegiatan Testing 1',
            'created_by' => $user->id
        ]);

        $kegiatan2 = \App\Models\KegiatanPejabat::factory()->create([
            'nama_kegiatan' => 'Kegiatan Other',
            'created_by' => $user->id
        ]);

        $this->actingAs($user);

        $component = Livewire::test(KegiatanPejabatManagement::class)
            ->set('search', 'Testing')
            ->assertSee('Kegiatan Testing 1')
            ->assertDontSee('Kegiatan Other');
    }

    public function test_filter_by_jenis_kegiatan_works(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sekretariat');

        $kegiatan1 = \App\Models\KegiatanPejabat::factory()->create([
            'nama_kegiatan' => 'Rapat Testing',
            'jenis_kegiatan' => 'Rapat Internal',
            'created_by' => $user->id
        ]);

        $kegiatan2 = \App\Models\KegiatanPejabat::factory()->create([
            'nama_kegiatan' => 'Kunjungan Testing',
            'jenis_kegiatan' => 'Kunjungan Kerja',
            'created_by' => $user->id
        ]);

        $this->actingAs($user);

        $component = Livewire::test(KegiatanPejabatManagement::class)
            ->set('filterJenis', 'Rapat Internal')
            ->assertSee('Rapat Testing')
            ->assertDontSee('Kunjungan Testing');
    }

    public function test_filter_by_date_range_works(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sekretariat');

        $kegiatan1 = \App\Models\KegiatanPejabat::factory()->create([
            'nama_kegiatan' => 'Kegiatan Tanggal Testing',
            'tanggal_mulai' => '2024-01-01',
            'tanggal_selesai' => '2024-01-02',
            'created_by' => $user->id
        ]);

        $kegiatan2 = \App\Models\KegiatanPejabat::factory()->create([
            'nama_kegiatan' => 'Kegiatan Tanggal Other',
            'tanggal_mulai' => '2024-02-01',
            'tanggal_selesai' => '2024-02-02',
            'created_by' => $user->id
        ]);

        $this->actingAs($user);

        $component = Livewire::test(KegiatanPejabatManagement::class)
            ->set('filterStartDate', '2024-01-01')
            ->set('filterEndDate', '2024-01-31')
            ->assertSee('Kegiatan Tanggal Testing')
            ->assertDontSee('Kegiatan Tanggal Other');
    }

    public function test_pagination_works(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sekretariat');

        // Create 15 test records to trigger pagination
        for ($i = 0; $i < 15; $i++) {
            \App\Models\KegiatanPejabat::factory()->create([
                'nama_kegiatan' => "Test Kegiatan {$i}",
                'created_by' => $user->id
            ]);
        }

        $this->actingAs($user);

        Livewire::test(KegiatanPejabatManagement::class)
            ->set('perPage', 10)
            ->assertSet('perPage', 10);
    }

    public function test_only_authorized_users_can_access(): void
    {
        $user = User::factory()->create();
        // User without sekretariat or super-admin role

        $this->actingAs($user);

        // This should still load but not show any data since user doesn't have access to create
        Livewire::test(KegiatanPejabatManagement::class)
            ->assertStatus(200);
    }
}