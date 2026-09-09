<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'company_name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => Customer::STATUS_ACTIVE,
            'notes' => null,
        ];
    }

    public function prospect(): static
    {
        return $this->state(fn () => ['status' => Customer::STATUS_PROSPECT]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => Customer::STATUS_INACTIVE]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => Customer::STATUS_SUSPENDED]);
    }
}
