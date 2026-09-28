<?php

namespace App\Support\VendorApi;

use Illuminate\Http\JsonResponse;

/**
 * Builds the SRS §71 standard Vendor API response envelope. Success
 * responses are signed by {@see VendorApiResponseSigner} (APISEC-003, RSA) —
 * error responses carry no `signature` key at all (recorded at APISEC-001:
 * nothing sensitive to protect in an error body).
 */
final class VendorApiResponse
{
    public static function success(?string $requestId, string $code, string $message, array $data = [], int $status = 200): JsonResponse
    {
        $body = [
            'success' => true,
            'request_id' => $requestId,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];

        $body['signature'] = VendorApiResponseSigner::sign($body);

        return new JsonResponse($body, $status);
    }

    public static function error(?string $requestId, VendorApiErrorCode $code, string $message, array $data = [], ?int $status = null): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'request_id' => $requestId,
            'code' => $code->value,
            'message' => $message,
            'data' => $data,
        ], $status ?? self::defaultStatusFor($code));
    }

    private static function defaultStatusFor(VendorApiErrorCode $code): int
    {
        return match ($code) {
            VendorApiErrorCode::ValidationError => 422,
            VendorApiErrorCode::NotFound => 404,
            VendorApiErrorCode::SignatureError,
            VendorApiErrorCode::RequestExpired,
            VendorApiErrorCode::DuplicateRequest => 401,
            VendorApiErrorCode::RateLimitExceeded => 429,
            VendorApiErrorCode::LicenseSuspended,
            VendorApiErrorCode::LicenseExpired,
            VendorApiErrorCode::PaymentDue,
            VendorApiErrorCode::LicenseRevoked,
            VendorApiErrorCode::InstallationSuspended => 403,
            VendorApiErrorCode::InstallationAlreadyBound => 409,
            default => 500,
        };
    }
}
