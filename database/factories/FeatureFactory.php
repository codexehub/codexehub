<?php

namespace Database\Factories;

use App\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Feature>
 */
class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'key' => Str::slug($name, '_'),
            'type' => Feature::TYPE_MODULE,
            'description' => fake()->sentence(12),
            'default_price' => fake()->randomElement([0, 500, 1500, 5000]),
            'version' => '1.0.0',
            'status' => Feature::STATUS_ACTIVE,
            'dependencies' => null,
            'config_schema' => null,
            'documentation' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => Feature::STATUS_INACTIVE]);
    }

    public function deprecated(): static
    {
        return $this->state(fn () => ['status' => Feature::STATUS_DEPRECATED]);
    }

    public function type(string $type): static
    {
        return $this->state(fn () => ['type' => $type]);
    }

    /**
     * @param  list<string>  $keys
     */
    public function dependsOn(array $keys): static
    {
        return $this->state(fn () => ['dependencies' => $keys]);
    }

    public function withConfigSchema(): static
    {
        return $this->state(fn () => [
            'config_schema' => [
                'type' => 'object',
                'properties' => [
                    'provider' => ['type' => 'string'],
                ],
                'required' => ['provider'],
            ],
        ]);
    }
}
