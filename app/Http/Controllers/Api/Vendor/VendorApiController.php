<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorApi\VendorApiRequest;
use App\Support\VendorApi\VendorApiErrorCode;
use App\Support\VendorApi\VendorApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Base for every `/api/vendor/v1/*` controller — response helpers only.
 * Envelope shape validation lives in {@see VendorApiRequest}; signature
 * verification, replay/timestamp rejection and request-id dedupe run in
 * `App\Http\Middleware\VendorApi\VerifyVendorApiSignature` and rate limiting
 * in `App\Http\Middleware\VendorApi\ThrottleVendorApiRequest` (both applied
 * to the route group in `routes/vendor-api.php`, APISEC-002) — never here.
 */
abstract class VendorApiController extends Controller
{
    protected function success(Request $request, string $code, string $message, array $data = [], int $status = 200): JsonResponse
    {
        return VendorApiResponse::success($this->requestId($request), $code, $message, $data, $status);
    }

    protected function error(Request $request, VendorApiErrorCode $code, string $message, array $data = [], ?int $status = null): JsonResponse
    {
        return VendorApiResponse::error($this->requestId($request), $code, $message, $data, $status);
    }

    private function requestId(Request $request): ?string
    {
        $requestId = $request->input('request_id');

        return is_string($requestId) ? $requestId : null;
    }
}
