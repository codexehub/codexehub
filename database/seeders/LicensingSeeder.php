<?php

namespace Database\Seeders;

use App\Models\LicensedApp;
use Illuminate\Database\Seeder;

class LicensingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $app = LicensedApp::firstOrCreate(
            ['slug' => 'ecommerce-laravel'],
            ['name' => 'Ecommerce Laravel', 'description' => 'The Ecommerce-Laravel storefront product.', 'is_active' => true]
        );

        $app->packages()->firstOrCreate(
            ['name' => 'Basic'],
            ['price' => 49.00, 'duration_days' => 365, 'max_activations' => 1, 'features' => ['storefront'], 'is_active' => true]
        );

        $app->packages()->firstOrCreate(
            ['name' => 'Pro'],
            ['price' => 99.00, 'duration_days' => 365, 'max_activations' => 3, 'features' => ['storefront', 'multi-domain', 'priority-support'], 'is_active' => true]
        );
    }
}
