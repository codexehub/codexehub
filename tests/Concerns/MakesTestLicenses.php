<?php

namespace Tests\Concerns;

use App\Models\License;
use App\Models\LicenseCustomer;
use App\Models\LicensedApp;

/**
 * Shared `License` builder for Vendor API license tests — mirrors the helper
 * `Feature/Api/LicenseApiLifecycleTest` already uses for the legacy endpoint
 * (no `LicenseFactory` exists yet).
 */
trait MakesTestLicenses
{
    private function makeLicense(string $status, array $overrides = []): License
    {
        $app = LicensedApp::create(['name' => 'Ecom', 'slug' => 'ecom-app-'.uniqid(), 'is_active' => true]);
        $package = $app->packages()->create([
            'name' => 'Basic', 'price' => 10, 'duration_days' => 365, 'max_activations' => 5, 'features' => [], 'is_active' => true,
        ]);
        $customer = LicenseCustomer::factory()->create();

        return License::create(array_merge([
            'license_customer_id' => $customer->id,
            'licensed_app_id' => $app->id,
            'licensed_app_package_id' => $package->id,
            'status' => $status,
            'expires_at' => now()->addMonth(),
        ], $overrides));
    }
}
