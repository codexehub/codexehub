<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PROD-001 fold-in — the existing marketing `products` table is promoted to the
 * SRS §52 control-plane product entity. SRS "name" = `title`; `version`,
 * `status` and `description` are already present. The one missing column is a
 * stable machine `key`: customer packages, offers, releases and the Vendor API
 * bind to it, so it must not follow the editable marketing `slug`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('key')->nullable()->unique()->after('slug');
        });

        // Existing rows: key defaults to the current slug.
        DB::table('products')->whereNull('key')->update(['key' => DB::raw('slug')]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->dropColumn('key');
        });
    }
};
