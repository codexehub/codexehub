<?php

namespace Tests\Unit;

use App\Models\Feature;
use App\Services\Feature\FeatureDependencyResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureDependencyResolverTest extends TestCase
{
    use RefreshDatabase;

    private FeatureDependencyResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new FeatureDependencyResolver;
    }

    public function test_missing_reports_only_unknown_keys(): void
    {
        Feature::factory()->create(['key' => 'payment_gateway']);

        $this->assertSame(['courier'], $this->resolver->missing(['payment_gateway', 'courier']));
        $this->assertSame([], $this->resolver->missing(['payment_gateway']));
        $this->assertSame([], $this->resolver->missing([]));
    }

    public function test_detect_cycle_flags_a_direct_two_node_loop(): void
    {
        Feature::factory()->create(['key' => 'a', 'dependencies' => ['b']]);
        Feature::factory()->create(['key' => 'b']);

        // b now wants to depend on a -> a -> b -> a
        $cycle = $this->resolver->detectCycle('b', ['a']);

        $this->assertNotNull($cycle);
        $this->assertSame($cycle[0], $cycle[count($cycle) - 1]);
    }

    public function test_detect_cycle_flags_a_transitive_loop(): void
    {
        Feature::factory()->create(['key' => 'a', 'dependencies' => ['b']]);
        Feature::factory()->create(['key' => 'b', 'dependencies' => ['c']]);
        Feature::factory()->create(['key' => 'c']);

        $this->assertNotNull($this->resolver->detectCycle('c', ['a']));
    }

    public function test_detect_cycle_returns_null_for_an_acyclic_graph(): void
    {
        Feature::factory()->create(['key' => 'a', 'dependencies' => ['b']]);
        Feature::factory()->create(['key' => 'b']);
        Feature::factory()->create(['key' => 'c']);

        $this->assertNull($this->resolver->detectCycle('c', ['a', 'b']));
    }

    public function test_resolve_returns_the_full_transitive_dependency_set(): void
    {
        Feature::factory()->create(['key' => 'seo']);
        Feature::factory()->create(['key' => 'analytics', 'dependencies' => ['seo']]);
        $feature = Feature::factory()->create(['key' => 'marketing_suite', 'dependencies' => ['analytics']]);

        $resolved = $this->resolver->resolve($feature)->pluck('key')->sort()->values()->all();

        $this->assertSame(['analytics', 'seo'], $resolved);
    }

    public function test_resolve_skips_dangling_keys_and_never_includes_the_feature_itself(): void
    {
        $feature = Feature::factory()->create(['key' => 'x', 'dependencies' => ['gone', 'x']]);

        $this->assertTrue($this->resolver->resolve($feature)->isEmpty());
    }
}
