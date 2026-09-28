<?php

namespace Tests\Feature\Api\Vendor;

use Tests\Concerns\SignsVendorApiRequests;
use Tests\Concerns\UsesEphemeralVendorApiSigningKey;
use Tests\TestCase;

/**
 * APISEC-001 — envelope validation renders the SRS §71 standard error
 * envelope (never Laravel's default {message, errors} shape). Payloads here
 * are correctly signed (APISEC-002's `VerifyVendorApiSignature` middleware
 * runs first) so these tests isolate FormRequest-level shape validation;
 * `VendorApiSecurityTest` covers the security middleware itself.
 */
class VendorApiEnvelopeTest extends TestCase
{
    use SignsVendorApiRequests;
    use UsesEphemeralVendorApiSigningKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useEphemeralVendorApiSigningKey();
    }

    public function test_missing_envelope_fields_return_standard_error_envelope(): void
    {
        $response = $this->postJson('/api/vendor/v1/ping', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'request_id' => null,
                'code' => 'VALIDATION_ERROR',
            ])
            ->assertJsonStructure(['success', 'request_id', 'code', 'message', 'data' => ['errors']])
            ->assertJsonMissingPath('signature');

        foreach (['installation_id', 'request_id', 'timestamp', 'client_version', 'signature'] as $field) {
            $response->assertJsonPath("data.errors.{$field}.0", fn ($message) => is_string($message));
        }
    }

    public function test_request_id_is_echoed_back_on_a_validation_error_when_present(): void
    {
        $payload = $this->signedVendorPayload();
        unset($payload['signature']);

        $response = $this->postJson('/api/vendor/v1/ping', $payload);

        $response->assertStatus(422)->assertJson([
            'success' => false,
            'request_id' => $payload['request_id'],
            'code' => 'VALIDATION_ERROR',
        ]);
    }

    public function test_malformed_timestamp_is_rejected(): void
    {
        $payload = $this->signedVendorPayload(['timestamp' => 'not-a-date']);

        $this->postJson('/api/vendor/v1/ping', $payload)
            ->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonPath('data.errors.timestamp.0', fn ($message) => is_string($message));
    }

    public function test_a_valid_envelope_passes_validation(): void
    {
        $this->postJson('/api/vendor/v1/ping', $this->signedVendorPayload())
            ->assertStatus(200)
            ->assertJson(['success' => true, 'code' => 'PING_OK']);
    }
}
