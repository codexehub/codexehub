<?php

namespace Tests\Feature\Api\Vendor\Installation;

use App\Models\Installation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SignsVendorApiRequests;
use Tests\Concerns\UsesEphemeralVendorApiSigningKey;
use Tests\TestCase;

/**
 * INST-002 — `GET /api/vendor/v1/installations/{id}`. The envelope travels as
 * query parameters on a GET request — same signature algorithm, same
 * middleware, just a different HTTP method (see ShowInstallationRequest).
 */
class ShowInstallationTest extends TestCase
{
    use RefreshDatabase;
    use SignsVendorApiRequests;
    use UsesEphemeralVendorApiSigningKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useEphemeralVendorApiSigningKey();
    }

    private function signedGetUrl(string $installationId, array $overrides = []): string
    {
        $payload = $this->signedVendorPayload(array_merge(['installation_id' => $installationId], $overrides));

        return '/api/vendor/v1/installations/'.$installationId.'?'.http_build_query($payload);
    }

    public function test_an_installation_can_look_up_its_own_record(): void
    {
        Installation::factory()->create(['installation_id' => 'INS-SHOW0001', 'domain' => 'shop.example.com']);

        $response = $this->getJson($this->signedGetUrl('INS-SHOW0001'));

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'code' => 'INSTALLATION_STATUS',
        ])->assertJsonPath('data.installation_id', 'INS-SHOW0001')
            ->assertJsonPath('data.domain', 'shop.example.com');
    }

    public function test_looking_up_a_different_installation_id_than_the_caller_claims_returns_not_found(): void
    {
        Installation::factory()->create(['installation_id' => 'INS-SHOW0002']);

        // URL path targets a different installation than the signed
        // envelope's own installation_id claims to be.
        $payload = $this->signedVendorPayload(['installation_id' => 'INS-SHOW0003']);
        $url = '/api/vendor/v1/installations/INS-SHOW0002?'.http_build_query($payload);

        $this->getJson($url)->assertStatus(404)->assertJson(['success' => false, 'code' => 'NOT_FOUND']);
    }

    public function test_an_unregistered_installation_returns_not_found(): void
    {
        $this->getJson($this->signedGetUrl('INS-NEVER0003'))
            ->assertStatus(404)->assertJson(['success' => false, 'code' => 'NOT_FOUND']);
    }
}
