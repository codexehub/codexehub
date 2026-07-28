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
        Schema::table('portfolios', function (Blueprint $table) {
            $table->string('industry')->nullable()->after('client_name');
            $table->date('completed_date')->nullable()->after('team_size');
            $table->text('challenge')->nullable()->after('description');
            $table->text('solution')->nullable()->after('challenge');
            $table->string('case_study_file')->nullable()->after('github_url');
            $table->text('testimonial_quote')->nullable()->after('case_study_file');
            $table->string('testimonial_name')->nullable()->after('testimonial_quote');
            $table->string('testimonial_role')->nullable()->after('testimonial_name');
            $table->unsignedTinyInteger('testimonial_rating')->nullable()->after('testimonial_role');
            $table->string('testimonial_avatar')->nullable()->after('testimonial_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn([
                'industry', 'completed_date', 'challenge', 'solution', 'case_study_file',
                'testimonial_quote', 'testimonial_name', 'testimonial_role', 'testimonial_rating', 'testimonial_avatar',
            ]);
        });
    }
};
