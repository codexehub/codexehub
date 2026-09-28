<?php

namespace Tests\Feature\Api\Vendor\License;

use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MakesTestLicenses;
use Tests\Concerns\SignsVendorApiRequests;
use Tests\Concerns\UsesEphemeralVendorApiSigningKey;
use Tests\TestCase;

/**
 * LIC-CP-002 — `POST /api/vendor/v1/licenses/verify`.
 */
class VerifyLicenseTest extends TestCase
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

    /**
     * @dataProvider operationalStatusProvider
     */
    public function test_an_operational_license_verifies_successfully(string $status): void
    {
        $license = $this->makeLicense($status);

        $response = $this->postJson('/api/vendor/v1/licenses/verify', $this->signedVendorPayload([
            'license_key' => $license->license_key,
        ]));

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'code' => 'LICENSE_VERIFIED',
        ])->assertJsonPath('data.license_status', $status)
            ->assertJsonPath('data.license_key', $license->license_key);

        $this->assertNotNull($license->fresh()->last_verified_at);
    }

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
    public function test_a_non_operational_license_returns_the_correct_business_error(string $status, string $expectedCode): void
    {
        $license = $this->makeLicense($status);

        $response = $this->postJson('/api/vendor/v1/licenses/verify', $this->signedVendorPayload([
            'license_key' => $license->license_key,
        ]));

        $response->assertStatus(403)->assertJson([
            'success' => false,
            'code' => $expectedCode,
        ])->assertJsonMissingPath('signature');

        $this->assertNull($license->fresh()->last_verified_at);
    }

    public static function lockedStatusProvider(): array
    {
        return [
            [License::STATUS_PENDING, 'PAYMENT_DUE'],
            [License::STATUS_SUSPENDED, 'LICENSE_SUSPENDED'],
            [License::STATUS_REVOKED, 'LICENSE_REVOKED'],
        ];
    }

    public function test_an_unknown_license_key_returns_not_found(): void
    {
        $this->postJson('/api/vendor/v1/licenses/verify', $this->signedVendorPayload([
            'license_key' => 'DOES-NOT-EXIST',
        ]))->assertStatus(404)->assertJson(['success' => false, 'code' => 'NOT_FOUND']);
    }

    public function test_an_active_license_past_its_expiry_flips_to_expired_and_is_rejected(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE, ['expires_at' => now()->subDay()]);

        $this->postJson('/api/vendor/v1/licenses/verify', $this->signedVendorPayload([
            'license_key' => $license->license_key,
        ]))->assertStatus(403)->assertJson(['success' => false, 'code' => 'LICENSE_EXPIRED']);

        $this->assertSame(License::STATUS_EXPIRED, $license->fresh()->status);
    }

    public function test_an_unsigned_request_is_rejected_by_the_security_middleware(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);

        $payload = $this->signedVendorPayload(['license_key' => $license->license_key]);
        $payload['signature'] = 'not-the-real-signature';

        $this->postJson('/api/vendor/v1/licenses/verify', $payload)
            ->assertStatus(401)
            ->assertJson(['success' => false, 'code' => 'SIGNATURE_ERROR']);
    }
}
