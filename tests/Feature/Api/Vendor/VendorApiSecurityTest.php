<?php

namespace Tests\Feature\Api\Vendor;

use Tests\Concerns\SignsVendorApiRequests;
use Tests\Concerns\UsesEphemeralVendorApiSigningKey;
use Tests\TestCase;

/**
 * APISEC-002 contract tests (WORK-INSTRUCTIONS §13 — "Vendor API tasks
 * additionally get contract tests: signature verify/reject, replay
 * rejection, domain/installation binding, rate-limit, idempotency,
 * offline-grace behaviour"). Domain/installation binding and offline-grace
 * arrive with INST-002/VC-003; covered here: signature verify/reject,
 * timestamp replay-window rejection, request-id dedupe (replay protection),
 * and per-route rate limiting. Response signing (APISEC-003) has its own
 * `VendorApiResponseSigningTest`.
 */
class VendorApiSecurityTest extends TestCase
{
    use SignsVendorApiRequests;
    use UsesEphemeralVendorApiSigningKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useEphemeralVendorApiSigningKey();
    }

    public function test_a_correctly_signed_request_is_accepted(): void
    {
        $this->postJson('/api/vendor/v1/ping', $this->signedVendorPayload())
            ->assertStatus(200)
            ->assertJson(['success' => true, 'code' => 'PING_OK']);
    }

    public function test_a_request_with_no_signature_field_fails_shape_validation_not_signature_verification(): void
    {
        $payload = $this->signedVendorPayload();
        unset($payload['signature']);

        // No signature to verify against at all — the security middleware
        // passes it through untouched and the normal envelope-shape
        // validation reports the missing field as VALIDATION_ERROR (422),
        // not SIGNATURE_ERROR.
        $this->postJson('/api/vendor/v1/ping', $payload)
            ->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_ERROR');
    }

    public function test_a_forged_signature_is_rejected(): void
    {
        $payload = $this->signedVendorPayload();
        $payload['signature'] = 'not-the-real-signature';

        $this->postJson('/api/vendor/v1/ping', $payload)
            ->assertStatus(401)
            ->assertJson(['success' => false, 'code' => 'SIGNATURE_ERROR']);
    }

    public function test_tampering_with_a_signed_field_after_signing_invalidates_the_signature(): void
    {
        $payload = $this->signedVendorPayload();
        $payload['client_version'] = '9.9.9';

        $this->postJson('/api/vendor/v1/ping', $payload)
            ->assertStatus(401)
            ->assertJsonPath('code', 'SIGNATURE_ERROR');
    }

    public function test_a_stale_timestamp_outside_the_replay_window_is_rejected(): void
    {
        $payload = $this->signedVendorPayload(['timestamp' => now()->subMinutes(20)->toIso8601String()]);

        $this->postJson('/api/vendor/v1/ping', $payload)
            ->assertStatus(401)
            ->assertJson(['success' => false, 'code' => 'REQUEST_EXPIRED']);
    }

    public function test_a_timestamp_within_the_replay_window_is_accepted(): void
    {
        $payload = $this->signedVendorPayload(['timestamp' => now()->subMinutes(2)->toIso8601String()]);

        $this->postJson('/api/vendor/v1/ping', $payload)->assertStatus(200);
    }

    public function test_replaying_the_same_request_id_is_rejected(): void
    {
        $payload = $this->signedVendorPayload(['request_id' => 'REQ-REPLAY-TEST']);

        $this->postJson('/api/vendor/v1/ping', $payload)->assertStatus(200);

        $this->postJson('/api/vendor/v1/ping', $payload)
            ->assertStatus(401)
            ->assertJson(['success' => false, 'code' => 'DUPLICATE_REQUEST']);
    }

    public function test_the_same_request_id_from_a_different_installation_is_not_treated_as_a_duplicate(): void
    {
        $requestId = 'REQ-SHARED-ID';

        $this->postJson('/api/vendor/v1/ping', $this->signedVendorPayload([
            'installation_id' => 'INS-A',
            'request_id' => $requestId,
        ]))->assertStatus(200);

        $this->postJson('/api/vendor/v1/ping', $this->signedVendorPayload([
            'installation_id' => 'INS-B',
            'request_id' => $requestId,
        ]))->assertStatus(200);
    }

    public function test_requests_beyond_the_configured_rate_limit_are_rejected(): void
    {
        config(['vendor_api.rate_limits.default' => ['max_attempts' => 2, 'decay_seconds' => 60]]);

        $this->postJson('/api/vendor/v1/ping', $this->signedVendorPayload())->assertStatus(200);
        $this->postJson('/api/vendor/v1/ping', $this->signedVendorPayload())->assertStatus(200);

        $this->postJson('/api/vendor/v1/ping', $this->signedVendorPayload())
            ->assertStatus(429)
            ->assertJson(['success' => false, 'code' => 'RATE_LIMIT_EXCEEDED'])
            ->assertJsonStructure(['data' => ['retry_after_seconds']]);
    }
}
