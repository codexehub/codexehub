<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FEAT-001 — the reusable feature catalog (SRS §7, §52).
 *
 * Distinct-table decision (recorded): the existing `product_features` /
 * `service_features` tables are per-product / per-service marketing bullet-list
 * repeater rows (icon + text, no key, no price, no schema) owned by a single
 * marketing Product / Service. They are unrelated to a reusable entitlement
 * catalog, so FEAT-001 gets its own `features` table — no fold-in.
 *
 * A `feature` is picked into a customer package (Phase 2 `customer_package_features`),
 * priced per customer, configured against `config_schema`, versioned, and synced
 * to the customer installation by machine `key` over the Vendor API.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Stable machine identifier — packages, sync payloads and the Vendor
            // API bind to this, never the display name (SRS §52).
            $table->string('key')->unique();
            $table->string('type', 40)->default('module')->index();
            $table->text('description')->nullable();
            $table->decimal('default_price', 12, 2)->default(0);
            $table->string('version', 20)->default('1.0.0');
            $table->string('status', 20)->default('active')->index();
            // Array of feature keys this feature requires (SRS §7 "Dependencies").
            $table->json('dependencies')->nullable();
            // JSON-schema describing the shape of this feature's per-package
            // configuration; values are validated against it (SRS §79).
            $table->json('config_schema')->nullable();
            $table->text('documentation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
