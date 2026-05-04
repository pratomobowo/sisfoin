<?php

namespace Tests\Unit;

use App\Services\PayrollCalculationService;
use PHPUnit\Framework\TestCase;

class PayrollCalculationServiceTest extends TestCase
{
    public function test_reconciles_payroll_totals_from_components(): void
    {
        $result = (new PayrollCalculationService())->reconcile([
            'gaji_pokok' => 1_000_000,
            'honor_tetap' => 500_000,
            'tpp' => 250_000,
            'penerimaan_kotor' => 9_999_999,
            'potongan_arisan' => 50_000,
            'potongan_koperasi' => 100_000,
            'pajak' => 25_000,
            'pph21_kurang_dipotong' => 10_000,
            'penerimaan_bersih' => 9_999_999,
        ]);

        $this->assertSame(1_750_000.0, $result['penerimaan_kotor']);
        $this->assertSame(185_000.0, $result['total_potongan']);
        $this->assertSame(1_565_000.0, $result['penerimaan_bersih']);
    }
}
