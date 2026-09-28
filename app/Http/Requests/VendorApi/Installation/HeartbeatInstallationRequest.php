<?php

namespace App\Http\Requests\VendorApi\Installation;

use App\Http\Requests\VendorApi\VendorApiRequest;

/**
 * `POST /installations/heartbeat` (INST-002) — a lightweight, frequent
 * liveness ping. `software_version` is optional so a heartbeat can also
 * report a version bump without waiting for the next `/verify` cycle.
 */
class HeartbeatInstallationRequest extends VendorApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function endpointRules(): array
    {
        return [
            'software_version' => ['nullable', 'string', 'max:20'],
        ];
    }
}
