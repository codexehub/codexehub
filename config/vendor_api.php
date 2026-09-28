<?php

return [
    /*
     * Shared HMAC signing secret (SRS §63/§70). Temporary: a single
     * environment-wide secret used for every installation until INST-002
     * issues one credential per installation — VerifyVendorApiSignature will
     * then look the secret up by installation_id instead of reading this one
     * config value; nothing else about the signing scheme changes.
     */
    'signing_secret' => env('VENDOR_API_SIGNING_SECRET'),

    // How far a request's `timestamp` may drift from server time (either
    // direction) before it's rejected as stale (SRS §63 "Timestamp validation").
    'replay_window_seconds' => (int) env('VENDOR_API_REPLAY_WINDOW_SECONDS', 300),

    // How long a seen `request_id` is remembered to reject a literal replay
    // (SRS §63 "Request IDs" / "Replay protection"). Kept >= the replay
    // window so a request can never be simultaneously "fresh" and "forgotten"
    // by the dedupe cache.
    'request_id_dedupe_ttl_seconds' => (int) env('VENDOR_API_REQUEST_ID_DEDUPE_TTL_SECONDS', 600),

    /*
     * Per-route rate limits (SRS §120) — max attempts per decay window, keyed
     * by route name. 'default' covers any vendor-api route without its own
     * entry. Each later endpoint task (INST-002, LIC-CP-002, SYNC-001,
     * UPD-001) should add its own entry here for the SRS §120 category it
     * belongs to (license verification / heartbeat / package sync / release
     * download / deployment) instead of relying on the default.
     */
    'rate_limits' => [
        'default' => [
            'max_attempts' => (int) env('VENDOR_API_RATE_LIMIT_DEFAULT_MAX_ATTEMPTS', 60),
            'decay_seconds' => (int) env('VENDOR_API_RATE_LIMIT_DEFAULT_DECAY_SECONDS', 60),
        ],

        // LIC-CP-002 (SRS §120 "License Verification" category). verify/status
        // are cheap reads a Vendor Client may poll fairly often; renew is
        // capped tighter since there's rarely a reason to call it in a burst.
        'vendor-api.licenses.verify' => ['max_attempts' => 30, 'decay_seconds' => 60],
        'vendor-api.licenses.status' => ['max_attempts' => 30, 'decay_seconds' => 60],
        'vendor-api.licenses.renew' => ['max_attempts' => 10, 'decay_seconds' => 60],

        // INST-002 (SRS §120 "Installation Registration"/"Heartbeat"
        // categories). Heartbeat is expected to be the most frequent call in
        // the whole API (a periodic liveness ping) so it gets the most
        // headroom; register is rare (once per install, occasionally again
        // on a domain change) so it's capped tightly.
        'vendor-api.installations.register' => ['max_attempts' => 20, 'decay_seconds' => 60],
        'vendor-api.installations.verify' => ['max_attempts' => 30, 'decay_seconds' => 60],
        'vendor-api.installations.heartbeat' => ['max_attempts' => 120, 'decay_seconds' => 60],
        'vendor-api.installations.show' => ['max_attempts' => 30, 'decay_seconds' => 60],
    ],

    /*
     * Response signing (SRS §61/§63, APISEC-003) — RSA keypair(s) used to
     * sign every success response's `signature` field (error responses carry
     * no `signature` key at all — see APISEC-001's recorded asymmetry
     * decision). `active_key_id` is the key used to *sign* new responses;
     * `keys` is every key this server has ever signed with (keyed by id) so
     * a rotation is additive: generate a new key
     * (`php artisan vendor-api:generate-signing-key --key-id=v2`), add its
     * entry here, then flip `active_key_id` once deployed — old entries stay
     * so anything still relying on an older key can still be verified
     * against it.
     */
    'response_signing' => [
        'active_key_id' => env('VENDOR_API_RESPONSE_SIGNING_KEY_ID', 'v1'),

        'keys' => [
            'v1' => [
                'private_key_path' => env('VENDOR_API_RESPONSE_SIGNING_V1_PRIVATE_KEY_PATH', 'storage/keys/vendor-api/v1-private.pem'),
                'public_key_path' => env('VENDOR_API_RESPONSE_SIGNING_V1_PUBLIC_KEY_PATH', 'storage/keys/vendor-api/v1-public.pem'),
            ],
        ],
    ],
];
