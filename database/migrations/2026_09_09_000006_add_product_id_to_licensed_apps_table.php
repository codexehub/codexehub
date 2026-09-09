<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PROD-001 fold-in — `licensed_apps` is kept as released schema and demoted to a
 * licensing-domain link row onto the canonical `products` entity (same shape as
 * the CUST-001 `license_customers` → `customers` fold-in). The Vendor API keeps
 * resolving on `licensed_apps.slug`; LIC-CP-001 / INST-002 move it onto the
 * product.
 *
 * Backfill links by exact slug/key match only — a `licensed_apps` row with no
 * matching product stays unlinked (nullable) until an admin picks one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licensed_apps', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        DB::table('licensed_apps')->whereNull('product_id')->orderBy('id')->each(function ($app) {
            $productId = DB::table('products')
                ->where('slug', $app->slug)
                ->orWhere('key', $app->slug)
                ->value('id');

            if ($productId !== null) {
                DB::table('licensed_apps')->where('id', $app->id)->update(['product_id' => $productId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('licensed_apps', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
