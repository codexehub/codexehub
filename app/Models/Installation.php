<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * SRS §57 — one row per customer site running a Codexehub product. `INST-002`'s
 * register/verify/heartbeat Vendor API endpoints operate on this table and are
 * expected to bind `licenses.installation_id` to this row's business
 * `installation_id` once built; this task only lands the table + model.
 */
class Installation extends Model
{
    use Auditable, HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_SUSPENDED = 'suspended';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_SUSPENDED,
    ];

    public const ENVIRONMENT_PRODUCTION = 'production';

    public const ENVIRONMENT_STAGING = 'staging';

    public const ENVIRONMENT_DEVELOPMENT = 'development';

    /** @var list<string> */
    public const ENVIRONMENTS = [
        self::ENVIRONMENT_PRODUCTION,
        self::ENVIRONMENT_STAGING,
        self::ENVIRONMENT_DEVELOPMENT,
    ];

    protected $fillable = [
        'customer_id', 'product_id', 'licensed_app_package_id', 'installation_id',
        'domain', 'environment', 'vendor_client_version', 'software_version',
        'status', 'last_heartbeat_at', 'last_verified_at', 'last_deployed_at',
    ];

    protected function casts(): array
    {
        return [
            'last_heartbeat_at' => 'datetime',
            'last_verified_at' => 'datetime',
            'last_deployed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Installation $installation) {
            if (empty($installation->installation_id)) {
                $installation->installation_id = static::generateIdentifier();
            }

            if (empty($installation->status)) {
                $installation->status = self::STATUS_PENDING;
            }

            if (empty($installation->environment)) {
                $installation->environment = self::ENVIRONMENT_PRODUCTION;
            }
        });
    }

    /**
     * SRS §38 example format, e.g. "INS-100001" — collision-checked like
     * `License::generateKey()`.
     */
    public static function generateIdentifier(): string
    {
        do {
            $id = 'INS-'.strtoupper(Str::random(8));
        } while (static::where('installation_id', $id)->exists());

        return $id;
    }

    /**
     * `GET /installations/{id}` (SRS §65, INST-002) addresses this business
     * key, not the internal auto-increment id.
     */
    public function getRouteKeyName(): string
    {
        return 'installation_id';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Temporary stand-in for the SRS §52 `customer_packages` package until
     * Phase 2 (PKG-001) exists — the same convention `License::package()`
     * already uses.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(LicensedAppPackage::class, 'licensed_app_package_id');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeEnvironment(Builder $query, string $environment): Builder
    {
        return $query->where('environment', $environment);
    }
}
