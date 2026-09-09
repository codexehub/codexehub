<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * The canonical customer of the Codexehub control plane (SRS §52) — the party a
 * package, offer, invoice, license, installation and ownership record all hang
 * off. Not a `users` row (that is admin staff).
 */
class Customer extends Model
{
    use Auditable, HasFactory;

    public const STATUS_PROSPECT = 'prospect';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_SUSPENDED = 'suspended';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_PROSPECT,
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_SUSPENDED,
    ];

    protected $fillable = [
        'name', 'company_name', 'email', 'phone', 'status', 'notes',
    ];

    /**
     * Legacy licensing-domain link rows (one per licensed buyer identity).
     * `licenses` hang off these until the licensing screens move to the FK.
     */
    public function licenseCustomers(): HasMany
    {
        return $this->hasMany(LicenseCustomer::class);
    }

    public function licenses(): HasManyThrough
    {
        return $this->hasManyThrough(License::class, LicenseCustomer::class);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
