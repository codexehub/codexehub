<?php

namespace App\Http\Controllers\Api\Vendor\Installation;

use App\Http\Controllers\Api\Vendor\VendorApiController;
use App\Http\Requests\VendorApi\Installation\VerifyInstallationRequest;
use App\Services\Installation\InstallationBinder;
use App\Support\VendorApi\VendorApiErrorCode;
use Illuminate\Http\JsonResponse;

/**
 * SRS §38 `POST /installations/verify` (INST-002) — the revalidation step: a
 * PENDING installation (fresh registration or a domain change) is confirmed
 * and flipped to ACTIVE here, not on register itself.
 */
class VerifyInstallationController extends VendorApiController
{
    public function __construct(private InstallationBinder $installations) {}

    public function __invoke(VerifyInstallationRequest $request): JsonResponse
    {
        $installation = $this->installations->findByInstallationId($request->input('installation_id'));

        if ($installation === null) {
            return $this->error($request, VendorApiErrorCode::NotFound, 'Installation is not registered. Call /installations/register first.');
        }

        $businessError = $this->installations->businessErrorFor($installation);

        if ($businessError !== null) {
            return $this->error($request, $businessError, $this->installations->businessMessageFor($installation));
        }

        $revalidated = $this->installations->verify($installation);

        return $this->success(
            $request,
            'INSTALLATION_VERIFIED',
            $revalidated ? 'Installation revalidated and is now active.' : 'Installation is valid.',
            $this->installations->toPayload($installation->fresh()),
        );
    }
}
