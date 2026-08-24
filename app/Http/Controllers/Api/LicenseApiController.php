<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicensedApp;
use App\Services\LicenseTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LicenseApiController extends Controller
{
    public function __construct(private LicenseTokenService $tokens)
    {
    }

    /**
     * First-time install call: binds this license to a domain/fingerprint
     * (subject to the package's max_activations) and issues a token.
     */
    public function activate(Request $request): JsonResponse
    {
        return $this->resolve($request);
    }

    /**
     * Periodic heartbeat from an already-activated install: refreshes the
     * token and reports the current status (active/suspended/expired/...).
     */
    public function verify(Request $request): JsonResponse
    {
        return $this->resolve($request);
    }

    private function resolve(Request $request): JsonResponse
    {
        $data = $request->validate([
            'license_key' => ['required', 'string'],
            'product_slug' => ['required', 'string'],
            'domain' => ['required', 'string'],
            'fingerprint' => ['required', 'string'],
        ]);

        $licensedApp = LicensedApp::where('slug', $data['product_slug'])->where('is_active', true)->first();
        if (! $licensedApp) {
            return response()->json(['status' => 'invalid', 'message' => 'Unknown product.'], 404);
        }

        $license = License::where('license_key', $data['license_key'])
            ->where('licensed_app_id', $licensedApp->id)
            ->first();

        if (! $license) {
            return response()->json(['status' => 'invalid', 'message' => 'License key not recognized for this product.'], 404);
        }

        // Status may have simply timed out since it was last checked.
        if ($license->status === 'active' && $license->expires_at !== null && $license->expires_at->isPast()) {
            $license->update(['status' => 'expired']);
        }

        if ($license->status !== 'active') {
            return response()->json([
                'status' => $license->status,
                'message' => match ($license->status) {
                    'pending' => 'License is awaiting payment confirmation.',
                    'suspended' => 'License has been suspended.',
                    'expired' => 'License has expired.',
                    'revoked' => 'License has been revoked.',
                    default => 'License is not active.',
                },
            ], 403);
        }

        $activation = LicenseActivation::where('license_id', $license->id)
            ->where('fingerprint', $data['fingerprint'])
            ->first();

        if (! $activation) {
            $activeCount = LicenseActivation::where('license_id', $license->id)->count();
            if ($activeCount >= $license->package->max_activations) {
                return response()->json([
                    'status' => 'activation_limit_reached',
                    'message' => 'This license is already activated on the maximum number of installs allowed by its package.',
                ], 403);
            }

            $activation = LicenseActivation::create([
                'license_id' => $license->id,
                'domain' => $data['domain'],
                'fingerprint' => $data['fingerprint'],
                'last_seen_at' => Carbon::now(),
            ]);
        } else {
            $activation->update([
                'domain' => $data['domain'],
                'last_seen_at' => Carbon::now(),
            ]);
        }

        $license->update(['last_verified_at' => Carbon::now()]);

        return response()->json([
            'status' => 'active',
            'token' => $this->tokens->issue($license, $data['domain']),
            'package' => [
                'name' => $license->package->name,
                'features' => $license->package->features ?? [],
            ],
            'expires_at' => optional($license->expires_at)->toIso8601String(),
        ]);
    }
}
