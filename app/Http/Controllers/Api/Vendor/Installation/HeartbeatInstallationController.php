<?php

namespace App\Http\Controllers\Api\Vendor\Installation;

use App\Http\Controllers\Api\Vendor\VendorApiController;
use App\Http\Requests\VendorApi\Installation\HeartbeatInstallationRequest;
use App\Services\Installation\InstallationBinder;
use App\Support\VendorApi\VendorApiErrorCode;
use Illuminate\Http\JsonResponse;

/**
 * SRS §65 `POST /installations/heartbeat` (INST-002) — a lightweight
 * liveness ping. Deliberately does not revalidate a PENDING installation
 * (that's `/verify`'s job) — see `InstallationBinder::heartbeat()`.
 */
class HeartbeatInstallationController extends VendorApiController
{
    public function __construct(private InstallationBinder $installations) {}

    public function __invoke(HeartbeatInstallationRequest $request): JsonResponse
    {
        $installation = $this->installations->findByInstallationId($request->input('installation_id'));

        if ($installation === null) {
            return $this->error($request, VendorApiErrorCode::NotFound, 'Installation is not registered. Call /installations/register first.');
        }

        $businessError = $this->installations->businessErrorFor($installation);

        if ($businessError !== null) {
            return $this->error($request, $businessError, $this->installations->businessMessageFor($installation));
        }

        $this->installations->heartbeat($installation, $request->input('software_version'));

        return $this->success(
            $request,
            'INSTALLATION_HEARTBEAT_RECEIVED',
            'Heartbeat received.',
            $this->installations->toPayload($installation->fresh()),
        );
    }
}
