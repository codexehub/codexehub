<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A reusable entitlement in the Codexehub feature catalog (SRS §7, §52).
 *
 * One catalog row per capability (payment_gateway, courier, seo, …). It is
 * selected into a customer-specific package (Phase 2), priced per customer,
 * configured against {@see $config_schema}, versioned, and synchronised to the
 * customer installation by {@see $key} over the Vendor API.
 */
class Feature extends Model
{
    use Auditable, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_DEPRECATED = 'deprecated';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_DEPRECATED,
    ];

    public const TYPE_CORE = 'core';

    public const TYPE_MODULE = 'module';

    public const TYPE_INTEGRATION = 'integration';

    public const TYPE_ANALYTICS = 'analytics';

    public const TYPE_MARKETING = 'marketing';

    public const TYPE_CUSTOM = 'custom';

    /** @var list<string> */
    public const TYPES = [
        self::TYPE_CORE,
        self::TYPE_MODULE,
        self::TYPE_INTEGRATION,
        self::TYPE_ANALYTICS,
        self::TYPE_MARKETING,
        self::TYPE_CUSTOM,
    ];

    protected $fillable = [
        'name', 'key', 'type', 'description', 'default_price',
        'version', 'status', 'dependencies', 'config_schema', 'documentation',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
            'dependencies' => 'array',
            'config_schema' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Feature $feature) {
            if (empty($feature->key)) {
                $feature->key = Str::slug((string) $feature->name, '_');
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * The catalog rows this feature declares as dependencies, resolved from the
     * stored key list. Missing / inactive keys are simply absent from the result
     * — FEAT-002 owns full dependency validation.
     *
     * @return Collection<int, Feature>
     */
    public function dependencyFeatures(): Collection
    {
        $keys = $this->dependencies ?? [];

        if ($keys === []) {
            return new Collection;
        }

        return static::query()->whereIn('key', $keys)->get();
    }
}
