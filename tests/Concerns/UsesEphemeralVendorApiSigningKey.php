<?php

namespace Tests\Concerns;

/**
 * APISEC-003 response signing needs a real RSA keypair on disk
 * (`config('vendor_api.response_signing')`). Points config at the committed
 * test-only fixture keypair in `tests/fixtures/vendor-api/` (see the
 * README.md alongside it) rather than generating one at runtime —
 * `openssl_pkey_new()` depends on the host's OpenSSL config being
 * discoverable (observed to fail on a stock Windows/XAMPP PHP install
 * without `OPENSSL_CONF` set), which a test suite must not depend on to
 * pass. Signing/verifying with an already-loaded PEM has no such dependency.
 */
trait UsesEphemeralVendorApiSigningKey
{
    protected function useEphemeralVendorApiSigningKey(): void
    {
        config(['vendor_api.response_signing' => [
            'active_key_id' => 'test',
            'keys' => [
                'test' => [
                    'private_key_path' => base_path('tests/fixtures/vendor-api/test-private.pem'),
                    'public_key_path' => base_path('tests/fixtures/vendor-api/test-public.pem'),
                ],
            ],
        ]]);
    }
}
