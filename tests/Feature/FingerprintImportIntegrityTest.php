<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\MesinFinger;
use App\Services\FingerprintService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FingerprintImportIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_without_device_serial_uses_unknown_machine_not_id_one_fallback(): void
    {
        $result = (new FingerprintService())->saveAttendanceLogsToDatabase([
            [
                'adms_id' => 1001,
                'pin' => 'PIN001',
                'name' => 'Pegawai Test',
                'datetime' => '2026-02-16 08:00:00',
                'status' => 0,
                'verify' => 1,
            ],
        ], false);

        $this->assertTrue($result['success']);

        $log = AttendanceLog::firstOrFail();
        $machine = MesinFinger::findOrFail($log->mesin_finger_id);

        $this->assertSame('UNKNOWN_ADMS_DEVICE', $machine->serial_number);
    }

    public function test_import_is_idempotent_by_adms_id_and_resets_processed_when_changed(): void
    {
        $service = new FingerprintService();

        $service->saveAttendanceLogsToDatabase([
            [
                'adms_id' => 1002,
                'pin' => 'PIN002',
                'name' => 'Pegawai Test',
                'datetime' => '2026-02-16 08:00:00',
                'status' => 0,
                'verify' => 1,
            ],
        ], false);

        $log = AttendanceLog::firstOrFail();
        $log->update(['processed_at' => now()]);

        $result = $service->saveAttendanceLogsToDatabase([
            [
                'adms_id' => 1002,
                'pin' => 'PIN002',
                'name' => 'Pegawai Test Updated',
                'datetime' => '2026-02-16 08:15:00',
                'status' => 0,
                'verify' => 1,
            ],
        ], false);

        $this->assertTrue($result['success']);
        $this->assertSame(1, AttendanceLog::count());

        $log->refresh();

        $this->assertSame('2026-02-16 08:15:00', $log->datetime->format('Y-m-d H:i:s'));
        $this->assertNull($log->processed_at);
    }
}
