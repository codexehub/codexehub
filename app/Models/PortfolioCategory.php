<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioCategory extends Model
{
    use HasUniqueSlug;

    protected $fillable = ['name', 'slug', 'status'];

    protected function slugSourceColumn(): string
    {
        return 'name';
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class, 'category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
