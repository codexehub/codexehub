<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * INST-001 — SRS §57 `installations`: one row per customer site running a
 * Codexehub product, the entity `INST-002`'s register/verify/heartbeat
 * endpoints will operate on.
 *
 * `customer_id` / `product_id` are real FKs onto the canonical `customers`
 * (CUST-001) / `products` (PROD-001) entities. SRS §57's `package_id` would
 * point at `customer_packages`, which doesn't exist yet (Phase 2 — PKG-001);
 * per WORK-INSTRUCTIONS §8 ("reuse, don't rebuild") this reuses the existing
 * pre-SRS `licensed_app_packages` table as `licensed_app_package_id` — the
 * same stand-in `licenses.licensed_app_package_id` already uses. PKG-001 can
 * add a real `customer_package_id` FK alongside it later without touching
 * this column.
 *
 * Supersedes `license_activations` additively (WORK-INSTRUCTIONS §16):
 * `license_activations` (domain + fingerprint per license) is untouched — it
 * stays what the legacy unsigned `/api/v1/license/activate` endpoint binds a
 * device to. `installations` is the broader SRS-shaped forward path
 * (customer/product/package/environment/versions/heartbeat) `INST-002`'s
 * Vendor API endpoints create/update going forward. Any existing
 * `license_activations` rows are backfilled below so pre-existing real-world
 * activations aren't invisible to the new table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('licensed_app_package_id')->nullable()->constrained('licensed_app_packages')->nullOnDelete();
            $table->string('installation_id', 64)->unique();
            $table->string('domain');
            $table->string('environment', 20)->default('production');
            $table->string('vendor_client_version', 20)->nullable();
            $table->string('software_version', 20)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('last_deployed_at')->nullable();
            $table->timestamps();

            $table->index('domain');
            $table->index('status');
        });

        $this->backfillFromLicenseActivations();
    }

    private function backfillFromLicenseActivations(): void
    {
        if (! Schema::hasTable('license_activations')) {
            return;
        }

        DB::table('license_activations')->orderBy('id')->each(function ($activation) {
            $license = DB::table('licenses')->where('id', $activation->license_id)->first();
            if ($license === null) {
                return;
            }

            $alreadyBackfilled = DB::table('installations')
                ->where('domain', $activation->domain)
                ->where('licensed_app_package_id', $license->licensed_app_package_id)
                ->exists();

            if ($alreadyBackfilled) {
                return;
            }

            $customerId = $license->license_customer_id !== null
                ? DB::table('license_customers')->where('id', $license->license_customer_id)->value('customer_id')
                : null;

            $productId = $license->licensed_app_id !== null
                ? DB::table('licensed_apps')->where('id', $license->licensed_app_id)->value('product_id')
                : null;

            DB::table('installations')->insert([
                'customer_id' => $customerId,
                'product_id' => $productId,
                'licensed_app_package_id' => $license->licensed_app_package_id,
                'installation_id' => $this->generateInstallationKey(),
                'domain' => $activation->domain,
                'environment' => 'production',
                'vendor_client_version' => null,
                'software_version' => null,
                'status' => 'active',
                'last_heartbeat_at' => $activation->last_seen_at,
                'last_verified_at' => $license->last_verified_at,
                'last_deployed_at' => null,
                'created_at' => $activation->created_at ?? now(),
                'updated_at' => $activation->updated_at ?? now(),
            ]);
        });
    }

    private function generateInstallationKey(): string
    {
        do {
            $key = 'INS-'.strtoupper(Str::random(8));
        } while (DB::table('installations')->where('installation_id', $key)->exists());

        return $key;
    }

    public function down(): void
    {
        Schema::dropIfExists('installations');
    }
};
