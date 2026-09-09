<?php

namespace Tests\Feature\Foundation;

use App\Models\LicensedApp;
use App\Models\Product;
use Database\Seeders\LicensingSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * PROD-001 — the marketing `products` table is the canonical SRS §52 control-plane
 * product; `licensed_apps` is demoted to a nullable link row onto it.
 */
class ControlPlaneProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_table_carries_a_machine_key(): void
    {
        $this->assertTrue(Schema::hasColumn('products', 'key'));
    }

    public function test_licensed_apps_link_to_a_product(): void
    {
        $this->assertTrue(Schema::hasColumn('licensed_apps', 'product_id'));

        $product = Product::factory()->create(['key' => 'ecommerce']);
        $app = LicensedApp::create([
            'product_id' => $product->id,
            'name' => 'Ecommerce Laravel',
            'slug' => 'ecommerce-laravel',
            'is_active' => true,
        ]);

        $this->assertTrue($app->product->is($product));
        $this->assertTrue($product->licensedApps->contains($app));
    }

    public function test_a_licensed_app_without_a_matching_product_is_unlinked(): void
    {
        $app = LicensedApp::create(['name' => 'Orphan App', 'slug' => 'orphan-app', 'is_active' => true]);

        $this->assertNull($app->product_id);
    }

    public function test_deleting_a_product_nulls_the_link_but_keeps_the_licensed_app(): void
    {
        $product = Product::factory()->create();
        $app = LicensedApp::create([
            'product_id' => $product->id,
            'name' => 'Bridged App',
            'slug' => 'bridged-app',
            'is_active' => true,
        ]);

        $product->delete();

        $this->assertDatabaseHas('licensed_apps', ['id' => $app->id, 'product_id' => null]);
    }

    public function test_seeders_create_and_link_the_ecommerce_product(): void
    {
        $this->seed(ProductSeeder::class);
        $this->seed(LicensingSeeder::class);

        $ecommerce = Product::where('key', 'ecommerce')->first();
        $this->assertNotNull($ecommerce);
        $this->assertSame(Product::TYPE_SOFTWARE, $ecommerce->type);

        $app = LicensedApp::where('slug', 'ecommerce-laravel')->first();
        $this->assertSame($ecommerce->id, $app->product_id);
    }
}
