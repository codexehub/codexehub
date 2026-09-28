<?php

namespace App\Http\Requests\VendorApi\Concerns;

/**
 * Every Vendor API request carries this envelope (SRS §70). This trait only
 * guarantees the fields exist and are well-formed strings/dates — it runs
 * *after* `App\Http\Middleware\VendorApi\VerifyVendorApiSignature`, which
 * already rejected a bad signature, a stale timestamp, or a replayed
 * `request_id` before the request ever reaches this validation (APISEC-002).
 */
trait HasVendorEnvelope
{
    protected function envelopeRules(): array
    {
        return [
            'installation_id' => ['required', 'string', 'max:64'],
            'request_id' => ['required', 'string', 'max:64'],
            'timestamp' => ['required', 'date'],
            'client_version' => ['required', 'string', 'max:20'],
            'signature' => ['required', 'string'],
        ];
    }
}
