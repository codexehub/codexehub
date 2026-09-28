<?php

namespace Tests\Feature\Api;

use App\Models\License;
use App\Models\LicenseCustomer;
use App\Models\LicensedApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * LIC-CP-001 — the legacy unsigned `/api/v1/license/verify` pair must keep
 * working correctly across the widened SRS §39 status set: PAYMENT_DUE /
 * GRACE_PERIOD / REACTIVATED / COMPLETED stay usable (SRS §45/§46 — only a
 * genuinely locked state blocks a check-in), and the response now echoes the
 * real status instead of a hardcoded "active".
 */
class LicenseApiLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function makeLicense(string $status, array $overrides = []): License
    {
        $app = LicensedApp::create(['name' => 'Ecom', 'slug' => 'ecom-app', 'is_active' => true]);
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

    private function verify(License $license): TestResponse
    {
        return $this->postJson('/api/v1/license/verify', [
            'license_key' => $license->license_key,
            'product_slug' => 'ecom-app',
            'domain' => 'customer.example',
            'fingerprint' => 'fp-'.$license->id,
        ]);
    }

    /**
     * @dataProvider operationalStatusProvider
     */
    public function test_operational_statuses_return_a_token_and_echo_the_real_status(string $status): void
    {
        $license = $this->makeLicense($status);

        $this->verify($license)
            ->assertOk()
            ->assertJson(['status' => $status])
            ->assertJsonStructure(['status', 'token', 'package', 'expires_at']);
    }

    /**
     * @return list<array{0: string}>
     */
    public static function operationalStatusProvider(): array
    {
        return [
            [License::STATUS_ACTIVE],
            [License::STATUS_PAYMENT_DUE],
            [License::STATUS_GRACE_PERIOD],
            [License::STATUS_REACTIVATED],
            [License::STATUS_COMPLETED],
        ];
    }

    /**
     * @dataProvider lockedStatusProvider
     */
    public function test_locked_statuses_are_rejected_with_403(string $status): void
    {
        $license = $this->makeLicense($status);

        $this->verify($license)
            ->assertStatus(403)
            ->assertJson(['status' => $status]);
    }

    /**
     * @return list<array{0: string}>
     */
    public static function lockedStatusProvider(): array
    {
        return [
            [License::STATUS_PENDING],
            [License::STATUS_SUSPENDED],
            [License::STATUS_REVOKED],
        ];
    }

    public function test_an_active_license_past_its_expiry_flips_to_expired_and_is_rejected(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE, ['expires_at' => now()->subDay()]);

        $this->verify($license)
            ->assertStatus(403)
            ->assertJson(['status' => License::STATUS_EXPIRED]);

        $this->assertSame(License::STATUS_EXPIRED, $license->fresh()->status);
    }
}
