<?php

namespace Tests\Feature\Api\Vendor\Installation;

use App\Models\Installation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SignsVendorApiRequests;
use Tests\Concerns\UsesEphemeralVendorApiSigningKey;
use Tests\TestCase;

/**
 * INST-002 — `POST /api/vendor/v1/installations/heartbeat`.
 */
class HeartbeatInstallationTest extends TestCase
{
    use RefreshDatabase;
    use SignsVendorApiRequests;
    use UsesEphemeralVendorApiSigningKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useEphemeralVendorApiSigningKey();
    }

    public function test_a_heartbeat_updates_last_heartbeat_at_and_reported_version(): void
    {
        $installation = Installation::factory()->create([
            'installation_id' => 'INS-HB000001',
            'software_version' => '1.0.0',
            'last_heartbeat_at' => null,
        ]);

        $response = $this->postJson('/api/vendor/v1/installations/heartbeat', $this->signedVendorPayload([
            'installation_id' => 'INS-HB000001',
            'software_version' => '1.2.0',
        ]));

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'code' => 'INSTALLATION_HEARTBEAT_RECEIVED',
        ])->assertJsonPath('data.software_version', '1.2.0');

        $fresh = $installation->fresh();
        $this->assertNotNull($fresh->last_heartbeat_at);
        $this->assertSame('1.2.0', $fresh->software_version);
    }

    public function test_a_heartbeat_does_not_revalidate_a_pending_installation(): void
    {
        Installation::factory()->pending()->create(['installation_id' => 'INS-HB000002']);

        $this->postJson('/api/vendor/v1/installations/heartbeat', $this->signedVendorPayload([
            'installation_id' => 'INS-HB000002',
        ]))->assertStatus(200)->assertJsonPath('data.status', Installation::STATUS_PENDING);

        $this->assertSame(Installation::STATUS_PENDING, Installation::where('installation_id', 'INS-HB000002')->first()->status);
    }

    public function test_a_heartbeat_for_a_suspended_installation_returns_the_business_error(): void
    {
        Installation::factory()->suspended()->create(['installation_id' => 'INS-HB000003']);

        $this->postJson('/api/vendor/v1/installations/heartbeat', $this->signedVendorPayload([
            'installation_id' => 'INS-HB000003',
        ]))->assertStatus(403)->assertJson(['success' => false, 'code' => 'INSTALLATION_SUSPENDED']);
    }

    public function test_an_unregistered_installation_returns_not_found(): void
    {
        $this->postJson('/api/vendor/v1/installations/heartbeat', $this->signedVendorPayload([
            'installation_id' => 'INS-NEVER0002',
        ]))->assertStatus(404)->assertJson(['success' => false, 'code' => 'NOT_FOUND']);
    }
}
