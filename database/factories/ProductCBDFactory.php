<?php

namespace Database\Factories;

use App\Models\ProductCBD;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductCBD>
 */
class ProductCBDFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ProductCBD::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 5, 200),
            'stock' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Configure the factory to attach categories after creation.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (ProductCBD $product) {
            // Attach a category to the product
            $category = Category::factory()->create();
            $product->categories()->attach($category->id);
        });
    }
}
