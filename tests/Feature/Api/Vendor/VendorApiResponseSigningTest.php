<?php

namespace Tests\Feature\Api\Vendor;

use App\Support\VendorApi\VendorApiResponseSigner;
use Tests\Concerns\SignsVendorApiRequests;
use Tests\Concerns\UsesEphemeralVendorApiSigningKey;
use Tests\TestCase;

/**
 * APISEC-003 contract tests — every real success response is genuinely
 * signed (not the APISEC-001 skeleton's always-`null` placeholder), the
 * signature is bound to that response's own content, and error responses
 * are unaffected (still carry no `signature` key at all, per APISEC-001's
 * recorded asymmetry decision).
 */
class VendorApiResponseSigningTest extends TestCase
{
    use SignsVendorApiRequests;
    use UsesEphemeralVendorApiSigningKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useEphemeralVendorApiSigningKey();
    }

    public function test_a_success_response_carries_a_verifiable_signature(): void
    {
        $body = $this->postJson('/api/vendor/v1/ping', $this->signedVendorPayload())->json();

        $unsigned = $body;
        unset($unsigned['signature']);

        $this->assertTrue(VendorApiResponseSigner::verify($unsigned, $body['signature']));
    }

    public function test_two_different_requests_get_different_signatures(): void
    {
        $first = $this->postJson('/api/vendor/v1/ping', $this->signedVendorPayload(['request_id' => 'REQ-ONE']))->json('signature');
        $second = $this->postJson('/api/vendor/v1/ping', $this->signedVendorPayload(['request_id' => 'REQ-TWO']))->json('signature');

        $this->assertNotSame($first, $second);
    }

    public function test_a_response_signature_does_not_verify_against_a_tampered_copy_of_the_body(): void
    {
        $body = $this->postJson('/api/vendor/v1/ping', $this->signedVendorPayload())->json();

        $tampered = $body;
        unset($tampered['signature']);
        $tampered['message'] = 'Everything is fine, trust me.';

        $this->assertFalse(VendorApiResponseSigner::verify($tampered, $body['signature']));
    }

    public function test_error_responses_still_carry_no_signature_key(): void
    {
        $this->postJson('/api/vendor/v1/ping', [])
            ->assertStatus(422)
            ->assertJsonMissingPath('signature');
    }
}
