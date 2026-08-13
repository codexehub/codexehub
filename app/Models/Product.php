<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasUniqueSlug;

    public const TYPE_SOFTWARE = 'software';

    public const TYPE_PHYSICAL = 'physical';

    protected $fillable = [
        'type', 'product_category_id', 'icon', 'image', 'title', 'slug', 'tagline',
        'short_description', 'description', 'price', 'price_unit', 'pricing_note',
        'demo_url', 'docs_url', 'version', 'status', 'featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function features(): HasMany
    {
        return $this->hasMany(ProductFeature::class)->orderBy('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeSoftware(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_SOFTWARE);
    }

    public function scopePhysical(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PHYSICAL);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    /**
     * Formatted price for the public cards / detail page, e.g. "$499 one-time".
     * Products with no price fall back to a "Custom pricing" label.
     */
    public function getPriceLabelAttribute(): string
    {
        if ($this->price === null) {
            return 'Custom Pricing';
        }

        $amount = '$'.number_format((float) $this->price, (fmod((float) $this->price, 1) === 0.0) ? 0 : 2);

        return $this->price_unit ? "{$amount} {$this->price_unit}" : $amount;
    }
}
