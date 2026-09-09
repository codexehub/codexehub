<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(2, true).' '.fake()->randomElement(['Platform', 'Suite', 'System']);
        $slug = Str::slug($title);

        return [
            'type' => Product::TYPE_SOFTWARE,
            'product_category_id' => null,
            'icon' => 'bi-box-seam',
            'title' => Str::title($title),
            'slug' => $slug,
            'key' => $slug,
            'tagline' => fake()->sentence(4),
            'short_description' => fake()->sentence(12),
            'description' => fake()->paragraph(),
            'price' => fake()->randomElement([null, 299, 650, 1200]),
            'price_unit' => 'one-time',
            'version' => fake()->numerify('#.#'),
            'status' => 'active',
            'featured' => false,
            'sort_order' => 0,
        ];
    }

    public function physical(): static
    {
        return $this->state(fn () => ['type' => Product::TYPE_PHYSICAL, 'icon' => 'bi-cpu']);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }
}
