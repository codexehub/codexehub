<?php

namespace Tests\Feature\Api\Vendor\Installation;

use App\Models\Installation;
use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MakesTestLicenses;
use Tests\Concerns\SignsVendorApiRequests;
use Tests\Concerns\UsesEphemeralVendorApiSigningKey;
use Tests\TestCase;

/**
 * INST-002 — `POST /api/vendor/v1/installations/register`.
 */
class RegisterInstallationTest extends TestCase
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

    public function test_a_first_time_registration_creates_an_active_installation(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);

        $response = $this->postJson('/api/vendor/v1/installations/register', $this->signedVendorPayload([
            'installation_id' => 'INS-REG00001',
            'license_key' => $license->license_key,
            'domain' => 'shop.example.com',
        ]));

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'code' => 'INSTALLATION_REGISTERED',
        ])->assertJsonPath('data.status', Installation::STATUS_ACTIVE)
            ->assertJsonPath('data.domain', 'shop.example.com')
            ->assertJsonPath('data.revalidation_required', false);

        $this->assertDatabaseHas('installations', [
            'installation_id' => 'INS-REG00001',
            'domain' => 'shop.example.com',
            'status' => Installation::STATUS_ACTIVE,
        ]);
        $this->assertSame('INS-REG00001', $license->fresh()->installation_id);
    }

    public function test_re_registering_with_a_changed_domain_returns_pending_revalidation(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);

        $this->postJson('/api/vendor/v1/installations/register', $this->signedVendorPayload([
            'installation_id' => 'INS-REG00002',
            'license_key' => $license->license_key,
            'domain' => 'old.example.com',
        ]))->assertStatus(200);

        $response = $this->postJson('/api/vendor/v1/installations/register', $this->signedVendorPayload([
            'installation_id' => 'INS-REG00002',
            'license_key' => $license->license_key,
            'domain' => 'new.example.com',
        ]));

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'code' => 'INSTALLATION_PENDING_REVALIDATION',
        ])->assertJsonPath('data.status', Installation::STATUS_PENDING)
            ->assertJsonPath('data.revalidation_required', true);

        $this->assertDatabaseHas('installations', [
            'installation_id' => 'INS-REG00002',
            'domain' => 'new.example.com',
            'status' => Installation::STATUS_PENDING,
        ]);
    }

    public function test_an_unknown_license_key_returns_not_found(): void
    {
        $this->postJson('/api/vendor/v1/installations/register', $this->signedVendorPayload([
            'installation_id' => 'INS-REG00003',
            'license_key' => 'DOES-NOT-EXIST',
            'domain' => 'shop.example.com',
        ]))->assertStatus(404)->assertJson(['success' => false, 'code' => 'NOT_FOUND']);
    }

    public function test_a_non_operational_license_returns_the_business_error(): void
    {
        $license = $this->makeLicense(License::STATUS_SUSPENDED);

        $this->postJson('/api/vendor/v1/installations/register', $this->signedVendorPayload([
            'installation_id' => 'INS-REG00004',
            'license_key' => $license->license_key,
            'domain' => 'shop.example.com',
        ]))->assertStatus(403)->assertJson(['success' => false, 'code' => 'LICENSE_SUSPENDED']);

        $this->assertDatabaseMissing('installations', ['installation_id' => 'INS-REG00004']);
    }

    public function test_a_license_already_bound_to_a_different_installation_is_rejected(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE, ['installation_id' => 'INS-BOUND001']);

        $this->postJson('/api/vendor/v1/installations/register', $this->signedVendorPayload([
            'installation_id' => 'INS-ANOTHER01',
            'license_key' => $license->license_key,
            'domain' => 'shop.example.com',
        ]))->assertStatus(409)->assertJson(['success' => false, 'code' => 'INSTALLATION_ALREADY_BOUND']);
    }

    public function test_the_same_installation_id_claimed_by_a_different_customers_license_is_rejected(): void
    {
        $licenseA = $this->makeLicense(License::STATUS_ACTIVE);
        $this->postJson('/api/vendor/v1/installations/register', $this->signedVendorPayload([
            'installation_id' => 'INS-SHARED001',
            'license_key' => $licenseA->license_key,
            'domain' => 'shop.example.com',
        ]))->assertStatus(200);

        $licenseB = $this->makeLicense(License::STATUS_ACTIVE);

        $this->postJson('/api/vendor/v1/installations/register', $this->signedVendorPayload([
            'installation_id' => 'INS-SHARED001',
            'license_key' => $licenseB->license_key,
            'domain' => 'shop.example.com',
        ]))->assertStatus(409)->assertJson(['success' => false, 'code' => 'INSTALLATION_ALREADY_BOUND']);
    }

    public function test_domain_is_required(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);

        $this->postJson('/api/vendor/v1/installations/register', $this->signedVendorPayload([
            'installation_id' => 'INS-REG00005',
            'license_key' => $license->license_key,
        ]))->assertStatus(422)->assertJson(['success' => false, 'code' => 'VALIDATION_ERROR']);
    }

    public function test_an_unsigned_request_is_rejected_by_the_security_middleware(): void
    {
        $license = $this->makeLicense(License::STATUS_ACTIVE);

        $payload = $this->signedVendorPayload([
            'installation_id' => 'INS-REG00006',
            'license_key' => $license->license_key,
            'domain' => 'shop.example.com',
        ]);
        $payload['signature'] = 'not-the-real-signature';

        $this->postJson('/api/vendor/v1/installations/register', $payload)
            ->assertStatus(401)
            ->assertJson(['success' => false, 'code' => 'SIGNATURE_ERROR']);
    }
}
