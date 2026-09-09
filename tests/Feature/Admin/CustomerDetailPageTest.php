<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\License;
use App\Models\LicenseCustomer;
use App\Models\LicensedApp;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDetailPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function viewer(): User
    {
        return User::factory()->nonAdmin()->asRole('support')->create();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $customer = Customer::factory()->create();

        $this->get(route('admin.customers.show', $customer))->assertRedirect(route('admin.login'));
    }

    public function test_a_user_without_customers_view_is_forbidden(): void
    {
        $user = User::factory()->nonAdmin()->asRole('qa')->create();
        $customer = Customer::factory()->create();

        $this->actingAs($user)->get(route('admin.customers.show', $customer))->assertForbidden();
    }

    public function test_detail_page_renders_the_information_tab_and_every_srs_section_tab(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Meridian Retail',
            'company_name' => 'Meridian Ltd',
            'email' => 'ops@meridian.test',
            'phone' => '555-2200',
            'notes' => 'Flagship pilot account',
        ]);

        $this->actingAs($this->viewer())->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('Meridian Retail')
            ->assertSee('ops@meridian.test')
            ->assertSee('555-2200')
            ->assertSee('Flagship pilot account')
            ->assertSee('Package Features')
            ->assertSee('Custom Development')
            ->assertSee('Software Version')
            ->assertSee('Audit History')
            ->assertSee('Phase 2 — Package Management (PKG-001)')
            ->assertSee('Phase 3 — Offer & Billing (INV-001)')
            ->assertSee('Phase 7 — Deployment (DEP-001)')
            ->assertSee('Phase 10 — Ownership (OWN-001)');
    }

    public function test_license_tab_lists_the_customers_licenses(): void
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

        $this->actingAs($this->viewer())->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee($license->license_key);
    }

    public function test_audit_tab_shows_the_customers_history(): void
    {
        $customer = Customer::factory()->create();
        $customer->update(['company_name' => 'Renamed Co']);

        $this->actingAs($this->viewer())->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('company_name');
    }
}
