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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();

            // Step 1 — Personal Information
            $table->string('full_name');
            $table->string('company_name')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('country');
            $table->string('city')->nullable();
            $table->string('language')->default('English');

            // Step 2 — Project Information
            $table->string('service');
            $table->string('project_type')->nullable();
            $table->text('description');
            $table->string('budget')->nullable();
            $table->string('timeline')->nullable();

            // Step 3 — Meeting Preferences
            $table->string('contact_method');
            $table->date('meeting_date')->nullable();
            $table->string('meeting_time')->nullable();
            $table->string('timezone')->nullable();
            $table->string('attachment')->nullable();

            // Admin management
            $table->string('status')->default('new');
            $table->string('priority')->default('medium');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            // Meta
            $table->string('source')->default('website');
            $table->string('ip_address')->nullable();
            $table->text('browser')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
