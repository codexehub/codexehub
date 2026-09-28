<?php

namespace Tests\Feature\Admin;

use App\Models\License;
use App\Models\LicenseCustomer;
use App\Models\LicensedApp;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * LIC-CP-001 — the admin suspend/reactivate/revoke actions now route through
 * `LicenseLifecycle`, and a generic status endpoint reaches the SRS §39
 * states (PAYMENT_DUE / GRACE_PERIOD / REACTIVATED / COMPLETED) that have no
 * dedicated button yet (LIC-001 wires the automatic job later).
 */
class LicenseLifecycleAdminTest extends TestCase
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

    private function makeLicense(string $status = License::STATUS_ACTIVE, array $overrides = []): License
    {
        $app = LicensedApp::create(['name' => 'Ecom', 'slug' => 'ecom', 'is_active' => true]);
        $package = $app->packages()->create([
            'name' => 'Basic', 'price' => 10, 'duration_days' => 365, 'max_activations' => 1, 'features' => [], 'is_active' => true,
        ]);
        $customer = LicenseCustomer::factory()->create();

        return License::create(array_merge([
            'license_customer_id' => $customer->id,
            'licensed_app_id' => $app->id,
            'licensed_app_package_id' => $package->id,
            'status' => $status,
        ], $overrides));
    }

    public function test_suspend_moves_an_active_license_to_suspended(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);

        $this->actingAs($this->admin())
            ->post(route('admin.licensing.licenses.suspend', $license))
            ->assertRedirect();

        $this->assertSame(License::STATUS_SUSPENDED, $license->fresh()->status);
    }

    public function test_reactivate_still_requires_a_future_expiry(): void
    {
        $license = $this->makeLicense(License::STATUS_SUSPENDED, ['expires_at' => now()->subDay()]);

        $this->actingAs($this->admin())
            ->post(route('admin.licensing.licenses.reactivate', $license))
            ->assertSessionHasErrors('status');

        $this->assertSame(License::STATUS_SUSPENDED, $license->fresh()->status);
    }

    public function test_reactivate_moves_a_suspended_license_with_a_future_expiry_to_active(): void
    {
        $license = $this->makeLicense(License::STATUS_SUSPENDED, ['expires_at' => now()->addMonth()]);

        $this->actingAs($this->admin())
            ->post(route('admin.licensing.licenses.reactivate', $license))
            ->assertRedirect();

        $this->assertSame(License::STATUS_ACTIVE, $license->fresh()->status);
    }

    public function test_revoke_rejects_when_the_transition_is_invalid(): void
    {
        $license = $this->makeLicense(License::STATUS_REVOKED);

        $this->actingAs($this->admin())
            ->post(route('admin.licensing.licenses.revoke', $license))
            ->assertSessionHasErrors('status');
    }

    public function test_the_generic_status_endpoint_reaches_payment_due_and_grace_period(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.licensing.licenses.status', $license), ['status' => License::STATUS_PAYMENT_DUE])
            ->assertRedirect();
        $this->assertSame(License::STATUS_PAYMENT_DUE, $license->fresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.licensing.licenses.status', $license), ['status' => License::STATUS_GRACE_PERIOD])
            ->assertRedirect();
        $this->assertSame(License::STATUS_GRACE_PERIOD, $license->fresh()->status);
    }

    public function test_the_generic_status_endpoint_rejects_an_illegal_jump(): void
    {
        $license = $this->makeLicense(License::STATUS_PENDING);

        $this->actingAs($this->admin())
            ->patch(route('admin.licensing.licenses.status', $license), ['status' => License::STATUS_GRACE_PERIOD])
            ->assertSessionHasErrors('status');

        $this->assertSame(License::STATUS_PENDING, $license->fresh()->status);
    }

    public function test_the_generic_status_endpoint_rejects_an_unknown_status_value(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);

        $this->actingAs($this->admin())
            ->patch(route('admin.licensing.licenses.status', $license), ['status' => 'not_a_real_status'])
            ->assertSessionHasErrors('status');
    }

    public function test_the_show_page_renders_the_next_valid_statuses(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);

        $this->actingAs($this->admin())
            ->get(route('admin.licensing.licenses.show', $license))
            ->assertOk()
            ->assertSee('Move to Status')
            ->assertSee('Payment Due');
    }
}
