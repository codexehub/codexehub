<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LIC-CP-001 — extend `licenses` toward the SRS §59 shape + the §39 lifecycle
     * states, additively (WORK-INSTRUCTIONS §16).
     *
     * `license_customer_id` / `licensed_app_id` / `licensed_app_package_id` stay
     * as-is — SRS §59's `customer_id` / `package_id` FKs (onto `customers` /
     * `customer_packages`) aren't added here because `customer_packages` doesn't
     * exist yet (Phase 2 — PKG-001); the CUST-002 bridge already lets `licenses`
     * reach a canonical `Customer` via `license_customer_id`. `installations`
     * doesn't exist yet either (Phase 6 — INST-001), so `installation_id` is
     * stored as a plain business-key string for now, not a FK — INST-001 can
     * turn it into one additively later.
     */
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->string('installation_id', 64)->nullable()->after('licensed_app_package_id')->index();
            $table->timestamp('issued_at')->nullable()->after('status');
            $table->timestamp('starts_at')->nullable()->after('issued_at');
            $table->unsignedSmallInteger('grace_period_days')->nullable()->after('expires_at');
            $table->date('next_payment_due')->nullable()->after('grace_period_days')->index();
            $table->string('ownership_status', 30)->nullable()->after('next_payment_due');
        });

        // Widen `status` from the released 5-value enum to the SRS §39 9-state
        // lifecycle (pending/active/payment_due/grace_period/suspended/
        // reactivated/completed/revoked/expired). Laravel 11+ column ->change()
        // needs no doctrine/dbal.
        Schema::table('licenses', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'installation_id', 'issued_at', 'starts_at', 'grace_period_days',
                'next_payment_due', 'ownership_status',
            ]);
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->enum('status', ['pending', 'active', 'suspended', 'expired', 'revoked'])->default('pending')->change();
        });
    }
};
