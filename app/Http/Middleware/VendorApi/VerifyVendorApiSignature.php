<?php

namespace App\Http\Middleware\VendorApi;

use App\Http\Requests\VendorApi\Concerns\HasVendorEnvelope;
use App\Support\VendorApi\VendorApiErrorCode;
use App\Support\VendorApi\VendorApiResponse;
use App\Support\VendorApi\VendorApiSigner;
use Closure;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * SRS §63/§70 — signature verification, timestamp freshness (replay window),
 * and request_id dedupe (replay protection). Deliberately does NOT duplicate
 * {@see HasVendorEnvelope}'s shape validation: if the envelope fields this
 * needs (installation_id/request_id/timestamp/signature) aren't all present
 * as non-empty strings, it passes the request through untouched so the
 * normal 422 VALIDATION_ERROR (with a per-field message) still fires
 * downstream — this middleware only judges requests that are shaped well
 * enough to actually verify.
 *
 * Order matters: signature is checked first (proves the payload, including
 * timestamp/request_id, wasn't tampered with) before freshness/dedupe trust
 * those values and before either is "spent" — an unsigned/forged request
 * never gets to pollute the dedupe cache or learn timing information about
 * why it was rejected.
 */
class VerifyVendorApiSignature
{
    public function __construct(private Cache $cache) {}

    public function handle(Request $request, Closure $next): Response
    {
        $envelope = $this->presentEnvelope($request);

        if ($envelope === null) {
            return $next($request);
        }

        [$installationId, $requestId, $timestamp, $signature] = $envelope;

        $secret = config('vendor_api.signing_secret');

        if (! is_string($secret) || $secret === '') {
            return VendorApiResponse::error($requestId, VendorApiErrorCode::ServerError, 'Vendor API signing is not configured.');
        }

        if (! VendorApiSigner::verify($request->all(), $signature, $secret)) {
            return VendorApiResponse::error($requestId, VendorApiErrorCode::SignatureError, 'Request signature is missing or invalid.');
        }

        $parsedTimestamp = $this->parseTimestamp($timestamp);

        if ($parsedTimestamp === null) {
            // Well-formed signature, malformed timestamp value — let the
            // FormRequest's `date` rule report it as a normal 422.
            return $next($request);
        }

        $windowSeconds = (int) config('vendor_api.replay_window_seconds', 300);
        $driftSeconds = abs(Carbon::now()->getTimestamp() - $parsedTimestamp->getTimestamp());

        if ($driftSeconds > $windowSeconds) {
            return VendorApiResponse::error($requestId, VendorApiErrorCode::RequestExpired, 'Request timestamp is outside the allowed freshness window.');
        }

        $dedupeKey = "vendor-api:request-id:{$installationId}:{$requestId}";

        if ($this->cache->has($dedupeKey)) {
            return VendorApiResponse::error($requestId, VendorApiErrorCode::DuplicateRequest, 'This request_id has already been processed.');
        }

        $this->cache->put($dedupeKey, true, (int) config('vendor_api.request_id_dedupe_ttl_seconds', 600));

        return $next($request);
    }

    /**
     * @return array{0:string,1:string,2:string,3:string}|null installation_id, request_id, timestamp, signature
     */
    private function presentEnvelope(Request $request): ?array
    {
        $fields = [
            $request->input('installation_id'),
            $request->input('request_id'),
            $request->input('timestamp'),
            $request->input('signature'),
        ];

        foreach ($fields as $field) {
            if (! is_string($field) || $field === '') {
                return null;
            }
        }

        return $fields;
    }

    private function parseTimestamp(string $timestamp): ?Carbon
    {
        try {
            return Carbon::parse($timestamp);
        } catch (Throwable) {
            return null;
        }
    }
}
