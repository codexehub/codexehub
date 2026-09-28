<?php

namespace App\Http\Requests\VendorApi\Installation;

use App\Http\Requests\VendorApi\VendorApiRequest;

/**
 * `POST /installations/verify` (INST-002) — no fields beyond the envelope;
 * the envelope's own `installation_id` identifies which installation to
 * revalidate.
 */
class VerifyInstallationRequest extends VendorApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function endpointRules(): array
    {
        return [];
    }
}
