<?php

namespace App\Http\Controllers\Api\Vendor\Installation;

use App\Http\Controllers\Api\Vendor\VendorApiController;
use App\Http\Requests\VendorApi\Installation\ShowInstallationRequest;
use App\Services\Installation\InstallationBinder;
use App\Support\VendorApi\VendorApiErrorCode;
use Illuminate\Http\JsonResponse;

/**
 * SRS §65 `GET /installations/{id}` (INST-002) — deliberately a
 * self-lookup only: `{id}` must match the caller's own envelope
 * `installation_id`, otherwise this responds `NOT_FOUND` rather than
 * confirming or denying that some other installation exists (SRS §80/§81
 * customer isolation — no route-model-binding, since a raw 404 wouldn't
 * carry the standard Vendor API error envelope).
 */
class ShowInstallationController extends VendorApiController
{
    public function __construct(private InstallationBinder $installations) {}

    public function __invoke(ShowInstallationRequest $request, string $installationId): JsonResponse
    {
        if ($installationId !== $request->input('installation_id')) {
            return $this->error($request, VendorApiErrorCode::NotFound, 'Installation not found.');
        }

        $installation = $this->installations->findByInstallationId($installationId);

        if ($installation === null) {
            return $this->error($request, VendorApiErrorCode::NotFound, 'Installation not found.');
        }

        return $this->success(
            $request,
            'INSTALLATION_STATUS',
            'Installation status retrieved.',
            $this->installations->toPayload($installation),
        );
    }
}
