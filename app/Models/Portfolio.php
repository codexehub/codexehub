<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portfolio extends Model
{
    use HasUniqueSlug;

    protected $fillable = [
        'category_id', 'title', 'slug', 'short_description', 'description',
        'client_name', 'project_url', 'github_url', 'featured', 'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PortfolioCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PortfolioImage::class)->orderBy('sort_order');
    }

    public function technologies(): HasMany
    {
        return $this->hasMany(PortfolioTechnology::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeOrderedFor(Builder $query, string $ordering): Builder
    {
        return match ($ordering) {
            'newest' => $query->orderByDesc('created_at'),
            'oldest' => $query->orderBy('created_at'),
            'manual' => $query->orderBy('sort_order'),
            default => $query->orderByDesc('featured')->orderBy('sort_order'),
        };
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $thumbnail = $this->images->firstWhere('type', 'thumbnail');

        return $thumbnail?->url;
    }

    public function getBannerUrlAttribute(): ?string
    {
        $banner = $this->images->firstWhere('type', 'banner');

        return $banner?->url ?? $this->thumbnail_url;
    }

    public function getGalleryImagesAttribute()
    {
        return $this->images->where('type', 'gallery');
    }
}
