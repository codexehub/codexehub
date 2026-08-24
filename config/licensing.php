<?php

return [
    'jwt' => [
        'private_key_path' => env('LICENSING_JWT_PRIVATE_KEY_PATH', 'storage/keys/private.pem'),
        'public_key_path' => env('LICENSING_JWT_PUBLIC_KEY_PATH', 'storage/keys/public.pem'),
        'algo' => 'RS256',
    ],

    'default_grace_hours' => (int) env('LICENSING_DEFAULT_GRACE_HOURS', 48),

    // Lifetime of each issued token; the client re-verifies well before this
    // expires, the grace period only matters if it genuinely can't reach us.
    'token_ttl_hours' => (int) env('LICENSING_TOKEN_TTL_HOURS', 24),
];
