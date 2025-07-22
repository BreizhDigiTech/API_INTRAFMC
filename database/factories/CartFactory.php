<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use App\Models\ProductCBD;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Cart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => ProductCBD::factory(),
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }

    /**
     * Create a cart item with large quantity.
     */
    public function largeQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(5, 10),
        ]);
    }

    /**
     * Create a cart item with single quantity.
     */
    public function single(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 1,
        ]);
    }
}
