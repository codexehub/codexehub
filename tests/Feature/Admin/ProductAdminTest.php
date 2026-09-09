<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use App\Support\Authorization\PermissionCatalog;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PROD-002 — the marketing Product admin is now gated by the control-plane
 * `products.*` permissions, and exposes the `key` machine identifier.
 */
class ProductAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function packageManager(): User
    {
        return User::factory()->nonAdmin()->asRole('package-manager')->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'software',
            'icon' => 'bi-box-seam',
            'title' => 'CodexeBooks',
            'short_description' => 'Accounting for small teams.',
            'status' => 'active',
        ], $overrides);
    }

    public function test_legacy_product_permission_keys_are_retired(): void
    {
        $keys = PermissionCatalog::all();

        $this->assertNotContains('product.view', $keys);
        $this->assertContains('products.view', $keys);
        $this->assertContains('products.manage', $keys);
    }

    public function test_a_role_without_products_view_is_forbidden(): void
    {
        // finance-manager holds no products.* permission
        $user = User::factory()->nonAdmin()->asRole('finance-manager')->create();

        $this->actingAs($user)->get(route('admin.products.index'))->assertForbidden();
    }

    public function test_view_only_role_cannot_create(): void
    {
        // sales-manager has products.view but not products.manage
        $viewer = User::factory()->nonAdmin()->asRole('sales-manager')->create();

        $this->actingAs($viewer)->get(route('admin.products.index'))->assertOk();
        $this->actingAs($viewer)->get(route('admin.products.create'))->assertForbidden();
        $this->actingAs($viewer)->post(route('admin.products.store'), $this->validPayload())->assertForbidden();
    }

    public function test_package_manager_creates_a_product_and_key_derives_from_slug(): void
    {
        $this->actingAs($this->packageManager())
            ->post(route('admin.products.store'), $this->validPayload(['title' => 'Codexe Books', 'slug' => 'codexe-books']))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['slug' => 'codexe-books', 'key' => 'codexe-books']);
    }

    public function test_an_explicit_key_is_slugified_and_must_be_unique(): void
    {
        Product::factory()->create(['key' => 'ecommerce', 'slug' => 'ecommerce']);

        $this->actingAs($this->packageManager())
            ->post(route('admin.products.store'), $this->validPayload(['key' => 'Ecommerce']))
            ->assertSessionHasErrors('key');

        $this->actingAs($this->packageManager())
            ->post(route('admin.products.store'), $this->validPayload(['title' => 'CRM', 'key' => 'Codexe CRM']))
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['key' => 'codexe-crm']);
    }

    public function test_editing_keeps_the_key_when_the_field_is_left_blank(): void
    {
        $product = Product::factory()->create(['key' => 'locked-key', 'slug' => 'locked-key']);

        $this->actingAs($this->packageManager())
            ->put(route('admin.products.update', $product), $this->validPayload([
                'title' => $product->title,
                'slug' => $product->slug,
                'key' => '',
            ]))
            ->assertRedirect();

        $this->assertSame('locked-key', $product->fresh()->key);
    }
}
