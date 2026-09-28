<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Installation;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Installation>
 */
class InstallationFactory extends Factory
{
    protected $model = Installation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'product_id' => Product::factory(),
            'licensed_app_package_id' => null,
            'installation_id' => Installation::generateIdentifier(),
            'domain' => fake()->unique()->domainName(),
            'environment' => Installation::ENVIRONMENT_PRODUCTION,
            'vendor_client_version' => '1.0.0',
            'software_version' => '1.0.0',
            'status' => Installation::STATUS_ACTIVE,
            'last_heartbeat_at' => now(),
            'last_verified_at' => now(),
            'last_deployed_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => Installation::STATUS_PENDING,
            'last_heartbeat_at' => null,
            'last_verified_at' => null,
            'last_deployed_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => Installation::STATUS_INACTIVE]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => Installation::STATUS_SUSPENDED]);
    }

    public function environment(string $environment): static
    {
        return $this->state(fn () => ['environment' => $environment]);
    }
}
