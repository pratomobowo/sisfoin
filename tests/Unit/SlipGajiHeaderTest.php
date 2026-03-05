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
        $header = SlipGajiHeader::factory()->create();

        $this->assertEquals('draft', $header->status);
        $this->assertTrue($header->isDraft());
    }

    public function test_is_draft_returns_true_for_draft_status(): void
    {
        $header = SlipGajiHeader::factory()->create(['status' => 'draft']);

        $this->assertTrue($header->isDraft());
    }

    public function test_is_published_returns_true_for_published_status(): void
    {
        $header = SlipGajiHeader::factory()->create(['status' => 'published']);

        $this->assertTrue($header->isPublished());
    }

    public function test_can_be_edited_returns_true_for_draft(): void
    {
        $header = SlipGajiHeader::factory()->create(['status' => 'draft']);

        $this->assertTrue($header->isEditable());
    }

    public function test_can_be_edited_returns_false_for_published(): void
    {
        $header = SlipGajiHeader::factory()->create(['status' => 'published']);

        $this->assertFalse($header->isEditable());
    }

    public function test_publisher_relationship(): void
    {
        $user = User::factory()->create();
        $header = SlipGajiHeader::factory()->create([
            'status' => 'published',
            'published_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $header->publisher);
        $this->assertEquals($user->id, $header->publisher->id);
    }
}
