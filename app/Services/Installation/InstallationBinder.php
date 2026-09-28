<?php

namespace App\Services\Installation;

use App\Models\Installation;
use App\Models\License;
use App\Support\VendorApi\VendorApiErrorCode;
use Illuminate\Support\Carbon;

/**
 * SRS §38/§65 (INST-002) — installation identity/binding + the register →
 * verify → heartbeat lifecycle on top of the INST-001 `installations` table.
 * Reuses `App\Services\License\VendorLicenseResolver` for license lookup +
 * operational-state checks (WORK-INSTRUCTIONS §8) rather than duplicating
 * that logic here.
 */
class InstallationBinder
{
    public function findByInstallationId(string $installationId): ?Installation
    {
        return Installation::where('installation_id', $installationId)->first();
    }

    /**
     * A license already bound to a *different* installation than the one in
     * this request can't be registered again elsewhere — the SRS §59
     * `licenses.installation_id` column is a single value, not a pivot, so
     * "one license, one installation" is the model this schema actually
     * supports (unlike the pre-SRS multi-activation `license_activations`).
     */
    public function isLicenseBoundElsewhere(License $license, string $installationId): bool
    {
        return $license->installation_id !== null && $license->installation_id !== $installationId;
    }

    /**
     * The reverse conflict: this exact `installation_id` already resolved to
     * a *different* customer than the one this license belongs to (SRS §80/81
     * customer isolation — an installation identity must not silently switch
     * owners because a caller claimed the same id with a different license).
     */
    public function isInstallationBoundToAnotherCustomer(Installation $installation, License $license): bool
    {
        $customerId = $license->customer?->customer_id;

        return $installation->customer_id !== null && $customerId !== null && $installation->customer_id !== $customerId;
    }

    /**
     * Creates or updates the `installations` row for `$installationId`,
     * binding it to `$license`'s customer/product/package. A first-time
     * registration is immediately ACTIVE (nothing to revalidate yet); a
     * *domain change* on an already-registered installation moves it to
     * PENDING instead of silently accepting the new domain — per SRS §38
     * "Domain changes must require authorization/revalidation", `/verify`
     * (not this method) is what confirms the new domain and flips it back to
     * ACTIVE. Also (re)binds `licenses.installation_id`, closing the gap
     * INST-001 deliberately left open.
     *
     * @return array{installation: Installation, domain_changed: bool}
     */
    public function register(
        License $license,
        string $installationId,
        string $domain,
        ?string $environment,
        ?string $vendorClientVersion,
        ?string $softwareVersion,
    ): array {
        $installation = $this->findByInstallationId($installationId);
        $domainChanged = $installation !== null && $installation->domain !== $domain;

        $attributes = [
            'customer_id' => $license->customer?->customer_id,
            'product_id' => $license->licensedApp?->product_id,
            'licensed_app_package_id' => $license->licensed_app_package_id,
            'domain' => $domain,
            'environment' => $environment ?? $installation?->environment ?? Installation::ENVIRONMENT_PRODUCTION,
            'vendor_client_version' => $vendorClientVersion,
            'software_version' => $softwareVersion ?? $installation?->software_version,
        ];

        if ($installation === null) {
            $installation = Installation::create($attributes + [
                'installation_id' => $installationId,
                'status' => Installation::STATUS_ACTIVE,
            ]);
        } else {
            if ($domainChanged) {
                $attributes['status'] = Installation::STATUS_PENDING;
            }

            $installation->fill($attributes);
            $installation->save();
        }

        if ($license->installation_id !== $installation->installation_id) {
            $license->forceFill(['installation_id' => $installation->installation_id])->save();
        }

        return ['installation' => $installation->fresh(), 'domain_changed' => $domainChanged];
    }

    public function businessErrorFor(Installation $installation): ?VendorApiErrorCode
    {
        return $installation->status === Installation::STATUS_SUSPENDED
            ? VendorApiErrorCode::InstallationSuspended
            : null;
    }

    public function businessMessageFor(Installation $installation): string
    {
        return 'Installation has been suspended.';
    }

    /**
     * The revalidation step (SRS §38): if the installation is PENDING (a
     * fresh registration or a domain change awaiting confirmation), this
     * flips it to ACTIVE. Always refreshes `last_verified_at`.
     *
     * @return bool whether this call performed a PENDING → ACTIVE revalidation.
     */
    public function verify(Installation $installation): bool
    {
        $wasPending = $installation->status === Installation::STATUS_PENDING;

        if ($wasPending) {
            $installation->status = Installation::STATUS_ACTIVE;
        }

        $installation->last_verified_at = Carbon::now();
        $installation->save();

        return $wasPending;
    }

    /**
     * Pure liveness signal — deliberately does **not** touch `status`, even
     * when PENDING. Keeping heartbeat (liveness) and verify (revalidation)
     * separate means a compromised/misbehaving client can't reactivate a
     * domain change just by pinging the heartbeat endpoint.
     */
    public function heartbeat(Installation $installation, ?string $softwareVersion): void
    {
        $installation->last_heartbeat_at = Carbon::now();

        if ($softwareVersion !== null) {
            $installation->software_version = $softwareVersion;
        }

        $installation->save();
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(Installation $installation): array
    {
        return [
            'installation_id' => $installation->installation_id,
            'status' => $installation->status,
            'domain' => $installation->domain,
            'environment' => $installation->environment,
            'customer' => $installation->customer?->only(['id', 'name']),
            'product' => $installation->product?->only(['id', 'key', 'title']),
            'package' => $installation->package?->only(['id', 'name']),
            'vendor_client_version' => $installation->vendor_client_version,
            'software_version' => $installation->software_version,
            'last_heartbeat_at' => optional($installation->last_heartbeat_at)->toIso8601String(),
            'last_verified_at' => optional($installation->last_verified_at)->toIso8601String(),
        ];
    }
}
