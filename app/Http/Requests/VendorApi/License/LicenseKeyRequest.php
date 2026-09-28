<?php

namespace App\Http\Requests\VendorApi\License;

use App\Http\Requests\VendorApi\VendorApiRequest;

/**
 * Shared by `/licenses/verify`, `/renew`, `/status` (LIC-CP-002) — all three
 * take the same single endpoint-specific field beyond the envelope.
 */
class LicenseKeyRequest extends VendorApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function endpointRules(): array
    {
        return [
            'license_key' => ['required', 'string', 'max:64'],
        ];
    }
}
