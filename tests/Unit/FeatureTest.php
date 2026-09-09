<?php

namespace Tests\Unit;

use App\Models\Feature;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_key_is_derived_from_the_name_when_blank(): void
    {
        $feature = Feature::factory()->create(['name' => 'Payment Gateway', 'key' => null]);

        $this->assertSame('payment_gateway', $feature->key);
    }

    public function test_an_explicit_key_is_kept(): void
    {
        $feature = Feature::factory()->create(['name' => 'Facebook Pixel', 'key' => 'fb_pixel']);

        $this->assertSame('fb_pixel', $feature->fresh()->key);
    }

    public function test_key_is_unique(): void
    {
        Feature::factory()->create(['key' => 'courier']);

        $this->expectException(QueryException::class);
        Feature::factory()->create(['key' => 'courier']);
    }

    public function test_dependencies_and_config_schema_cast_to_arrays(): void
    {
        $feature = Feature::factory()
            ->dependsOn(['payment_gateway'])
            ->withConfigSchema()
            ->create();

        $fresh = $feature->fresh();
        $this->assertSame(['payment_gateway'], $fresh->dependencies);
        $this->assertSame('object', $fresh->config_schema['type']);
    }

    public function test_default_price_defaults_to_zero_and_casts_with_two_decimals(): void
    {
        $feature = new Feature(['name' => 'Wishlist', 'key' => 'wishlist']);
        $feature->save();

        $this->assertSame('0.00', $feature->fresh()->default_price);
    }

    public function test_scopes_filter_by_status_and_type(): void
    {
        Feature::factory()->count(2)->create();
        Feature::factory()->inactive()->create();
        Feature::factory()->type(Feature::TYPE_INTEGRATION)->create();

        $this->assertSame(3, Feature::active()->count());
        $this->assertSame(1, Feature::status(Feature::STATUS_INACTIVE)->count());
        $this->assertSame(1, Feature::type(Feature::TYPE_INTEGRATION)->count());
    }

    public function test_dependency_features_resolves_stored_keys_to_catalog_rows(): void
    {
        $gateway = Feature::factory()->create(['key' => 'payment_gateway']);
        $feature = Feature::factory()->dependsOn(['payment_gateway', 'missing_key'])->create();

        $resolved = $feature->dependencyFeatures();

        $this->assertCount(1, $resolved);
        $this->assertTrue($resolved->first()->is($gateway));
    }

    public function test_dependency_features_is_empty_without_dependencies(): void
    {
        $this->assertTrue(Feature::factory()->create()->dependencyFeatures()->isEmpty());
    }
}
