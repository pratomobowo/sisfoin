<?php

namespace Tests\Feature;

use App\Imports\SlipGajiImport;
use ReflectionClass;
use Tests\TestCase;

class SlipGajiImportNipPrecisionTest extends TestCase
{
    public function test_format_nip_preserves_18_digit_string_exactly(): void
    {
        $import = new SlipGajiImport(1);
        $ref = new ReflectionClass($import);
        $method = $ref->getMethod('formatNip');
        $method->setAccessible(true);

        $result = $method->invoke($import, '197107212005011002');

        $this->assertSame('197107212005011002', $result);
    }

    public function test_format_nip_preserves_excel_text_with_leading_quote(): void
    {
        $import = new SlipGajiImport(1);
        $ref = new ReflectionClass($import);
        $method = $ref->getMethod('formatNip');
        $method->setAccessible(true);

        $result = $method->invoke($import, "'197107212005011002");

        $this->assertSame('197107212005011002', $result);
    }
}
