<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasUniqueSlug;

    protected $fillable = [
        'icon', 'title', 'slug', 'short_description', 'status', 'featured', 'sort_order',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }
}
