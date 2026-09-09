<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\LicenseCustomer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LicenseCustomer>
 */
class LicenseCustomerFactory extends Factory
{
    protected $model = LicenseCustomer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'notes' => null,
        ];
    }

    /**
     * A pre-fold-in row with no canonical customer yet.
     */
    public function orphan(): static
    {
        return $this->state(fn () => ['customer_id' => null]);
    }
}
