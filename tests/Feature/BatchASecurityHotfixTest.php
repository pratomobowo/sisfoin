<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\SlipGajiDetail;
use App\Models\SlipGajiHeader;
use App\Models\User;
use App\Services\SlipGajiEmailService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BatchASecurityHotfixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_staff_cannot_download_unpublished_slip(): void
    {
        $user = User::factory()->create(['nip' => '198001012006041001']);
        $user->assignRole('staff');

        $draftHeader = SlipGajiHeader::factory()->draft()->create();
        SlipGajiDetail::factory()->create([
            'header_id' => $draftHeader->id,
            'nip' => $user->nip,
        ]);

        $this->actingAs($user)
            ->get(route('staff.penggajian.download-pdf', $draftHeader->id))
            ->assertNotFound();
    }

    public function test_staff_dashboard_does_not_use_similar_user_salary(): void
    {
        $staff = User::factory()->create([
            'name' => 'Budi Santoso',
            'nip' => '111111111111111111',
        ]);
        $staff->assignRole('staff');

        $otherUser = User::factory()->create([
            'name' => 'Budi Lain',
            'nip' => '222222222222222222',
        ]);
        $otherUser->assignRole('staff');

        $publishedHeader = SlipGajiHeader::factory()->published()->create();
        SlipGajiDetail::factory()->create([
            'header_id' => $publishedHeader->id,
            'nip' => $otherUser->nip,
        ]);

        $response = $this->actingAs($staff)->get(route('staff.dashboard'));

        $response->assertOk();
        $quickStats = $response->viewData('quickStats');
        $this->assertNull($quickStats['latest_net_salary']);
        $this->assertNull($quickStats['latest_salary_period']);
    }

    public function test_bulk_email_rejects_ambiguous_payroll_recipient(): void
    {
        Queue::fake();

        $admin = User::factory()->create();
        $admin->assignRole('admin-sdm');
        $this->actingAs($admin);

        $header = SlipGajiHeader::factory()->published()->create();
        $detail = SlipGajiDetail::factory()->create([
            'header_id' => $header->id,
            'nip' => '198001012006041001',
        ]);

        Employee::factory()->create([
            'id_pegawai' => 'EMP001',
            'nip' => $detail->nip,
            'email_kampus' => 'pegawai-a@example.test',
        ]);

        Employee::factory()->create([
            'id_pegawai' => 'EMP002',
            'nip' => $detail->nip,
            'email_kampus' => 'pegawai-b@example.test',
        ]);

        $result = app(SlipGajiEmailService::class)->sendBulkEmail($header->id, [$detail->id]);

        $this->assertTrue($result['success']);
        $this->assertSame(0, $result['valid_recipients']);
        $this->assertSame(1, $result['invalid_recipients']);
        Queue::assertNothingPushed();
    }
}
