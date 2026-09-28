<?php

namespace Tests\Feature\Api\Vendor\Installation;

use App\Models\Installation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SignsVendorApiRequests;
use Tests\Concerns\UsesEphemeralVendorApiSigningKey;
use Tests\TestCase;

/**
 * INST-002 — `POST /api/vendor/v1/installations/verify`.
 */
class VerifyInstallationTest extends TestCase
{
    use RefreshDatabase;
    use SignsVendorApiRequests;
    use UsesEphemeralVendorApiSigningKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useEphemeralVendorApiSigningKey();
    }

    public function test_verifying_a_pending_installation_activates_it(): void
    {
        $installation = Installation::factory()->pending()->create(['installation_id' => 'INS-VER00001']);

        $response = $this->postJson('/api/vendor/v1/installations/verify', $this->signedVendorPayload([
            'installation_id' => 'INS-VER00001',
        ]));

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'code' => 'INSTALLATION_VERIFIED',
        ])->assertJsonPath('data.status', Installation::STATUS_ACTIVE);

        $fresh = $installation->fresh();
        $this->assertSame(Installation::STATUS_ACTIVE, $fresh->status);
        $this->assertNotNull($fresh->last_verified_at);
    }

    public function test_verifying_an_already_active_installation_just_refreshes_it(): void
    {
        Installation::factory()->create(['installation_id' => 'INS-VER00002', 'status' => Installation::STATUS_ACTIVE]);

        $this->postJson('/api/vendor/v1/installations/verify', $this->signedVendorPayload([
            'installation_id' => 'INS-VER00002',
        ]))->assertStatus(200)->assertJson([
            'success' => true,
            'code' => 'INSTALLATION_VERIFIED',
        ])->assertJsonPath('data.status', Installation::STATUS_ACTIVE);
    }

    public function test_verifying_a_suspended_installation_returns_the_business_error(): void
    {
        Installation::factory()->suspended()->create(['installation_id' => 'INS-VER00003']);

        $this->postJson('/api/vendor/v1/installations/verify', $this->signedVendorPayload([
            'installation_id' => 'INS-VER00003',
        ]))->assertStatus(403)->assertJson(['success' => false, 'code' => 'INSTALLATION_SUSPENDED']);
    }

    public function test_verifying_an_unregistered_installation_returns_not_found(): void
    {
        $this->postJson('/api/vendor/v1/installations/verify', $this->signedVendorPayload([
            'installation_id' => 'INS-NEVER0001',
        ]))->assertStatus(404)->assertJson(['success' => false, 'code' => 'NOT_FOUND']);
    }
}
