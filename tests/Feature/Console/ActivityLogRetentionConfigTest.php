<?php

namespace Tests\Feature\Console;

use Tests\TestCase;

class ActivityLogRetentionConfigTest extends TestCase
{
    public function test_activity_log_retention_is_set_to_365_days(): void
    {
        $this->assertSame(365, config('activitylog.delete_records_older_than_days'));
    }
}
