<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class TeamMember extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'role',
        'photo',
        'description',
        'linkedin_url',
        'github_url',
        'status',
        'sort_order',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(TeamMemberImage::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }
}
