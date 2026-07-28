<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Portfolio extends Model
{
    use HasUniqueSlug;

    protected $fillable = [
        'category_id', 'title', 'slug', 'short_description', 'description',
        'client_name', 'industry', 'location', 'duration', 'team_size', 'completed_date',
        'challenge', 'solution',
        'project_url', 'github_url', 'case_study_file', 'featured', 'status', 'sort_order',
        'testimonial_quote', 'testimonial_name', 'testimonial_role', 'testimonial_rating', 'testimonial_avatar',
    ];

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'completed_date' => 'date',
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

    public function highlights(): HasMany
    {
        return $this->hasMany(PortfolioHighlight::class)->orderBy('sort_order');
    }

    public function results(): HasMany
    {
        return $this->hasMany(PortfolioResult::class)->orderBy('sort_order');
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

    public function getCaseStudyFileUrlAttribute(): ?string
    {
        return $this->case_study_file ? Storage::disk('public')->url($this->case_study_file) : null;
    }

    public function getTestimonialAvatarUrlAttribute(): ?string
    {
        return $this->testimonial_avatar ? Storage::disk('public')->url($this->testimonial_avatar) : null;
    }
}
