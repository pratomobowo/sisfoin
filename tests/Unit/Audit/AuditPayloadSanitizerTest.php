<?php

namespace Tests\Unit\Audit;

use App\Support\Audit\AuditPayloadSanitizer;
use Tests\TestCase;

class AuditPayloadSanitizerTest extends TestCase
{
    public function test_redacts_secret_fields_and_masks_pii(): void
    {
        $payload = [
            'password' => 'super-secret',
            'token' => 'abc123',
            'email' => 'johndoe@example.com',
            'phone' => '081234567890',
            'nik' => '3276010101010001',
        ];

        $result = AuditPayloadSanitizer::sanitize($payload);

        $this->assertSame('[REDACTED]', $result['password']);
        $this->assertSame('[REDACTED]', $result['token']);
        $this->assertNotSame('johndoe@example.com', $result['email']);
        $this->assertNotSame('081234567890', $result['phone']);
        $this->assertNotSame('3276010101010001', $result['nik']);
    }

    public function test_sanitizes_nested_payloads(): void
    {
        $payload = [
            'request' => [
                'authorization' => 'Bearer xxx',
                'email' => 'admin@example.com',
            ],
        ];

        $result = AuditPayloadSanitizer::sanitize($payload);

        $this->assertSame('[REDACTED]', $result['request']['authorization']);
        $this->assertNotSame('admin@example.com', $result['request']['email']);
    }
}
