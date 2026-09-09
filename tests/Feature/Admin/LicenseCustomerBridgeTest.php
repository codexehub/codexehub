<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\License;
use App\Models\LicenseCustomer;
use App\Models\LicensedApp;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The license admin form now speaks `customers` (CUST-001/002); the controller
 * bridges to the released `licenses.license_customer_id` FK via a per-customer
 * `LicenseCustomer` link row.
 */
class LicenseCustomerBridgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->nonAdmin()->asRole(Role::SUPER_ADMIN)->create();
    }

    private function seedApp(): array
    {
        $app = LicensedApp::create(['name' => 'Ecom', 'slug' => 'ecom', 'is_active' => true]);
        $package = $app->packages()->create([
            'name' => 'Basic', 'price' => 10, 'duration_days' => 365, 'max_activations' => 1, 'features' => [], 'is_active' => true,
        ]);

        return [$app, $package];
    }

    public function test_creating_a_license_for_a_customer_creates_one_link_row(): void
    {
        $customer = Customer::factory()->create();
        [$app, $package] = $this->seedApp();

        $this->actingAs($this->admin())->post(route('admin.licensing.licenses.store'), [
            'customer_id' => $customer->id,
            'licensed_app_id' => $app->id,
            'licensed_app_package_id' => $package->id,
        ])->assertRedirect();

        $link = LicenseCustomer::where('customer_id', $customer->id)->sole();
        $this->assertSame($customer->email, $link->email);
        $this->assertDatabaseHas('licenses', [
            'license_customer_id' => $link->id,
            'status' => 'pending',
        ]);

        // A second license for the same customer reuses the link row.
        $this->actingAs($this->admin())->post(route('admin.licensing.licenses.store'), [
            'customer_id' => $customer->id,
            'licensed_app_id' => $app->id,
            'licensed_app_package_id' => $package->id,
        ])->assertRedirect();

        $this->assertSame(1, LicenseCustomer::where('customer_id', $customer->id)->count());
        $this->assertSame(2, License::count());
    }

    public function test_the_license_form_lists_canonical_customers(): void
    {
        $customer = Customer::factory()->create(['name' => 'Umbrella Corp']);
        $this->seedApp();

        $this->actingAs($this->admin())->get(route('admin.licensing.licenses.create'))
            ->assertOk()
            ->assertSee('Umbrella Corp')
            ->assertSee('name="customer_id"', false);
    }
}
