<?php

namespace Tests\Feature\SDM;

use App\Models\SlipGajiHeader;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlipGajiPublishTest extends TestCase
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

    public function test_publish_changes_status_to_published(): void
    {
        $header = SlipGajiHeader::factory()->create(['status' => 'draft']);

        $response = $this->post(route('sdm.slip-gaji.publish', $header->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('published', $header->fresh()->status);
    }

    public function test_publish_sets_published_at_and_published_by(): void
    {
        $header = SlipGajiHeader::factory()->create(['status' => 'draft']);

        $response = $this->post(route('sdm.slip-gaji.publish', $header->id));

        $response->assertRedirect();

        $header = $header->fresh();
        $this->assertNotNull($header->published_at);
        $this->assertNotNull($header->published_by);
        $this->assertEquals(auth()->user()->id, $header->published_by);
    }

    public function test_unpublish_changes_status_to_draft(): void
    {
        $header = SlipGajiHeader::factory()->published()->create();

        $response = $this->post(route('sdm.slip-gaji.unpublish', $header->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('draft', $header->fresh()->status);
    }

    public function test_unpublish_clears_published_at_and_published_by(): void
    {
        $header = SlipGajiHeader::factory()->published()->create();

        $response = $this->post(route('sdm.slip-gaji.unpublish', $header->id));

        $response->assertRedirect();

        $header = $header->fresh();
        $this->assertNull($header->published_at);
        $this->assertNull($header->published_by);
    }
}
