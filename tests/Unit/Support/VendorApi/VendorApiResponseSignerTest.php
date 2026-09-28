<?php

namespace Tests\Unit\Support\VendorApi;

use App\Support\VendorApi\VendorApiResponseSigner;
use Tests\Concerns\UsesEphemeralVendorApiSigningKey;
use Tests\TestCase;

class VendorApiResponseSignerTest extends TestCase
{
    use UsesEphemeralVendorApiSigningKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useEphemeralVendorApiSigningKey();
    }

    public function test_sign_and_verify_round_trip(): void
    {
        $payload = ['success' => true, 'code' => 'PING_OK', 'data' => ['a' => 1]];
        $signature = VendorApiResponseSigner::sign($payload);

        $this->assertTrue(VendorApiResponseSigner::verify($payload, $signature));
    }

    public function test_signature_carries_the_active_key_id_as_a_prefix(): void
    {
        $signature = VendorApiResponseSigner::sign(['code' => 'PING_OK']);

        $this->assertStringStartsWith('test:', $signature);
    }

    public function test_verify_fails_when_the_payload_is_tampered_with(): void
    {
        $payload = ['code' => 'PING_OK', 'data' => ['amount' => 100]];
        $signature = VendorApiResponseSigner::sign($payload);

        $tampered = $payload;
        $tampered['data']['amount'] = 999;

        $this->assertFalse(VendorApiResponseSigner::verify($tampered, $signature));
    }

    public function test_verify_fails_for_an_unknown_key_id(): void
    {
        $this->assertFalse(VendorApiResponseSigner::verify(['code' => 'PING_OK'], 'no-such-key:'.base64_encode('garbage')));
    }

    public function test_verify_fails_for_a_malformed_signature_string(): void
    {
        $this->assertFalse(VendorApiResponseSigner::verify(['code' => 'PING_OK'], 'not-even-the-right-shape'));
    }

    public function test_a_signature_from_one_key_does_not_verify_against_a_different_keys_public_key(): void
    {
        $payload = ['code' => 'PING_OK'];
        $signature = VendorApiResponseSigner::sign($payload); // signed under 'test'

        // Register a second, genuinely different keypair as 'other' without
        // touching 'test' — a signature produced under 'test' must not
        // verify if it's re-labelled as coming from 'other'.
        config(['vendor_api.response_signing.keys.other.public_key_path' => base_path('tests/fixtures/vendor-api/other-public.pem')]);

        $relabelled = 'other:'.substr($signature, strpos($signature, ':') + 1);

        $this->assertFalse(VendorApiResponseSigner::verify($payload, $relabelled));
    }
}
