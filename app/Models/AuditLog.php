<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Append-only. Rows are written by App\Services\Audit\AuditLogger and never
 * updated — hence no `updated_at`.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForEntity($query, Model $entity)
    {
        return $query->where('entity_type', $entity->getMorphClass())
            ->where('entity_id', $entity->getKey());
    }

    /**
     * "Customer #12" — the short entity label for the audit viewer.
     */
    public function getEntityNameAttribute(): ?string
    {
        if ($this->entity_type === null) {
            return null;
        }

        return class_basename($this->entity_type).($this->entity_id !== null ? ' #'.$this->entity_id : '');
    }

    /**
     * Per-key before/after pairs for the audit viewer's "Changes" column.
     *
     * @return array<string, array{from: mixed, to: mixed}>
     */
    public function getChangeSetAttribute(): array
    {
        $keys = array_unique([...array_keys($this->old_values ?? []), ...array_keys($this->new_values ?? [])]);

        $changes = [];
        foreach ($keys as $key) {
            $changes[$key] = [
                'from' => $this->old_values[$key] ?? null,
                'to' => $this->new_values[$key] ?? null,
            ];
        }

        return $changes;
    }
}
