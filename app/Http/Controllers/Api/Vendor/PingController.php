<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Requests\VendorApi\PingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

/**
 * Diagnostic-only endpoint proving the envelope/response skeleton works
 * end-to-end. Not part of the SRS endpoint list (§65-§69) — real endpoints
 * (installations, licenses, packages/sync, updates, deployments) are their
 * own tasks (INST-002, LIC-CP-002, SYNC-001, UPD-001, DEP-004).
 */
class PingController extends VendorApiController
{
    public function __invoke(PingRequest $request): JsonResponse
    {
        return $this->success($request, 'PING_OK', 'Vendor API is reachable.', [
            'server_time' => Carbon::now()->toIso8601String(),
        ]);
    }
}
