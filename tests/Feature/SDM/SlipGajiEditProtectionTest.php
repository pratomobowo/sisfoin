<?php

namespace Tests\Feature\SDM;

use App\Models\SlipGajiDetail;
use App\Models\SlipGajiHeader;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlipGajiEditProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super-admin');
        $this->actingAs($user);
    }

    public function test_update_returns_error_for_published_slip(): void
    {
        $header = SlipGajiHeader::factory()->published()->create();
        $detail = SlipGajiDetail::factory()->create(['header_id' => $header->id]);

        $response = $this->put(route('sdm.slip-gaji.update', $detail->id), [
            'nip' => '123456',
            'status' => 'tetap',
            'gaji_pokok' => 5000000,
        ]);

        $response->assertSessionHas('error');
        $response->assertRedirect();
    }

    public function test_destroy_returns_error_for_published_slip(): void
    {
        $header = SlipGajiHeader::factory()->published()->create();

        $response = $this->delete(route('sdm.slip-gaji.destroy', $header->id));

        $response->assertSessionHas('error');
        $response->assertRedirect();
    }
}
