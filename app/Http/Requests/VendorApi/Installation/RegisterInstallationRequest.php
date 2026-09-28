<?php

namespace App\Http\Requests\VendorApi\Installation;

use App\Http\Requests\VendorApi\VendorApiRequest;
use App\Models\Installation;
use Illuminate\Validation\Rule;

/**
 * `POST /installations/register` (INST-002) — the envelope's own
 * `installation_id` *is* the SRS §38 installation identity being bound;
 * `license_key` is what proves the caller is authorized to bind it to a
 * customer/product/package, the same credential the pre-SRS
 * `/api/v1/license/activate` endpoint already uses.
 */
class RegisterInstallationRequest extends VendorApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function endpointRules(): array
    {
        return [
            'license_key' => ['required', 'string', 'max:64'],
            'domain' => ['required', 'string', 'max:255'],
            'environment' => ['nullable', 'string', Rule::in(Installation::ENVIRONMENTS)],
            'software_version' => ['nullable', 'string', 'max:20'],
        ];
    }
}
