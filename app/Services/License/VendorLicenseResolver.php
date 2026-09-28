<?php

namespace App\Services\License;

use App\Models\License;
use App\Support\VendorApi\VendorApiErrorCode;

/**
 * Vendor API license lookup + the SRS §61 signed-response payload
 * (LIC-CP-002). Shared by `/licenses/verify`, `/status`, `/renew` — all
 * three resolve a license and build the response the same way; only their
 * side effects differ (verify/renew touch `last_verified_at`; only renew can
 * transition state).
 *
 * Deliberately excludes package `features`/entitlements from the payload —
 * that is `SYNC-001`'s job (`GET /packages/{id}/features`, `POST
 * /packages/sync`), not this task's "signed license response" scope.
 */
class VendorLicenseResolver
{
    public function __construct(private LicenseLifecycle $lifecycle) {}

    public function findByKey(string $licenseKey): ?License
    {
        $license = License::where('license_key', $licenseKey)->first();

        return $license === null ? null : $this->applyExpirySafetyNet($license);
    }

    /**
     * Mirrors the legacy `LicenseApiController`'s safety net: a status may
     * simply have gone stale since it was last checked. Narrowly scoped to
     * ACTIVE→EXPIRED only (matching that existing precedent exactly) — a
     * fuller automatic sweep across every state is LIC-001's job (Phase 9).
     */
    private function applyExpirySafetyNet(License $license): License
    {
        if ($license->status === License::STATUS_ACTIVE
            && $license->expires_at !== null
            && $license->expires_at->isPast()) {
            $this->lifecycle->transition($license, License::STATUS_EXPIRED);
        }

        return $license;
    }

    /**
     * Null when the license is operational (SRS §45/§46 — the site should
     * keep working); otherwise the SRS §72 business code to report.
     */
    public function businessErrorFor(License $license): ?VendorApiErrorCode
    {
        if ($license->isOperational()) {
            return null;
        }

        return match ($license->status) {
            License::STATUS_SUSPENDED => VendorApiErrorCode::LicenseSuspended,
            License::STATUS_REVOKED => VendorApiErrorCode::LicenseRevoked,
            License::STATUS_EXPIRED => VendorApiErrorCode::LicenseExpired,
            // PENDING has no dedicated SRS §72 code — it means "not yet
            // activated", which the pre-SRS system already framed as
            // awaiting payment confirmation (see the legacy
            // LicenseApiController's identical message); PAYMENT_DUE is the
            // closest fit. Recorded decision, not an oversight.
            License::STATUS_PENDING => VendorApiErrorCode::PaymentDue,
            default => VendorApiErrorCode::LicenseSuspended,
        };
    }

    public function businessMessageFor(License $license): string
    {
        return match ($license->status) {
            License::STATUS_SUSPENDED => 'License has been suspended.',
            License::STATUS_REVOKED => 'License has been revoked.',
            License::STATUS_EXPIRED => 'License has expired.',
            License::STATUS_PENDING => 'License is awaiting payment confirmation.',
            default => 'License is not currently active.',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toSignedPayload(License $license, string $requestInstallationId): array
    {
        $graceUntil = $license->next_payment_due !== null
            ? $license->next_payment_due->copy()->addDays($license->grace_period_days)
            : null;

        return [
            'license_key' => $license->license_key,
            // Installation binding/validation is INST-002's job — until then
            // this just echoes the caller's own claimed installation_id when
            // the license hasn't been bound to one yet.
            'installation_id' => $license->installation_id ?? $requestInstallationId,
            'license_status' => $license->status,
            // Pre-SRS flat `licensed_app_packages` row — the SRS §52
            // `customer_packages` shape (with `package_version`) arrives
            // with Phase 2.
            'package' => $license->package?->only(['id', 'name']),
            'expires_at' => optional($license->expires_at)->toIso8601String(),
            'grace_until' => optional($graceUntil)->toIso8601String(),
            'ownership_status' => $license->ownership_status,
            'last_verified_at' => optional($license->last_verified_at)->toIso8601String(),
        ];
    }

    /**
     * SRS §66 `/renew` — a client-triggered check for "has this license's
     * payment already been recorded elsewhere (e.g. an admin manually moved
     * it via LIC-CP-001's status control) but the state hasn't caught up
     * yet?" If so, transitions PAYMENT_DUE/GRACE_PERIOD → ACTIVE immediately
     * instead of making the Vendor Client wait for LIC-001's scheduled job
     * (Phase 9, not built). Does **not** itself record a payment or extend
     * `next_payment_due` — that is Phase 3 (invoicing) / Phase 9 territory.
     *
     * @return bool whether a transition actually happened.
     */
    public function renewIfPaymentRecorded(License $license): bool
    {
        $eligibleFromState = in_array($license->status, [License::STATUS_PAYMENT_DUE, License::STATUS_GRACE_PERIOD], true);
        $paymentLooksRecorded = $license->next_payment_due !== null && $license->next_payment_due->isFuture();

        if (! ($eligibleFromState && $paymentLooksRecorded)) {
            return false;
        }

        $this->lifecycle->transition($license, License::STATUS_ACTIVE);

        return true;
    }
}
