<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LicensedApp extends Model
{
    use HasUniqueSlug;

    protected $fillable = ['name', 'slug', 'description', 'is_active'];

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

    public function packages(): HasMany
    {
        return $this->hasMany(LicensedAppPackage::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
}
