<?php

namespace Tests\Feature;

use App\Imports\SlipGajiImport;
use App\Imports\SlipGajiArrayImport;
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

    public function test_parse_numeric_handles_indonesian_dot_thousands(): void
    {
        $import = new SlipGajiImport(1);
        $ref = new ReflectionClass($import);
        $method = $ref->getMethod('parseNumeric');
        $method->setAccessible(true);

        $this->assertSame(1234567.0, $method->invoke($import, '1.234.567'));
        $this->assertSame(1234.56, $method->invoke($import, '1.234,56'));
    }

    public function test_array_import_parse_numeric_handles_indonesian_dot_thousands(): void
    {
        $import = new SlipGajiArrayImport();
        $ref = new ReflectionClass($import);
        $method = $ref->getMethod('parseNumeric');
        $method->setAccessible(true);

        $this->assertSame(1234567.0, $method->invoke($import, '1.234.567'));
        $this->assertSame(1234.56, $method->invoke($import, '1.234,56'));
    }
}
