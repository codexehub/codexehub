<?php

use App\Http\Controllers\Api\LicenseApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/license')->group(function () {
    Route::post('/activate', [LicenseApiController::class, 'activate']);
    Route::post('/verify', [LicenseApiController::class, 'verify']);
});

// SRS §64 — the Vendor API proper (installations/licenses/packages/updates/
// deployments), superseding the ad-hoc /v1/license/* pair above over time
// (see LIC-CP-001/002, INST-002). Kept in its own file — see routes/vendor-api.php.
Route::prefix('vendor/v1')->group(base_path('routes/vendor-api.php'));
