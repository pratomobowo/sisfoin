<?php

namespace Tests\Feature;

use App\Models\SlipGajiHeader;
use App\Models\EmailLog;
use App\Models\SlipGajiDetail;
use App\Models\User;
use App\Services\SlipGajiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PayrollIntegrityHotfixTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_import_does_not_leave_open_transaction(): void
    {
        $user = User::factory()->create();
        SlipGajiHeader::factory()->create([
            'periode' => '2026-03',
            'mode' => 'standard',
        ]);
        $transactionLevel = DB::transactionLevel();

        $result = app(SlipGajiService::class)->processAndStoreImport(
            UploadedFile::fake()->create('slip.xlsx', 1),
            '2026-03',
            $user->id,
            'standard'
        );

        $this->assertFalse($result['success']);
        $this->assertSame($transactionLevel, DB::transactionLevel());
    }

    public function test_cancel_import_is_blocked_when_email_job_is_active(): void
    {
        $header = SlipGajiHeader::factory()->draft()->create();
        $detail = SlipGajiDetail::factory()->create(['header_id' => $header->id]);

        EmailLog::create([
            'from_email' => 'hr@example.test',
            'to_email' => 'staff@example.test',
            'subject' => 'Slip Gaji',
            'message' => 'Slip Gaji',
            'status' => EmailLog::STATUS_PENDING,
            'slip_gaji_detail_id' => $detail->id,
        ]);

        $result = app(SlipGajiService::class)->cancelImport($header->id);

        $this->assertFalse($result['success']);
        $this->assertDatabaseHas('slip_gaji_header', ['id' => $header->id]);
        $this->assertDatabaseHas('slip_gaji_detail', ['id' => $detail->id]);
    }

    public function test_duplicate_slip_detail_report_command_succeeds_when_clean(): void
    {
        $header = SlipGajiHeader::factory()->draft()->create();
        SlipGajiDetail::factory()->create([
            'header_id' => $header->id,
            'nip' => '198001012006041001',
        ]);

        $this->artisan('slip-gaji:report-duplicate-details')
            ->expectsOutput('Duplicate slip gaji detail groups: 0')
            ->assertSuccessful();
    }
}
