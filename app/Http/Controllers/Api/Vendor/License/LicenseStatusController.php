<?php

namespace App\Http\Controllers\Api\Vendor\License;

use App\Http\Controllers\Api\Vendor\VendorApiController;
use App\Http\Requests\VendorApi\License\LicenseKeyRequest;
use App\Services\License\VendorLicenseResolver;
use App\Support\VendorApi\VendorApiErrorCode;
use Illuminate\Http\JsonResponse;

/**
 * SRS §66 `POST /licenses/status` (LIC-CP-002) — a pure read: unlike
 * `/verify`, this does **not** update `last_verified_at` (it isn't a
 * verification heartbeat, just an on-demand status peek — e.g. for a "My
 * Package" screen refresh that shouldn't count as the periodic check-in).
 * The ACTIVE→EXPIRED staleness safety net still applies (that's a
 * data-correctness fix, not a heartbeat side effect).
 */
class LicenseStatusController extends VendorApiController
{
    public function __construct(private VendorLicenseResolver $licenses) {}

    public function __invoke(LicenseKeyRequest $request): JsonResponse
    {
        $license = $this->licenses->findByKey($request->input('license_key'));

        if ($license === null) {
            return $this->error($request, VendorApiErrorCode::NotFound, 'License key not recognized.');
        }

        $businessError = $this->licenses->businessErrorFor($license);

        if ($businessError !== null) {
            return $this->error($request, $businessError, $this->licenses->businessMessageFor($license));
        }

        return $this->success(
            $request,
            'LICENSE_STATUS',
            'License status retrieved.',
            $this->licenses->toSignedPayload($license, $request->input('installation_id')),
        );
    }
}
