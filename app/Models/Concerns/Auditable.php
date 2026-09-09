<?php

namespace App\Models\Concerns;

use App\Services\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;

/**
 * Records `created` / `updated` / `deleted` / `restored` on the model to
 * `audit_logs` via AuditLogger.
 *
 * Pivot / relation syncs are invisible to model events — log those explicitly
 * with `app(AuditLogger::class)->log(...)` from the service or controller.
 *
 * Override `auditExclude()` to keep sensitive or noisy attributes out of the
 * recorded values.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn (Model $model) => static::writeAudit('created', $model, null, $model->auditableAttributes()));

        static::updated(function (Model $model) {
            $changed = array_keys($model->getChanges());
            $new = $model->auditableAttributes($changed);

            if ($new === []) {
                return; // only excluded attributes changed
            }

            static::writeAudit('updated', $model, array_intersect_key($model->getOriginal(), $new), $new);
        });

        static::deleted(fn (Model $model) => static::writeAudit('deleted', $model, $model->auditableAttributes(), null));

        if (method_exists(static::class, 'restored')) {
            static::restored(fn (Model $model) => static::writeAudit('restored', $model, null, $model->auditableAttributes()));
        }
    }

    /**
     * @return list<string>
     */
    public function auditExclude(): array
    {
        return ['password', 'remember_token', 'created_at', 'updated_at', 'deleted_at'];
    }

    /**
     * The model's attributes, minus the excluded ones, optionally narrowed to
     * a set of keys.
     *
     * @param  list<string>|null  $only
     * @return array<string, mixed>
     */
    public function auditableAttributes(?array $only = null): array
    {
        $attributes = $this->attributesToArray();

        if ($only !== null) {
            $attributes = array_intersect_key($attributes, array_flip($only));
        }

        return array_diff_key($attributes, array_flip($this->auditExclude()));
    }

    protected static function writeAudit(string $action, Model $model, ?array $old, ?array $new): void
    {
        app(AuditLogger::class)->log($action, $model, $old, $new);
    }
}
