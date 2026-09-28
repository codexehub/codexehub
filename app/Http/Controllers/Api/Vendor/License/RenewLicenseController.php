<?php

namespace App\Http\Controllers\Api\Vendor\License;

use App\Http\Controllers\Api\Vendor\VendorApiController;
use App\Http\Requests\VendorApi\License\LicenseKeyRequest;
use App\Services\License\VendorLicenseResolver;
use App\Support\VendorApi\VendorApiErrorCode;
use Illuminate\Http\JsonResponse;

/**
 * SRS §66 `POST /licenses/renew` (LIC-CP-002). Does not itself process a
 * payment or extend `next_payment_due` — that is Phase 3 (invoicing) /
 * Phase 9 (`LIC-001..007`) territory. What it does: if payment for a
 * PAYMENT_DUE/GRACE_PERIOD license already looks recorded elsewhere (see
 * `VendorLicenseResolver::renewIfPaymentRecorded()`), transitions it to
 * ACTIVE immediately rather than making the Vendor Client wait for LIC-001's
 * scheduled job (Phase 9, not built yet). A non-operational license (e.g.
 * suspended) is still rejected — renewal never bypasses that.
 */
class RenewLicenseController extends VendorApiController
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

        $renewed = $this->licenses->renewIfPaymentRecorded($license);

        $license->forceFill(['last_verified_at' => now()])->save();

        $payload = $this->licenses->toSignedPayload($license, $request->input('installation_id'));
        $payload['renewed'] = $renewed;

        return $this->success(
            $request,
            'LICENSE_RENEWAL_CHECKED',
            $renewed ? 'License renewed.' : 'No renewal was needed.',
            $payload,
        );
    }
}
