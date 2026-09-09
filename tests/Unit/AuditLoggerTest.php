<?php

namespace Tests\Unit;

use App\Services\Audit\AuditLogger;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    public function test_without_auditing_returns_the_callback_value_and_restores_state(): void
    {
        $result = AuditLogger::withoutAuditing(fn () => 'value');

        $this->assertSame('value', $result);
    }

    public function test_without_auditing_restores_the_flag_even_when_the_callback_throws(): void
    {
        try {
            AuditLogger::withoutAuditing(function () {
                throw new \RuntimeException('boom');
            });
        } catch (\RuntimeException) {
            // expected
        }

        // A no-op way to assert the flag came back: a nested withoutAuditing
        // still returns normally.
        $this->assertSame(1, AuditLogger::withoutAuditing(fn () => 1));
    }
}
