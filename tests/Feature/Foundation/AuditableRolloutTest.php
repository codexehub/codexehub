<?php

namespace Tests\Feature\Foundation;

use App\Models\AuditLog;
use App\Models\Concerns\Auditable;
use App\Models\Consultation;
use App\Models\Customer;
use App\Models\License;
use App\Models\LicenseCustomer;
use App\Models\LicensedApp;
use App\Models\LicensedAppPackage;
use App\Models\LicenseTransaction;
use App\Models\Portfolio;
use App\Models\PortfolioCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditableRolloutTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_admin_managed_model_uses_the_auditable_trait(): void
    {
        $models = [
            Customer::class,
            Product::class,
            ProductCategory::class,
            Service::class,
            Portfolio::class,
            PortfolioCategory::class,
            TeamMember::class,
            Consultation::class,
            User::class,
            Role::class,
            LicensedApp::class,
            LicensedAppPackage::class,
            License::class,
            LicenseCustomer::class,
            LicenseTransaction::class,
        ];

        foreach ($models as $model) {
            $this->assertContains(
                Auditable::class,
                class_uses_recursive($model),
                "{$model} should use the Auditable trait"
            );
        }
    }

    public function test_customer_lifecycle_is_audited(): void
    {
        $customer = Customer::factory()->create();
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created', 'entity_type' => Customer::class, 'entity_id' => $customer->id,
        ]);

        $customer->update(['company_name' => 'Renamed Ltd']);
        $updated = AuditLog::where('action', 'updated')->where('entity_id', $customer->id)->firstOrFail();
        $this->assertSame(['company_name' => 'Renamed Ltd'], $updated->new_values);

        $customer->delete();
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted', 'entity_type' => Customer::class, 'entity_id' => $customer->id,
        ]);
    }

    public function test_license_last_verified_at_change_is_not_audited(): void
    {
        $app = LicensedApp::create(['name' => 'Ecom', 'slug' => 'ecom', 'is_active' => true]);
        $package = $app->packages()->create([
            'name' => 'Basic', 'price' => 10, 'duration_days' => 365, 'max_activations' => 1, 'features' => [], 'is_active' => true,
        ]);
        $license = License::create([
            'license_customer_id' => LicenseCustomer::factory()->create()->id,
            'licensed_app_id' => $app->id,
            'licensed_app_package_id' => $package->id,
            'status' => 'active',
        ]);

        AuditLog::query()->delete();
        $license->update(['last_verified_at' => now()]);

        $this->assertSame(0, AuditLog::where('action', 'updated')->where('entity_id', $license->id)->count());

        // A real status change is still audited.
        $license->update(['status' => 'suspended']);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'updated', 'entity_type' => License::class, 'entity_id' => $license->id,
        ]);
    }
}
