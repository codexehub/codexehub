<?php

namespace Tests\Feature\Api\Vendor\License;

use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MakesTestLicenses;
use Tests\Concerns\SignsVendorApiRequests;
use Tests\Concerns\UsesEphemeralVendorApiSigningKey;
use Tests\TestCase;

/**
 * LIC-CP-002 — `POST /api/vendor/v1/licenses/status`. A pure read: unlike
 * `/verify`, must never update `last_verified_at`.
 */
class LicenseStatusTest extends TestCase
{
    use MakesTestLicenses;
    use RefreshDatabase;
    use SignsVendorApiRequests;
    use UsesEphemeralVendorApiSigningKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useEphemeralVendorApiSigningKey();
    }

    public function test_an_operational_license_reports_its_status(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);

        $this->postJson('/api/vendor/v1/licenses/status', $this->signedVendorPayload([
            'license_key' => $license->license_key,
        ]))->assertStatus(200)->assertJson([
            'success' => true,
            'code' => 'LICENSE_STATUS',
        ])->assertJsonPath('data.license_status', License::STATUS_ACTIVE);
    }

    public function test_status_does_not_update_last_verified_at(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);
        $this->assertNull($license->last_verified_at);

        $this->postJson('/api/vendor/v1/licenses/status', $this->signedVendorPayload([
            'license_key' => $license->license_key,
        ]))->assertStatus(200);

        $this->assertNull($license->fresh()->last_verified_at);
    }

    public function test_a_suspended_license_returns_a_business_error(): void
    {
        $license = $this->makeLicense(License::STATUS_SUSPENDED);

        $this->postJson('/api/vendor/v1/licenses/status', $this->signedVendorPayload([
            'license_key' => $license->license_key,
        ]))->assertStatus(403)->assertJson(['success' => false, 'code' => 'LICENSE_SUSPENDED']);
    }

    public function test_an_unknown_license_key_returns_not_found(): void
    {
        $this->postJson('/api/vendor/v1/licenses/status', $this->signedVendorPayload([
            'license_key' => 'DOES-NOT-EXIST',
        ]))->assertStatus(404)->assertJson(['success' => false, 'code' => 'NOT_FOUND']);
    }

    public function test_the_expiry_safety_net_still_applies_on_a_pure_status_read(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE, ['expires_at' => now()->subDay()]);

        $this->postJson('/api/vendor/v1/licenses/status', $this->signedVendorPayload([
            'license_key' => $license->license_key,
        ]))->assertStatus(403)->assertJson(['success' => false, 'code' => 'LICENSE_EXPIRED']);

        $this->assertSame(License::STATUS_EXPIRED, $license->fresh()->status);
    }
}
