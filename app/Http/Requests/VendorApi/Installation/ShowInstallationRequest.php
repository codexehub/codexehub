<?php

namespace App\Http\Requests\VendorApi\Installation;

use App\Http\Requests\VendorApi\VendorApiRequest;

/**
 * `GET /installations/{id}` (INST-002) — no fields beyond the envelope
 * (carried as query parameters on a GET request; `VendorApiRequest`/
 * `VendorApiSigner` treat GET query params and POST body fields
 * identically, both read via `$request->input()`/`$request->all()`).
 */
class ShowInstallationRequest extends VendorApiRequest
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
