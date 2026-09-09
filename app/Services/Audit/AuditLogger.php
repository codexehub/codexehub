<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes rows to `audit_logs`. Resolve from the container (`app(AuditLogger::class)`)
 * or type-hint it — no constructor dependencies.
 *
 * AUDIT-001 ships the writer + the `Auditable` model trait. AUDIT-002 wires it
 * across every module and adds the admin viewer.
 */
class AuditLogger
{
    private static bool $enabled = true;

    /**
     * Run a callback with audit writing suppressed (imports, seeders, back-fills).
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function withoutAuditing(callable $callback): mixed
    {
        $previous = self::$enabled;
        self::$enabled = false;

        try {
            return $callback();
        } finally {
            self::$enabled = $previous;
        }
    }

    public static function disable(): void
    {
        self::$enabled = false;
    }

    public static function enable(): void
    {
        self::$enabled = true;
    }

    public function log(
        string $action,
        ?Model $entity = null,
        ?array $old = null,
        ?array $new = null,
        ?Authenticatable $actor = null,
    ): ?AuditLog {
        if (! self::$enabled) {
            return null;
        }

        $actor ??= auth()->user();
        $request = request();

        return AuditLog::create([
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getAuthIdentifier(),
            'actor_label' => $this->labelFor($actor),
            'action' => $action,
            'entity_type' => $entity?->getMorphClass(),
            'entity_id' => $entity?->getKey(),
            'old_values' => $old !== null && $old !== [] ? $old : null,
            'new_values' => $new !== null && $new !== [] ? $new : null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }

    private function labelFor(?Authenticatable $actor): ?string
    {
        if ($actor === null) {
            return null;
        }

        return $actor->name ?? $actor->email ?? (string) $actor->getAuthIdentifier();
    }
}
