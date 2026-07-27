<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioSetting extends Model
{
    protected $fillable = [
        'section_visible', 'section_title', 'section_subtitle', 'projects_count', 'ordering',
    ];

    protected function casts(): array
    {
        return [
            'section_visible' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'section_visible' => true,
            'section_title' => 'Our Recent Work',
            'section_subtitle' => 'Explore our latest successful projects',
            'projects_count' => 6,
            'ordering' => 'featured_first',
        ]);
    }
}
