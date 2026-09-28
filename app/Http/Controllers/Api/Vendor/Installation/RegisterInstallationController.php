<?php

namespace App\Http\Controllers\Api\Vendor\Installation;

use App\Http\Controllers\Api\Vendor\VendorApiController;
use App\Http\Requests\VendorApi\Installation\RegisterInstallationRequest;
use App\Services\Installation\InstallationBinder;
use App\Services\License\VendorLicenseResolver;
use App\Support\VendorApi\VendorApiErrorCode;
use Illuminate\Http\JsonResponse;

/**
 * SRS §38/§65 `POST /installations/register` (INST-002) — binds the
 * envelope's `installation_id` to the customer/product/package a valid
 * `license_key` resolves to. A domain change on an already-registered
 * installation is accepted but flips it to PENDING rather than silently
 * trusting the new domain — see `InstallationBinder::register()`.
 */
class RegisterInstallationController extends VendorApiController
{
    public function __construct(
        private VendorLicenseResolver $licenses,
        private InstallationBinder $installations,
    ) {}

    public function __invoke(RegisterInstallationRequest $request): JsonResponse
    {
        $license = $this->licenses->findByKey($request->input('license_key'));

        if ($license === null) {
            return $this->error($request, VendorApiErrorCode::NotFound, 'License key not recognized.');
        }

        $businessError = $this->licenses->businessErrorFor($license);

        if ($businessError !== null) {
            return $this->error($request, $businessError, $this->licenses->businessMessageFor($license));
        }

        $installationId = $request->input('installation_id');

        if ($this->installations->isLicenseBoundElsewhere($license, $installationId)) {
            return $this->error(
                $request,
                VendorApiErrorCode::InstallationAlreadyBound,
                'This license is already registered to a different installation.',
            );
        }

        $existing = $this->installations->findByInstallationId($installationId);

        if ($existing !== null && $this->installations->isInstallationBoundToAnotherCustomer($existing, $license)) {
            return $this->error(
                $request,
                VendorApiErrorCode::InstallationAlreadyBound,
                'This installation identity is already registered to a different customer.',
            );
        }

        $result = $this->installations->register(
            license: $license,
            installationId: $installationId,
            domain: $request->input('domain'),
            environment: $request->input('environment'),
            vendorClientVersion: $request->input('client_version'),
            softwareVersion: $request->input('software_version'),
        );

        $payload = $this->installations->toPayload($result['installation']);
        $payload['revalidation_required'] = $result['domain_changed'];

        return $this->success(
            $request,
            $result['domain_changed'] ? 'INSTALLATION_PENDING_REVALIDATION' : 'INSTALLATION_REGISTERED',
            $result['domain_changed']
                ? 'Domain change detected — installation is pending revalidation. Call /installations/verify to confirm.'
                : 'Installation registered.',
            $payload,
        );
    }
}
