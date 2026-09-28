<?php

namespace App\Support\VendorApi;

use RuntimeException;

/**
 * RSA (SHA-256) signing for Vendor API success responses (SRS §61/§63,
 * APISEC-003). Asymmetric, unlike {@see VendorApiSigner}'s request-side HMAC
 * — the server signs with a private key so the Vendor Client can verify with
 * a public key it holds, without ever needing a secret that could let it (or
 * an attacker) forge a response.
 *
 * The signed content is the same canonicalization `VendorApiSigner` already
 * uses (the payload minus `signature`, recursively key-sorted, JSON-encoded)
 * — one canonicalization algorithm shared by both request and response
 * signing.
 *
 * `signature` is `"{key_id}:{base64(raw signature bytes)}"`. The key id lets
 * a verifier pick the right public key across a rotation: `config('vendor_api.response_signing.keys')`
 * is a map of every key this server has ever signed with, keyed by id;
 * `response_signing.active_key_id` is the one currently used to *sign* new
 * responses. Rotating means adding a new entry to `keys` and flipping
 * `active_key_id` — old entries stay so anything that still needs to verify
 * a response signed under an older key can.
 */
final class VendorApiResponseSigner
{
    public static function sign(array $payload): string
    {
        $keyId = self::activeKeyId();
        $canonical = VendorApiSigner::canonicalize($payload);

        $signed = openssl_sign($canonical, $binarySignature, self::privateKey($keyId), OPENSSL_ALGO_SHA256);

        if (! $signed) {
            throw new RuntimeException("Failed to sign Vendor API response with key '{$keyId}'.");
        }

        return $keyId.':'.base64_encode($binarySignature);
    }

    public static function verify(array $payload, string $signature): bool
    {
        $parts = explode(':', $signature, 2);

        if (count($parts) !== 2) {
            return false;
        }

        [$keyId, $encoded] = $parts;
        $publicKey = self::publicKey($keyId);

        if ($publicKey === null) {
            return false;
        }

        $binarySignature = base64_decode($encoded, true);

        if ($binarySignature === false) {
            return false;
        }

        $canonical = VendorApiSigner::canonicalize($payload);

        return openssl_verify($canonical, $binarySignature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    public static function activeKeyId(): string
    {
        $keyId = config('vendor_api.response_signing.active_key_id');

        if (! is_string($keyId) || $keyId === '') {
            throw new RuntimeException('vendor_api.response_signing.active_key_id is not configured.');
        }

        return $keyId;
    }

    private static function privateKey(string $keyId): string
    {
        $path = config("vendor_api.response_signing.keys.{$keyId}.private_key_path");

        if (! is_string($path)) {
            throw new RuntimeException("No private key configured for Vendor API signing key '{$keyId}'.");
        }

        $resolved = self::resolvePath($path);

        if (! is_file($resolved)) {
            throw new RuntimeException("Vendor API signing private key file not found: {$resolved}. Run `php artisan vendor-api:generate-signing-key`.");
        }

        return file_get_contents($resolved);
    }

    private static function publicKey(string $keyId): ?string
    {
        $path = config("vendor_api.response_signing.keys.{$keyId}.public_key_path");

        if (! is_string($path)) {
            return null;
        }

        $resolved = self::resolvePath($path);

        return is_file($resolved) ? file_get_contents($resolved) : null;
    }

    /**
     * Config paths are relative to the app base path, matching
     * `config/licensing.php`'s convention — except an already-absolute path
     * (e.g. a test pointing at a temp file) is used as-is.
     */
    private static function resolvePath(string $path): string
    {
        return is_file($path) ? $path : base_path($path);
    }
}
