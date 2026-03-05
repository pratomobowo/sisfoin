<?php

namespace Tests\Feature;

use App\Models\SlipGajiDetail;
use App\Models\SlipGajiHeader;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffPayrollFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_staff_can_only_see_published_slips(): void
    {
        $user = User::factory()->create(['nip' => '123456789012345678']);
        $user->assignRole('staff');
        $this->actingAs($user);

        $publishedHeader = SlipGajiHeader::factory()->published()->create();
        $draftHeader = SlipGajiHeader::factory()->draft()->create();

        $publishedDetail = SlipGajiDetail::factory()->create([
            'header_id' => $publishedHeader->id,
            'nip' => $user->nip,
        ]);

        $draftDetail = SlipGajiDetail::factory()->create([
            'header_id' => $draftHeader->id,
            'nip' => $user->nip,
        ]);

        $response = $this->get(route('staff.penggajian.index'));

        $response->assertStatus(200);
        $response->assertViewHas('slipGaji');

        $slipGaji = $response->viewData('slipGaji');
        $this->assertTrue($slipGaji->contains('id', $publishedDetail->id));
        $this->assertFalse($slipGaji->contains('id', $draftDetail->id));
    }
}
