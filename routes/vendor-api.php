<?php

use App\Http\Controllers\Api\Vendor\Installation\HeartbeatInstallationController;
use App\Http\Controllers\Api\Vendor\Installation\RegisterInstallationController;
use App\Http\Controllers\Api\Vendor\Installation\ShowInstallationController;
use App\Http\Controllers\Api\Vendor\Installation\VerifyInstallationController;
use App\Http\Controllers\Api\Vendor\License\LicenseStatusController;
use App\Http\Controllers\Api\Vendor\License\RenewLicenseController;
use App\Http\Controllers\Api\Vendor\License\VerifyLicenseController;
use App\Http\Controllers\Api\Vendor\PingController;
use Illuminate\Support\Facades\Route;

/**
 * Vendor API (SRS §64 prefix `/api/vendor/v1`) — required from routes/api.php
 * under that prefix. Every route here runs through `vendor-api.throttle`
 * (SRS §120 rate limiting) then `vendor-api.verify` (SRS §63/§70 signature +
 * timestamp/replay-window + request-id dedupe) before its
 * controller/FormRequest runs (APISEC-002). `/ping` is the APISEC-001
 * skeleton's diagnostic route; real endpoints are added by their own tasks
 * (SYNC-001, UPD-001, DEP-004) using the same VendorApiController/Request
 * base — each should give its route a `vendor-api.*` name and, per SRS §120,
 * its own `config('vendor_api.rate_limits')` entry.
 */
Route::middleware(['vendor-api.throttle', 'vendor-api.verify'])->group(function () {
    Route::post('ping', PingController::class)->name('vendor-api.ping');

    // SRS §65 (INST-002) — GET /installations/{id} deliberately takes a
    // plain string, not implicit route-model-binding: a missing model would
    // 404 via Laravel's default exception rendering instead of the standard
    // Vendor API error envelope (see ShowInstallationController).
    Route::prefix('installations')->name('vendor-api.installations.')->group(function () {
        Route::post('register', RegisterInstallationController::class)->name('register');
        Route::post('verify', VerifyInstallationController::class)->name('verify');
        Route::post('heartbeat', HeartbeatInstallationController::class)->name('heartbeat');
        Route::get('{installationId}', ShowInstallationController::class)->name('show');
    });

    // SRS §66 (LIC-CP-002) — GET /licenses/{id} is deliberately not built
    // here; see doc/backend/API-CONTRACT.md's endpoint table for the scope note.
    Route::prefix('licenses')->name('vendor-api.licenses.')->group(function () {
        Route::post('verify', VerifyLicenseController::class)->name('verify');
        Route::post('renew', RenewLicenseController::class)->name('renew');
        Route::post('status', LicenseStatusController::class)->name('status');
    });
});
