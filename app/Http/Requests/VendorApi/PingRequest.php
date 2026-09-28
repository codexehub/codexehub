<?php

namespace App\Http\Requests\VendorApi;

/**
 * `/ping` has no fields beyond the envelope — it exists only to prove the
 * envelope/response/error-classification skeleton end-to-end (APISEC-001).
 */
class PingRequest extends VendorApiRequest
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
