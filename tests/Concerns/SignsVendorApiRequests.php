<?php

namespace Tests\Concerns;

use App\Support\VendorApi\VendorApiSigner;

/**
 * Shared helper for Vendor API tests (APISEC-002 onward): builds a valid
 * envelope and computes a real HMAC signature over it using the same
 * `config('vendor_api.signing_secret')` the app verifies against
 * (`VENDOR_API_SIGNING_SECRET` in phpunit.xml), so tests exercise the real
 * signature-verification path instead of a placeholder string.
 */
trait SignsVendorApiRequests
{
    protected function signedVendorPayload(array $overrides = []): array
    {
        $payload = array_merge([
            'installation_id' => 'INS-100001',
            'request_id' => 'REQ-'.strtoupper(bin2hex(random_bytes(6))),
            'timestamp' => now()->toIso8601String(),
            'client_version' => '1.0.0',
        ], $overrides);

        $payload['signature'] = VendorApiSigner::sign($payload, $this->vendorApiSecret());

        return $payload;
    }

    protected function vendorApiSecret(): string
    {
        return (string) config('vendor_api.signing_secret');
    }
}
