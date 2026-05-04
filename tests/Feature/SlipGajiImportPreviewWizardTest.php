<?php

namespace Tests\Feature;

use App\Models\SlipGajiHeader;
use App\Models\SlipGajiImportPreview;
use App\Models\User;
use App\Models\Employee;
use App\Models\SlipGajiDetail;
use App\Services\SlipGajiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class SlipGajiImportPreviewWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_tables_can_store_preview_rows_without_final_header(): void
    {
        $user = User::factory()->create();

        $preview = SlipGajiImportPreview::create([
            'token' => 'preview-token-1',
            'user_id' => $user->id,
            'periode' => '2026-05',
            'mode' => 'standard',
            'file_original' => 'slip.xlsx',
            'status' => 'ready',
            'summary_json' => ['row_count' => 1],
            'row_count' => 1,
            'error_count' => 0,
            'expires_at' => now()->addMinutes(30),
        ]);

        $preview->rows()->create([
            'row_number' => 2,
            'nip' => '197107212005011002',
            'nama' => 'Pegawai Test',
            'net_amount' => 5000000,
            'gross_amount' => 5500000,
            'deduction_amount' => 500000,
            'data_json' => ['gaji_bersih' => 5000000],
            'validation_status' => 'valid',
            'validation_errors_json' => [],
        ]);

        $this->assertSame(1, $preview->rows()->count());
        $this->assertDatabaseMissing('slip_gaji_header', ['periode' => '2026-05']);
        $this->assertSame(0, SlipGajiHeader::count());
    }

    public function test_preview_import_creates_preview_only(): void
    {
        $employee = Employee::factory()->create([
            'nip' => '197107212005011002',
            'nama' => 'Pegawai Valid',
        ]);
        $user = User::factory()->create();

        $result = app(SlipGajiService::class)->previewImport(
            $this->makeSlipUpload([
                ['nip' => $employee->nip, 'gaji_pokok' => 4000000, 'penerimaan_bersih' => 4500000],
            ]),
            '2026-05',
            $user->id,
            'standard'
        );

        $this->assertTrue($result['success'], json_encode($result));
        $this->assertSame(1, SlipGajiImportPreview::count());
        $this->assertSame(1, SlipGajiImportPreview::first()->rows()->count());
        $this->assertSame(0, SlipGajiHeader::count());
        $this->assertSame(0, SlipGajiDetail::count());
    }

    public function test_preview_import_blocks_duplicate_nips(): void
    {
        $employee = Employee::factory()->create(['nip' => '197107212005011002']);
        $user = User::factory()->create();

        $result = app(SlipGajiService::class)->previewImport(
            $this->makeSlipUpload([
                ['nip' => $employee->nip, 'gaji_pokok' => 4000000, 'penerimaan_bersih' => 4500000],
                ['nip' => $employee->nip, 'gaji_pokok' => 4100000, 'penerimaan_bersih' => 4600000],
            ]),
            '2026-05',
            $user->id,
            'standard'
        );

        $preview = SlipGajiImportPreview::firstOrFail();

        $this->assertTrue($result['success'], json_encode($result));
        $this->assertSame('blocked', $preview->status);
        $this->assertSame(2, $preview->error_count);
        $this->assertSame(2, $preview->rows()->where('validation_status', 'error')->count());
    }

    public function test_preview_import_blocks_unmatched_nip(): void
    {
        $user = User::factory()->create();

        $result = app(SlipGajiService::class)->previewImport(
            $this->makeSlipUpload([
                ['nip' => '197107212005011999', 'gaji_pokok' => 4000000, 'penerimaan_bersih' => 4500000],
            ]),
            '2026-05',
            $user->id,
            'standard'
        );

        $preview = SlipGajiImportPreview::firstOrFail();

        $this->assertTrue($result['success']);
        $this->assertSame('blocked', $preview->status);
        $this->assertSame(1, $preview->error_count);
        $this->assertStringContainsString('NIP tidak ditemukan', implode(' ', $preview->rows()->first()->validation_errors_json));
    }

    public function test_preview_import_blocks_existing_period_and_mode_before_creating_preview(): void
    {
        $employee = Employee::factory()->create(['nip' => '197107212005011002']);
        $user = User::factory()->create();
        SlipGajiHeader::factory()->create([
            'periode' => '2026-05',
            'mode' => 'standard',
        ]);

        $result = app(SlipGajiService::class)->previewImport(
            $this->makeSlipUpload([
                ['nip' => $employee->nip, 'gaji_pokok' => 4000000, 'penerimaan_bersih' => 4500000],
            ]),
            '2026-05',
            $user->id,
            'standard'
        );

        $this->assertFalse($result['success']);
        $this->assertSame(0, SlipGajiImportPreview::count());
    }

    public function test_commit_preview_copies_valid_rows_to_final_tables(): void
    {
        $employee = Employee::factory()->create(['nip' => '197107212005011002']);
        $user = User::factory()->create();
        $previewResult = app(SlipGajiService::class)->previewImport(
            $this->makeSlipUpload([
                ['nip' => $employee->nip, 'gaji_pokok' => 4000000, 'penerimaan_bersih' => 4500000],
            ]),
            '2026-05',
            $user->id,
            'standard'
        );

        $result = app(SlipGajiService::class)->commitPreview($previewResult['token'], $user->id);

        $this->assertTrue($result['success'], json_encode($result));
        $this->assertSame(1, SlipGajiHeader::count());
        $this->assertSame(1, SlipGajiDetail::count());
        $this->assertSame('committed', SlipGajiImportPreview::first()->status);
        $this->assertSame('4500000.00', SlipGajiDetail::first()->gaji_bersih);
    }

    public function test_blocked_preview_cannot_commit(): void
    {
        $user = User::factory()->create();
        $previewResult = app(SlipGajiService::class)->previewImport(
            $this->makeSlipUpload([
                ['nip' => '197107212005011999', 'gaji_pokok' => 4000000, 'penerimaan_bersih' => 4500000],
            ]),
            '2026-05',
            $user->id,
            'standard'
        );

        $result = app(SlipGajiService::class)->commitPreview($previewResult['token'], $user->id);

        $this->assertFalse($result['success']);
        $this->assertSame(0, SlipGajiHeader::count());
        $this->assertSame(0, SlipGajiDetail::count());
    }

    public function test_preview_belonging_to_another_user_cannot_commit(): void
    {
        $employee = Employee::factory()->create(['nip' => '197107212005011002']);
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $previewResult = app(SlipGajiService::class)->previewImport(
            $this->makeSlipUpload([
                ['nip' => $employee->nip, 'gaji_pokok' => 4000000, 'penerimaan_bersih' => 4500000],
            ]),
            '2026-05',
            $owner->id,
            'standard'
        );

        $result = app(SlipGajiService::class)->commitPreview($previewResult['token'], $otherUser->id);

        $this->assertFalse($result['success']);
        $this->assertSame(0, SlipGajiHeader::count());
    }

    public function test_http_upload_flow_redirects_to_preview_without_final_rows(): void
    {
        $employee = Employee::factory()->create(['nip' => '197107212005011002']);
        $user = $this->actingAsPayrollAdmin();

        $response = $this->post(route('sdm.slip-gaji.upload.process'), [
            'file' => $this->makeSlipUpload([
                ['nip' => $employee->nip, 'gaji_pokok' => 4000000, 'penerimaan_bersih' => 4500000],
            ]),
            'bulan' => '05',
            'tahun' => '2026',
            'mode' => 'standard',
        ]);

        $preview = SlipGajiImportPreview::where('user_id', $user->id)->firstOrFail();

        $response->assertRedirect(route('sdm.slip-gaji.upload.preview.show', $preview->token));
        $this->assertSame(0, SlipGajiHeader::count());
        $this->assertSame(0, SlipGajiDetail::count());
    }

    public function test_preview_page_can_search_rows_by_nip_or_name(): void
    {
        $employeeA = Employee::factory()->create(['nip' => '197107212005011002', 'nama' => 'Pegawai Alpha']);
        $employeeB = Employee::factory()->create(['nip' => '197107212005011003', 'nama' => 'Pegawai Beta']);
        $user = $this->actingAsPayrollAdmin();

        $previewResult = app(SlipGajiService::class)->previewImport(
            $this->makeSlipUpload([
                ['nip' => $employeeA->nip, 'gaji_pokok' => 4000000, 'penerimaan_bersih' => 4500000],
                ['nip' => $employeeB->nip, 'gaji_pokok' => 4100000, 'penerimaan_bersih' => 4600000],
            ]),
            '2026-05',
            $user->id,
            'standard'
        );

        $this->get(route('sdm.slip-gaji.upload.preview.show', [
            'token' => $previewResult['token'],
            'search' => 'Alpha',
        ]))
            ->assertOk()
            ->assertSee('Pegawai Alpha')
            ->assertDontSee('Pegawai Beta');
    }

    private function actingAsPayrollAdmin(): User
    {
        foreach (['payroll.view', 'payroll.create', 'payroll.edit', 'payroll.download'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::create(['name' => 'payroll-preview-admin', 'guard_name' => 'web']);
        $role->givePermissionTo(['payroll.view', 'payroll.create', 'payroll.edit', 'payroll.download']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);
        setActiveRole('payroll-preview-admin');

        return $user;
    }

    private function makeSlipUpload(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['nip', 'status', 'gaji_pokok', 'penerimaan_bersih'];

        foreach ($headers as $column => $header) {
            $sheet->setCellValue([$column + 1, 1], $header);
        }

        foreach ($rows as $rowIndex => $row) {
            foreach ($headers as $column => $header) {
                if ($header === 'nip') {
                    $sheet->setCellValueExplicit([$column + 1, $rowIndex + 2], (string) ($row[$header] ?? ''), DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue([$column + 1, $rowIndex + 2], $row[$header] ?? null);
                }
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'slip-preview-').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'slip.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
