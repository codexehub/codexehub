<?php

namespace App\Http\Requests\VendorApi;

use App\Http\Requests\VendorApi\Concerns\HasVendorEnvelope;
use App\Support\VendorApi\VendorApiErrorCode;
use App\Support\VendorApi\VendorApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Base Form Request for every Vendor API endpoint. A validation failure
 * (including a missing/malformed envelope field) renders the SRS §71
 * standard error envelope instead of Laravel's default {message, errors}
 * shape, so a Vendor Client only ever has to parse one response format.
 */
abstract class VendorApiRequest extends FormRequest
{
    use HasVendorEnvelope;

    /**
     * Endpoint-specific rules, merged with the envelope rules below.
     */
    abstract protected function endpointRules(): array;

    public function rules(): array
    {
        return array_merge($this->envelopeRules(), $this->endpointRules());
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            VendorApiResponse::error(
                requestId: is_string($this->input('request_id')) ? $this->input('request_id') : null,
                code: VendorApiErrorCode::ValidationError,
                message: 'One or more fields failed validation.',
                data: ['errors' => $validator->errors()->toArray()],
            )
        );
    }
}
