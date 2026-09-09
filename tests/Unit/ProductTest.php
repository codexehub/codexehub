<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_key_defaults_to_the_slug_on_create(): void
    {
        $product = Product::factory()->create(['slug' => 'my-product', 'key' => null]);

        $this->assertSame('my-product', $product->key);
    }

    public function test_an_explicit_key_is_kept(): void
    {
        $product = Product::factory()->create(['slug' => 'codexecommerce', 'key' => 'ecommerce']);

        $this->assertSame('ecommerce', $product->fresh()->key);
    }

    public function test_key_is_unique(): void
    {
        Product::factory()->create(['key' => 'ecommerce']);

        $this->expectException(QueryException::class);
        Product::factory()->create(['key' => 'ecommerce']);
    }

    public function test_type_scopes_filter_software_and_physical(): void
    {
        Product::factory()->count(2)->create();
        Product::factory()->physical()->create();

        $this->assertSame(2, Product::software()->count());
        $this->assertSame(1, Product::physical()->count());
    }
}
