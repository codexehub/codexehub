<?php

namespace App\Http\Controllers\Api\Vendor\License;

use App\Http\Controllers\Api\Vendor\VendorApiController;
use App\Http\Requests\VendorApi\License\LicenseKeyRequest;
use App\Services\License\VendorLicenseResolver;
use App\Support\VendorApi\VendorApiErrorCode;
use Illuminate\Http\JsonResponse;

/**
 * SRS §66 `POST /licenses/verify` (LIC-CP-002) — the Vendor Client's
 * periodic license check-in. Updates `last_verified_at` on every call, which
 * is exactly why `License::auditExclude()` already excludes that column
 * (added at AUDIT-002, in anticipation of this endpoint).
 */
class VerifyLicenseController extends VendorApiController
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

        $license->forceFill(['last_verified_at' => now()])->save();

        return $this->success(
            $request,
            'LICENSE_VERIFIED',
            'License is valid.',
            $this->licenses->toSignedPayload($license, $request->input('installation_id')),
        );
    }
}
