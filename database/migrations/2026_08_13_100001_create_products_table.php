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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Software and physical products share one table; the inventory-specific
            // columns (sku, stock, weight...) arrive with the inventory module later.
            $table->string('type')->default('software');
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('icon')->default('bi-box-seam');
            $table->string('image')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->string('short_description', 500);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('price_unit')->nullable();
            $table->string('pricing_note')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('docs_url')->nullable();
            $table->string('version')->nullable();
            $table->string('status')->default('active');
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
