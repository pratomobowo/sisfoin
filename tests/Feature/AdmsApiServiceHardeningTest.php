<?php

namespace Tests\Feature;

use App\Services\AdmsApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdmsApiServiceHardeningTest extends TestCase
{
    public function test_get_all_attendance_logs_continues_when_meta_total_missing(): void
    {
        config()->set('adms.api_url', 'https://adms.example.test');
        config()->set('adms.api_token', 'token');

        $firstPage = array_map(fn ($i) => $this->attendanceRow($i), range(1, 100));
        $secondPage = [$this->attendanceRow(101)];

        Http::fake([
            'adms.example.test/hr/attendances*' => Http::sequence()
                ->push(['success' => true, 'data' => $firstPage], 200)
                ->push(['success' => true, 'data' => $secondPage], 200),
        ]);

        $result = (new AdmsApiService())->getAllAttendanceLogs('2026-02-16', '2026-02-16');

        $this->assertTrue($result['success']);
        $this->assertCount(101, $result['data']);
    }

    public function test_bad_attendance_rows_are_skipped_not_fatal(): void
    {
        config()->set('adms.api_url', 'https://adms.example.test');
        config()->set('adms.api_token', 'token');

        Http::fake([
            'adms.example.test/hr/attendances*' => Http::response([
                'success' => true,
                'data' => [
                    $this->attendanceRow(1),
                    ['id' => 2, 'employee_id' => 'PIN002'],
                ],
                'meta' => ['total' => 2],
            ], 200),
        ]);

        $result = (new AdmsApiService())->getAttendanceLogs('2026-02-16', '2026-02-16');

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['data']);
        $this->assertSame('PIN001', $result['data'][0]['pin']);
    }

    private function attendanceRow(int $id): array
    {
        return [
            'id' => $id,
            'employee_id' => 'PIN'.str_pad((string) $id, 3, '0', STR_PAD_LEFT),
            'timestamp' => '2026-02-16 08:00:00',
            'check_type' => false,
            'verify_mode' => true,
            'device_sn' => 'DEVICE-1',
        ];
    }
}
