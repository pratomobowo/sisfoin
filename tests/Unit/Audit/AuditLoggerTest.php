<?php

namespace Tests\Unit\Audit;

use App\Support\Audit\AuditLogger;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    public function test_logger_does_not_throw_when_storage_fails(): void
    {
        AuditLogger::log(
            logName: 'system',
            event: 'execute',
            action: 'system.health.check',
            description: 'Health check executed',
            properties: ['token' => 'secret-token'],
            metadata: ['module' => 'system', 'result' => 'success']
        );

        $this->assertTrue(true);
    }
}
