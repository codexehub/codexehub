<?php

namespace App\Support\VendorApi;

/**
 * HMAC-SHA256 request signing (SRS §63/§70). The signed payload is the full
 * request body minus `signature` itself, canonicalized by recursively
 * sorting array keys and JSON-encoding — the Vendor Client must build the
 * exact same canonical string or verification will never match; this class
 * is the single source of truth for that algorithm (mirrored in
 * `doc/backend/API-CONTRACT.md`).
 *
 * The secret is a single shared value today (`config('vendor_api.signing_secret')`)
 * because no per-installation credential exists yet (INST-002). Once it
 * does, callers pass that installation's secret instead — this class's
 * shape doesn't change.
 */
final class VendorApiSigner
{
    public static function sign(array $payload, string $secret): string
    {
        return hash_hmac('sha256', self::canonicalize($payload), $secret);
    }

    public static function verify(array $payload, string $signature, string $secret): bool
    {
        return hash_equals(self::sign($payload, $secret), $signature);
    }

    public static function canonicalize(array $payload): string
    {
        unset($payload['signature']);

        return json_encode(self::ksortRecursive($payload), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private static function ksortRecursive(array $array): array
    {
        ksort($array);

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::ksortRecursive($value);
            }
        }

        return $array;
    }
}
