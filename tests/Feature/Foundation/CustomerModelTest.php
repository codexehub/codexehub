<?php

namespace Tests\Feature\Foundation;

use App\Models\Customer;
use App\Models\License;
use App\Models\LicenseCustomer;
use App\Models\LicensedApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_table_has_the_srs_52_columns(): void
    {
        foreach (['id', 'name', 'company_name', 'email', 'phone', 'status', 'created_at', 'updated_at'] as $column) {
            $this->assertTrue(
                Schema::hasColumn('customers', $column),
                "customers.{$column} missing",
            );
        }
    }

    public function test_status_scopes_filter_rows(): void
    {
        Customer::factory()->create();
        Customer::factory()->create();
        Customer::factory()->prospect()->create();

        $this->assertSame(2, Customer::active()->count());
        $this->assertSame(1, Customer::status(Customer::STATUS_PROSPECT)->count());
    }

    public function test_status_defaults_to_prospect(): void
    {
        $id = Customer::query()->insertGetId([
            'name' => 'No Status',
            'email' => 'no-status@acme.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('prospect', Customer::find($id)->status);
    }

    public function test_license_customer_belongs_to_a_customer(): void
    {
        $customer = Customer::factory()->create();
        $licenseCustomer = LicenseCustomer::factory()->for($customer)->create();

        $this->assertTrue($licenseCustomer->customer->is($customer));
        $this->assertTrue($customer->licenseCustomers->contains($licenseCustomer));
    }

    public function test_customer_reaches_its_licenses_through_license_customers(): void
    {
        $customer = Customer::factory()->create();
        $licenseCustomer = LicenseCustomer::factory()->for($customer)->create();

        $app = LicensedApp::create(['name' => 'Ecom', 'slug' => 'ecom', 'is_active' => true]);
        $package = $app->packages()->create([
            'name' => 'Basic', 'price' => 10, 'duration_days' => 365, 'max_activations' => 1, 'features' => [], 'is_active' => true,
        ]);

        $license = License::create([
            'license_customer_id' => $licenseCustomer->id,
            'licensed_app_id' => $app->id,
            'licensed_app_package_id' => $package->id,
            'status' => 'active',
        ]);

        $this->assertTrue($customer->licenses()->get()->contains($license));
    }

    public function test_deleting_a_customer_nulls_the_license_customer_link_not_the_row(): void
    {
        $customer = Customer::factory()->create();
        $licenseCustomer = LicenseCustomer::factory()->for($customer)->create();

        $customer->delete();

        $this->assertDatabaseHas('license_customers', [
            'id' => $licenseCustomer->id,
            'customer_id' => null,
        ]);
    }
}
