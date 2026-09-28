# Vendor API test signing keypairs

`test-private.pem` / `test-public.pem` are a throwaway RSA-2048 keypair used
**only** by `Tests\Concerns\UsesEphemeralVendorApiSigningKey` to exercise
`App\Support\VendorApi\VendorApiResponseSigner` (APISEC-003) in tests.
`other-private.pem` / `other-public.pem` is a second, unrelated keypair used
by one test (`VendorApiResponseSignerTest::test_a_signature_from_one_key_does_not_verify_against_a_different_keys_public_key`)
to prove a signature doesn't verify under the wrong key. Neither pair is used
anywhere in the running application, signs anything real, or carries any
secret of value outside a test run.

Regenerate any time with:

```
php artisan vendor-api:generate-signing-key --key-id=fixture --force
mv storage/keys/vendor-api/fixture-private.pem tests/fixtures/vendor-api/test-private.pem
mv storage/keys/vendor-api/fixture-public.pem tests/fixtures/vendor-api/test-public.pem
```
