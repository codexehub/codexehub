<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class License extends Model
{
    protected $fillable = [
        'license_key', 'license_customer_id', 'licensed_app_id', 'licensed_app_package_id',
        'status', 'expires_at', 'last_verified_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (License $license) {
            if (empty($license->license_key)) {
                $license->license_key = static::generateKey();
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

    public function isUsable(): bool
    {
        return $this->status === 'active'
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }
}
