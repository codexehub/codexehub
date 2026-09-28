<?php

namespace App\Http\Middleware\VendorApi;

use App\Support\VendorApi\VendorApiErrorCode;
use App\Support\VendorApi\VendorApiResponse;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SRS §63/§120 rate limiting. Runs before {@see VerifyVendorApiSignature} —
 * a cheap check first so an unsigned flood of requests never reaches the
 * (equally cheap, but logically "later") signature/replay work. The key is
 * built from the caller's IP + its claimed (not yet verified) `installation_id`;
 * that's fine here, since this middleware protects against volumetric abuse,
 * not identity — a forged `installation_id` still gets IP-bucketed.
 *
 * Limits are per route name (`config('vendor_api.rate_limits')`), falling
 * back to 'default' until each endpoint task (INST-002, LIC-CP-002,
 * SYNC-001, UPD-001) picks its own SRS §120 category.
 */
class ThrottleVendorApiRequest
{
    public function __construct(private RateLimiter $limiter) {}

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName() ?? 'unknown';
        $limits = config("vendor_api.rate_limits.{$routeName}") ?? config('vendor_api.rate_limits.default');

        $installationId = $request->input('installation_id');
        $key = 'vendor-api:'.$routeName.':'.$request->ip().'|'.(is_string($installationId) && $installationId !== '' ? $installationId : 'unknown');

        if ($this->limiter->tooManyAttempts($key, $limits['max_attempts'])) {
            $requestId = $request->input('request_id');

            return VendorApiResponse::error(
                requestId: is_string($requestId) ? $requestId : null,
                code: VendorApiErrorCode::RateLimitExceeded,
                message: 'Too many requests. Please slow down and try again shortly.',
                data: ['retry_after_seconds' => $this->limiter->availableIn($key)],
            );
        }

        $this->limiter->hit($key, $limits['decay_seconds']);

        return $next($request);
    }
}
