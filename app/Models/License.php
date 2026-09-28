<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class License extends Model
{
    use Auditable;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAYMENT_DUE = 'payment_due';

    public const STATUS_GRACE_PERIOD = 'grace_period';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_REACTIVATED = 'reactivated';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REVOKED = 'revoked';

    public const STATUS_EXPIRED = 'expired';

    /** @var list<string> SRS §39 full lifecycle. */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACTIVE,
        self::STATUS_PAYMENT_DUE,
        self::STATUS_GRACE_PERIOD,
        self::STATUS_SUSPENDED,
        self::STATUS_REACTIVATED,
        self::STATUS_COMPLETED,
        self::STATUS_REVOKED,
        self::STATUS_EXPIRED,
    ];

    /**
     * States in which the customer site should keep working. Per SRS §45/§46
     * only SUSPENDED/REVOKED/EXPIRED lock the site — PAYMENT_DUE/GRACE_PERIOD
     * are still operational by design (that's the entire point of a grace
     * period); COMPLETED (ownership completed) obviously stays usable too.
     *
     * @var list<string>
     */
    public const OPERATIONAL_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_PAYMENT_DUE,
        self::STATUS_GRACE_PERIOD,
        self::STATUS_REACTIVATED,
        self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'license_key', 'license_customer_id', 'licensed_app_id', 'licensed_app_package_id',
        'installation_id', 'status', 'issued_at', 'starts_at', 'expires_at',
        'grace_period_days', 'next_payment_due', 'last_verified_at', 'ownership_status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'next_payment_due' => 'date',
            'last_verified_at' => 'datetime',
            'grace_period_days' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (License $license) {
            if (empty($license->license_key)) {
                $license->license_key = static::generateKey();
            }

            if (empty($license->issued_at)) {
                $license->issued_at = now();
            }

            if (empty($license->starts_at)) {
                $license->starts_at = $license->issued_at;
            }

            if ($license->grace_period_days === null) {
                $license->grace_period_days = (int) ceil(config('licensing.default_grace_hours', 48) / 24);
            }
        });
    }

    public static function generateKey(): string
    {
        do {
            $key = strtoupper(implode('-', str_split(Str::random(20), 5)));
        } while (static::where('license_key', $key)->exists());

        return $key;
    }

    /**
     * `last_verified_at` is written on every Vendor-API verify call — auditing it
     * would bury real lifecycle changes under verification noise. The rest mirror
     * the Auditable trait default.
     *
     * @return list<string>
     */
    public function auditExclude(): array
    {
        return ['password', 'remember_token', 'created_at', 'updated_at', 'deleted_at', 'last_verified_at'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(LicenseCustomer::class, 'license_customer_id');
    }

    public function licensedApp(): BelongsTo
    {
        return $this->belongsTo(LicensedApp::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(LicensedAppPackage::class, 'licensed_app_package_id');
    }

    public function activations(): HasMany
    {
        return $this->hasMany(LicenseActivation::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LicenseTransaction::class);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Whether the customer site should keep working under this license state
     * (SRS §45/§46 — PAYMENT_DUE/GRACE_PERIOD are intentionally still usable).
     */
    public function isOperational(): bool
    {
        return in_array($this->status, self::OPERATIONAL_STATUSES, true);
    }

    public function isUsable(): bool
    {
        return $this->isOperational()
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
