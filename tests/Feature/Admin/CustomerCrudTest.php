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

class CustomerCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function manager(): User
    {
        return User::factory()->nonAdmin()->asRole('sales-manager')->create();
    }

    private function viewer(): User
    {
        return User::factory()->nonAdmin()->asRole('support')->create();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.customers.index'))->assertRedirect(route('admin.login'));
    }

    public function test_a_user_without_customers_view_is_forbidden(): void
    {
        $user = User::factory()->nonAdmin()->asRole('qa')->create();

        $this->actingAs($user)->get(route('admin.customers.index'))->assertForbidden();
    }

    public function test_viewer_sees_the_list_but_cannot_create(): void
    {
        Customer::factory()->create(['name' => 'Globex']);

        $this->actingAs($this->viewer())->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('Globex');

        $this->actingAs($this->viewer())->post(route('admin.customers.store'), [
            'name' => 'Nope', 'email' => 'nope@x.test', 'status' => 'prospect',
        ])->assertForbidden();

        $this->assertDatabaseMissing('customers', ['email' => 'nope@x.test']);
    }

    public function test_create_and_edit_forms_render(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->manager())->get(route('admin.customers.create'))->assertOk()->assertSee('Add Customer');
        $this->actingAs($this->manager())->get(route('admin.customers.edit', $customer))->assertOk()->assertSee($customer->name);
    }

    public function test_manager_creates_a_customer(): void
    {
        $this->actingAs($this->manager())->post(route('admin.customers.store'), [
            'name' => 'Jane Doe',
            'company_name' => 'Acme Inc',
            'email' => 'jane@acme.test',
            'phone' => '555-1000',
            'status' => 'active',
            'notes' => 'Priority account',
        ])->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseHas('customers', [
            'email' => 'jane@acme.test', 'company_name' => 'Acme Inc', 'status' => 'active',
        ]);
    }

    public function test_email_must_be_unique(): void
    {
        Customer::factory()->create(['email' => 'taken@acme.test']);

        $this->actingAs($this->manager())->post(route('admin.customers.store'), [
            'name' => 'Dup', 'email' => 'taken@acme.test', 'status' => 'prospect',
        ])->assertSessionHasErrors('email');
    }

    public function test_manager_updates_a_customer(): void
    {
        $customer = Customer::factory()->create(['status' => 'prospect']);

        $this->actingAs($this->manager())->put(route('admin.customers.update', $customer), [
            'name' => $customer->name,
            'email' => $customer->email,
            'company_name' => 'Renamed Ltd',
            'status' => 'active',
        ])->assertRedirect(route('admin.customers.index'));

        $this->assertSame('active', $customer->fresh()->status);
        $this->assertSame('Renamed Ltd', $customer->fresh()->company_name);
    }

    public function test_status_transition_endpoint(): void
    {
        $customer = Customer::factory()->create(['status' => 'active']);

        $this->actingAs($this->manager())
            ->patch(route('admin.customers.status', $customer), ['status' => 'suspended'])
            ->assertRedirect();

        $this->assertSame('suspended', $customer->fresh()->status);

        $this->actingAs($this->manager())
            ->patch(route('admin.customers.status', $customer), ['status' => 'nonsense'])
            ->assertSessionHasErrors('status');
    }

    public function test_search_and_status_filter(): void
    {
        Customer::factory()->create(['name' => 'Alpha Corp', 'status' => 'active']);
        Customer::factory()->create(['name' => 'Beta Corp', 'status' => 'inactive']);

        $this->actingAs($this->viewer())->get(route('admin.customers.index', ['search' => 'Alpha']))
            ->assertSee('Alpha Corp')->assertDontSee('Beta Corp');

        $this->actingAs($this->viewer())->get(route('admin.customers.index', ['status' => 'inactive']))
            ->assertSee('Beta Corp')->assertDontSee('Alpha Corp');
    }

    public function test_a_customer_with_licenses_cannot_be_deleted(): void
    {
        $customer = Customer::factory()->create();
        $licenseCustomer = LicenseCustomer::factory()->for($customer)->create();

        $app = LicensedApp::create(['name' => 'Ecom', 'slug' => 'ecom', 'is_active' => true]);
        $package = $app->packages()->create([
            'name' => 'Basic', 'price' => 10, 'duration_days' => 365, 'max_activations' => 1, 'features' => [], 'is_active' => true,
        ]);
        License::create([
            'license_customer_id' => $licenseCustomer->id,
            'licensed_app_id' => $app->id,
            'licensed_app_package_id' => $package->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->manager())->delete(route('admin.customers.destroy', $customer))
            ->assertSessionHasErrors('customer');

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_a_customer_without_licenses_is_deleted(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->manager())->delete(route('admin.customers.destroy', $customer))
            ->assertRedirect();

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
