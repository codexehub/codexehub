<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fold `license_customers` into the new canonical `customers` entity —
     * additively (WORK-INSTRUCTIONS §8/§16). Decision:
     *
     *  - `license_customers` stays as released schema; no column is renamed or
     *    dropped. It becomes the licensing-domain link row between a `customer`
     *    and its `licenses`.
     *  - A nullable `customer_id` FK is added and backfilled: each existing
     *    `license_customers` row is promoted to a `customers` row (deduplicated
     *    by email) and linked.
     *  - New code targets `customers`; the Admin\Licensing screens keep working
     *    unchanged and can migrate to the FK later (CUST-002).
     */
    public function up(): void
    {
        Schema::table('license_customers', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        foreach (DB::table('license_customers')->whereNull('customer_id')->get() as $legacy) {
            $customerId = DB::table('customers')->where('email', $legacy->email)->value('id');

            if ($customerId === null) {
                $customerId = DB::table('customers')->insertGetId([
                    'name' => $legacy->name,
                    'email' => $legacy->email,
                    'phone' => $legacy->phone,
                    'status' => 'active',
                    'notes' => $legacy->notes,
                    'created_at' => $legacy->created_at ?? now(),
                    'updated_at' => $legacy->updated_at ?? now(),
                ]);
            }

            DB::table('license_customers')->where('id', $legacy->id)->update(['customer_id' => $customerId]);
        }
    }

    public function down(): void
    {
        Schema::table('license_customers', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};
