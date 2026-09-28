<?php

namespace App\Support\VendorApi;

/**
 * Vendor API error codes (SRS §72) — the Vendor Client must treat these two
 * groups differently: a business code reflects the customer's real license
 * state and may legitimately lock the site; a technical code reflects a
 * communication/processing failure and must never be interpreted as
 * suspension (SRS §62, §72). New codes are added by the task that first
 * returns them (e.g. LIC-CP-002, SYNC-001) — this enum is the catalog, not a
 * predictor of every future code.
 */
enum VendorApiErrorCode: string
{
    // Business state — the request reached us and reflects real account state.
    case LicenseSuspended = 'LICENSE_SUSPENDED';
    case LicenseExpired = 'LICENSE_EXPIRED';
    case PaymentDue = 'PAYMENT_DUE';
    case LicenseRevoked = 'LICENSE_REVOKED';

    // Business state (INST-002) — installation identity/binding conflicts.
    // Not in the SRS §72 catalog by name, added the same way VALIDATION_ERROR/
    // NOT_FOUND were at APISEC-001: a working API needs them, they genuinely
    // reflect account state (not a communication glitch), so "business" fits
    // by elimination.
    case InstallationSuspended = 'INSTALLATION_SUSPENDED';
    case InstallationAlreadyBound = 'INSTALLATION_ALREADY_BOUND';

    // Technical error — the request could not be processed as sent.
    case ApiTimeout = 'API_TIMEOUT';
    case NetworkError = 'NETWORK_ERROR';
    case InvalidResponse = 'INVALID_RESPONSE';
    case ServerError = 'SERVER_ERROR';
    case SignatureError = 'SIGNATURE_ERROR';
    case ValidationError = 'VALIDATION_ERROR';
    case NotFound = 'NOT_FOUND';

    // Transport-level security (APISEC-002) — the request itself could not
    // be trusted as sent; always technical, never a reflection of license state.
    case RequestExpired = 'REQUEST_EXPIRED';
    case DuplicateRequest = 'DUPLICATE_REQUEST';
    case RateLimitExceeded = 'RATE_LIMIT_EXCEEDED';

    public function classification(): string
    {
        return match ($this) {
            self::LicenseSuspended, self::LicenseExpired, self::PaymentDue, self::LicenseRevoked,
            self::InstallationSuspended, self::InstallationAlreadyBound => 'business',
            default => 'technical',
        };
    }

    public function isBusiness(): bool
    {
        return $this->classification() === 'business';
    }

    public function isTechnical(): bool
    {
        return $this->classification() === 'technical';
    }
}
