<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('license_key', 64)->unique();
            $table->foreignId('license_customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('licensed_app_id')->constrained()->cascadeOnDelete();
            $table->foreignId('licensed_app_package_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'active', 'suspended', 'expired', 'revoked'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
