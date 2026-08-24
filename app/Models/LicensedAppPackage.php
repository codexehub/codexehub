<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicensedAppPackage extends Model
{
    protected $fillable = [
        'licensed_app_id', 'name', 'price', 'duration_days', 'max_activations', 'features', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function licensedApp(): BelongsTo
    {
        return $this->belongsTo(LicensedApp::class);
    }
}
