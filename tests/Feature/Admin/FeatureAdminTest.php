<?php

namespace Tests\Feature\Admin;

use App\Models\Feature;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FEAT-002 — admin CRUD for the feature catalog, gated by `features.*`, with
 * dependency resolution and config-schema validation.
 */
class FeatureAdminTest extends TestCase
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

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Payment Gateway',
            'type' => 'integration',
            'status' => 'active',
            'version' => '1.0.0',
            'default_price' => 1500,
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.features.index'))->assertRedirect(route('admin.login'));
    }

    public function test_a_role_without_features_view_is_forbidden(): void
    {
        $user = User::factory()->nonAdmin()->asRole('qa')->create();

        $this->actingAs($user)->get(route('admin.features.index'))->assertForbidden();
    }

    public function test_a_view_only_role_can_list_but_not_create(): void
    {
        // developer holds features.view but not features.manage
        $viewer = User::factory()->nonAdmin()->asRole('developer')->create();

        $this->actingAs($viewer)->get(route('admin.features.index'))->assertOk();
        $this->actingAs($viewer)->get(route('admin.features.create'))->assertForbidden();
        $this->actingAs($viewer)->post(route('admin.features.store'), $this->payload())->assertForbidden();
    }

    public function test_create_and_edit_forms_render(): void
    {
        $manager = $this->packageManager();
        $feature = Feature::factory()->create(['name' => 'Wishlist']);

        $this->actingAs($manager)->get(route('admin.features.create'))
            ->assertOk()->assertSee('Configuration Schema');

        $this->actingAs($manager)->get(route('admin.features.edit', $feature))
            ->assertOk()->assertSee('Wishlist', false)->assertSee('name="dependencies[]"', false);
    }

    public function test_package_manager_creates_a_feature_and_key_derives_from_name(): void
    {
        $this->actingAs($this->packageManager())
            ->post(route('admin.features.store'), $this->payload())
            ->assertRedirect(route('admin.features.index'));

        $this->assertDatabaseHas('features', [
            'key' => 'payment_gateway',
            'type' => 'integration',
            'default_price' => 1500,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'entity_type' => Feature::class,
        ]);
    }

    public function test_an_explicit_key_is_slugified_with_underscores_and_unique(): void
    {
        Feature::factory()->create(['key' => 'courier']);

        $this->actingAs($this->packageManager())
            ->post(route('admin.features.store'), $this->payload(['name' => 'Courier', 'key' => 'Courier']))
            ->assertSessionHasErrors('key');

        $this->actingAs($this->packageManager())
            ->post(route('admin.features.store'), $this->payload(['name' => 'SMS Gateway', 'key' => 'SMS Gateway']))
            ->assertRedirect();

        $this->assertDatabaseHas('features', ['key' => 'sms_gateway']);
    }

    public function test_config_schema_must_be_a_valid_json_object(): void
    {
        $manager = $this->packageManager();

        $this->actingAs($manager)
            ->post(route('admin.features.store'), $this->payload(['config_schema' => '{not json']))
            ->assertSessionHasErrors('config_schema');

        $this->actingAs($manager)
            ->post(route('admin.features.store'), $this->payload(['config_schema' => '[1,2,3]']))
            ->assertSessionHasErrors('config_schema');

        $this->actingAs($manager)
            ->post(route('admin.features.store'), $this->payload([
                'name' => 'Courier',
                'config_schema' => '{"type":"object","properties":{"provider":{"type":"string"}}}',
            ]))
            ->assertRedirect();

        $feature = Feature::where('key', 'courier')->firstOrFail();
        $this->assertSame('object', $feature->config_schema['type']);
    }

    public function test_dependencies_reject_unknown_self_and_circular_references(): void
    {
        $manager = $this->packageManager();
        Feature::factory()->create(['key' => 'seo']);
        $analytics = Feature::factory()->create(['key' => 'analytics', 'dependencies' => ['seo']]);

        // unknown key
        $this->actingAs($manager)
            ->post(route('admin.features.store'), $this->payload(['name' => 'Bundle', 'dependencies' => ['ghost']]))
            ->assertSessionHasErrors('dependencies');

        // self dependency
        $this->actingAs($manager)
            ->post(route('admin.features.store'), $this->payload(['name' => 'Seo', 'key' => 'seo_pro', 'dependencies' => ['seo_pro']]))
            ->assertSessionHasErrors('dependencies');

        // circular: seo would depend on analytics which already depends on seo
        $seo = Feature::where('key', 'seo')->firstOrFail();
        $this->actingAs($manager)
            ->put(route('admin.features.update', $seo), $this->payload([
                'name' => 'SEO', 'key' => 'seo', 'dependencies' => ['analytics'],
            ]))
            ->assertSessionHasErrors('dependencies');

        // a valid dependency set persists
        $this->actingAs($manager)
            ->post(route('admin.features.store'), $this->payload(['name' => 'Growth Kit', 'dependencies' => ['seo', 'analytics']]))
            ->assertRedirect();

        $this->assertSame(['seo', 'analytics'], Feature::where('key', 'growth_kit')->firstOrFail()->dependencies);
        $this->assertTrue($analytics->fresh()->exists);
    }

    public function test_a_feature_cannot_be_deleted_while_another_depends_on_it(): void
    {
        $manager = $this->packageManager();
        $seo = Feature::factory()->create(['key' => 'seo']);
        Feature::factory()->create(['key' => 'analytics', 'dependencies' => ['seo']]);

        $this->actingAs($manager)
            ->delete(route('admin.features.destroy', $seo))
            ->assertSessionHasErrors('feature');

        $this->assertDatabaseHas('features', ['key' => 'seo']);

        // once the dependent is gone, delete works
        Feature::where('key', 'analytics')->firstOrFail()->delete();
        $this->actingAs($manager)->delete(route('admin.features.destroy', $seo))->assertRedirect();
        $this->assertDatabaseMissing('features', ['key' => 'seo']);
    }

    public function test_status_can_be_changed_from_the_list(): void
    {
        $feature = Feature::factory()->create(['status' => 'active']);

        $this->actingAs($this->packageManager())
            ->patch(route('admin.features.status', $feature), ['status' => 'deprecated'])
            ->assertRedirect();

        $this->assertSame('deprecated', $feature->fresh()->status);
    }
}
