<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Licensing-domain link row for one licensable application identity — it owns
 * packages and licenses and is what the unsigned Vendor API matches on
 * (`licensed_apps.slug` / `is_active`).
 *
 * PROD-001 fold-in: demoted to a bridge onto the canonical control-plane product
 * (`App\Models\Product`, the marketing + control-plane catalogue with the
 * software/physical split). `product_id` is nullable — a licensing row with no
 * matching product stays unlinked until an admin picks one. LIC-CP-001 / INST-002
 * move the Vendor API onto the product directly.
 */
class LicensedApp extends Model
{
    use Auditable, HasUniqueSlug;

    protected $fillable = ['product_id', 'name', 'slug', 'description', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected function slugSourceColumn(): string
    {
        return 'name';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(LicensedAppPackage::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
}
