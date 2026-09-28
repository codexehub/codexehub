<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Installation;
use App\Models\LicensedApp;
use App\Models\LicensedAppPackage;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InstallationTest extends TestCase
{
    use RefreshDatabase;

    public function test_installation_id_is_auto_generated_when_blank(): void
    {
        $installation = Installation::factory()->create(['installation_id' => null]);

        $this->assertMatchesRegularExpression('/^INS-[A-Z0-9]{8}$/', $installation->installation_id);
    }

    public function test_an_explicit_installation_id_is_kept(): void
    {
        $installation = Installation::factory()->create(['installation_id' => 'INS-CUSTOM1']);

        $this->assertSame('INS-CUSTOM1', $installation->fresh()->installation_id);
    }

    public function test_installation_id_is_unique(): void
    {
        Installation::factory()->create(['installation_id' => 'INS-DUPLICAT']);

        $this->expectException(QueryException::class);
        Installation::factory()->create(['installation_id' => 'INS-DUPLICAT']);
    }

    public function test_status_and_environment_default_when_blank(): void
    {
        $installation = Installation::factory()->create(['status' => null, 'environment' => null]);

        $this->assertSame(Installation::STATUS_PENDING, $installation->status);
        $this->assertSame(Installation::ENVIRONMENT_PRODUCTION, $installation->environment);
    }

    public function test_heartbeat_verified_and_deployed_timestamps_cast_to_datetime(): void
    {
        $installation = Installation::factory()->create();

        $fresh = $installation->fresh();
        $this->assertInstanceOf(Carbon::class, $fresh->last_heartbeat_at);
        $this->assertInstanceOf(Carbon::class, $fresh->last_verified_at);
        $this->assertInstanceOf(Carbon::class, $fresh->last_deployed_at);
    }

    public function test_customer_and_product_relations_resolve(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $installation = Installation::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->assertTrue($installation->customer->is($customer));
        $this->assertTrue($installation->product->is($product));
    }

    public function test_package_relation_is_a_stand_in_for_the_licensed_app_package(): void
    {
        $app = LicensedApp::create(['name' => 'Ecommerce', 'is_active' => true]);
        $package = LicensedAppPackage::create([
            'licensed_app_id' => $app->id,
            'name' => 'Standard',
            'price' => 199,
            'duration_days' => 365,
            'max_activations' => 1,
        ]);

        $installation = Installation::factory()->create(['licensed_app_package_id' => $package->id]);

        $this->assertTrue($installation->package->is($package));
    }

    public function test_scopes_filter_by_status_and_environment(): void
    {
        Installation::factory()->count(2)->create();
        Installation::factory()->suspended()->create();
        Installation::factory()->environment(Installation::ENVIRONMENT_STAGING)->create();

        $this->assertSame(3, Installation::active()->count());
        $this->assertSame(1, Installation::status(Installation::STATUS_SUSPENDED)->count());
        $this->assertSame(1, Installation::environment(Installation::ENVIRONMENT_STAGING)->count());
    }

    public function test_route_key_name_is_the_business_installation_id(): void
    {
        $this->assertSame('installation_id', (new Installation)->getRouteKeyName());
    }
}
