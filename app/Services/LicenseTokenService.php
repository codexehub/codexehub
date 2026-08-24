<?php

namespace App\Services;

use App\Models\License;
use Firebase\JWT\JWT;
use Illuminate\Support\Carbon;

class LicenseTokenService
{
    /**
     * Sign a license token asserting the current status/package of a license.
     * Signed with our private RS256 key -- clients only ever hold the public
     * key, so a stripped/modified client still cannot mint its own tokens.
     */
    public function issue(License $license, string $domain): string
    {
        $now = Carbon::now();
        $ttlHours = (int) config('licensing.token_ttl_hours');

        $payload = [
            'iss' => config('app.url'),
            'sub' => $license->license_key,
            'iat' => $now->timestamp,
            'exp' => $now->copy()->addHours($ttlHours)->timestamp,
            'product' => $license->licensedApp->slug,
            'package' => $license->package->name,
            'features' => $license->package->features ?? [],
            'status' => $license->status,
            'domain' => $domain,
            'expires_at' => optional($license->expires_at)->timestamp,
        ];

        return JWT::encode($payload, $this->privateKey(), config('licensing.jwt.algo'));
    }

    private function privateKey(): string
    {
        return file_get_contents(base_path(config('licensing.jwt.private_key_path')));
    }
}
