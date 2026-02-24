<?php

namespace Tests\Feature\Livewire\Superadmin;

use App\Livewire\Superadmin\AttendanceLogs;
use App\Models\User;
use App\Services\FingerprintService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class AttendanceLogsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_pull_fingerprint_data_dispatches_success_toast_with_summary_message(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        $fingerprintService = Mockery::mock('overload:'.FingerprintService::class);
        $fingerprintService->shouldReceive('getAttendanceLogs')
            ->once()
            ->with('2026-02-01', '2026-02-02')
            ->andReturn([
                'success' => true,
                'data' => [
                    ['pin' => '1001', 'datetime' => '2026-02-01 08:00:00'],
                ],
            ]);

        $fingerprintService->shouldReceive('saveAttendanceLogsToDatabase')
            ->once()
            ->andReturn([
                'success' => true,
                'saved_count' => 12,
                'updated_count' => 3,
                'processed_count' => 10,
            ]);

        Livewire::test(AttendanceLogs::class)
            ->set('pullOption', 'range')
            ->set('pullDateFrom', '2026-02-01')
            ->set('pullDateTo', '2026-02-02')
            ->call('pullFingerprintData')
            ->assertDispatched('toast-show', function (string $name, array $params): bool {
                $message = (string) ($params['message'] ?? '');

                return $name === 'toast-show'
                    && ($params['type'] ?? null) === 'success'
                    && str_contains($message, 'Disimpan: 12')
                    && str_contains($message, 'Diperbarui: 3')
                    && str_contains($message, 'diproses: 10');
            });
    }

    public function test_pull_fingerprint_data_dispatches_warning_toast_when_no_data_found(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        $fingerprintService = Mockery::mock('overload:'.FingerprintService::class);
        $fingerprintService->shouldReceive('getAttendanceLogs')
            ->once()
            ->with('2026-02-10', '2026-02-11')
            ->andReturn([
                'success' => true,
                'data' => [],
            ]);

        Livewire::test(AttendanceLogs::class)
            ->set('showPullDataModal', true)
            ->set('pullOption', 'range')
            ->set('pullDateFrom', '2026-02-10')
            ->set('pullDateTo', '2026-02-11')
            ->call('pullFingerprintData')
            ->assertDispatched('toast-show', function (string $name, array $params): bool {
                return $name === 'toast-show'
                    && ($params['type'] ?? null) === 'warning'
                    && ($params['message'] ?? null) === 'Tidak ada data ditemukan untuk periode tersebut';
            })
            ->assertSet('showPullDataModal', false);
    }

    public function test_pull_fingerprint_data_dispatches_error_toast_when_api_returns_failed_response(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        $fingerprintService = Mockery::mock('overload:'.FingerprintService::class);
        $fingerprintService->shouldReceive('getAttendanceLogs')
            ->once()
            ->with('2026-02-12', '2026-02-13')
            ->andReturn([
                'success' => false,
                'message' => 'Token expired',
            ]);

        Livewire::test(AttendanceLogs::class)
            ->set('pullOption', 'range')
            ->set('pullDateFrom', '2026-02-12')
            ->set('pullDateTo', '2026-02-13')
            ->call('pullFingerprintData')
            ->assertDispatched('toast-show', function (string $name, array $params): bool {
                return $name === 'toast-show'
                    && ($params['type'] ?? null) === 'error'
                    && ($params['message'] ?? null) === 'Gagal menarik data: Token expired';
            });
    }

    public function test_pull_fingerprint_data_dispatches_connectivity_error_toast_when_adms_unreachable(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        $fingerprintService = Mockery::mock('overload:'.FingerprintService::class);
        $fingerprintService->shouldReceive('getAttendanceLogs')
            ->once()
            ->with('2026-02-14', '2026-02-15')
            ->andThrow(new \Exception('Connection timed out'));

        Livewire::test(AttendanceLogs::class)
            ->set('pullOption', 'range')
            ->set('pullDateFrom', '2026-02-14')
            ->set('pullDateTo', '2026-02-15')
            ->call('pullFingerprintData')
            ->assertDispatched('toast-show', function (string $name, array $params): bool {
                return $name === 'toast-show'
                    && ($params['type'] ?? null) === 'error'
                    && ($params['message'] ?? null) === 'Gagal terhubung ke server ADMS. Periksa koneksi jaringan atau konfigurasi ADMS.';
            });
    }
}
