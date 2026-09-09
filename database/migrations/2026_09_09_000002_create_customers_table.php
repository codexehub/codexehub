<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The canonical customer entity for the control plane (SRS §52).
     *
     * Distinct from `users` (Codexehub admin staff) and a superset of the legacy
     * licensing-only `license_customers` table — the follow-up migration adds a
     * nullable `customer_id` to `license_customers` and backfills it, so every
     * licensing buyer becomes a real customer without touching released schema.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone', 50)->nullable();
            $table->string('status', 20)->default('prospect')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
