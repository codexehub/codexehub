<?php

namespace Tests\Feature\Api\Vendor;

use App\Support\VendorApi\VendorApiResponseSigner;
use Tests\Concerns\SignsVendorApiRequests;
use Tests\Concerns\UsesEphemeralVendorApiSigningKey;
use Tests\TestCase;

/**
 * APISEC-001 — `/ping` is the diagnostic route proving the envelope +
 * standard success response work end-to-end. Not an SRS endpoint itself.
 * APISEC-003 — the response is now really signed; verified here too.
 */
class PingEndpointTest extends TestCase
{
    use SignsVendorApiRequests;
    use UsesEphemeralVendorApiSigningKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useEphemeralVendorApiSigningKey();
    }

    public function test_ping_returns_the_standard_success_envelope(): void
    {
        $payload = $this->signedVendorPayload(['request_id' => 'REQ-ABC123']);

        $response = $this->postJson('/api/vendor/v1/ping', $payload);

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'request_id' => 'REQ-ABC123',
            'code' => 'PING_OK',
        ])->assertJsonStructure([
            'success', 'request_id', 'code', 'message', 'data' => ['server_time'], 'signature',
        ]);

        $body = $response->json();
        $this->assertIsString($body['signature']);
        $this->assertNotSame('', $body['signature']);

        $unsigned = $body;
        unset($unsigned['signature']);
        $this->assertTrue(VendorApiResponseSigner::verify($unsigned, $body['signature']));
    }
}
