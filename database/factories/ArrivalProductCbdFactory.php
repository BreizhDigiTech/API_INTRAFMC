<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ArrivalProductCbd;
use App\Models\CbdArrival;
use App\Models\ProductCBD;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ArrivalProductCbd>
 */
class ArrivalProductCbdFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'arrival_id' => CbdArrival::factory(),
            'product_id' => ProductCBD::factory(),
            'quantity' => fake()->numberBetween(1, 100),
            'unit_price' => fake()->randomFloat(2, 5.00, 50.00),
        ];
    }

    /**
     * State pour des quantités élevées
     */
    public function highQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(100, 500),
        ]);
    }

    /**
     * State pour des prix premium
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_price' => fake()->randomFloat(2, 50.00, 150.00),
        ]);
    }

    /**
     * State pour des petites quantités
     */
    public function lowQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(1, 10),
        ]);
    }

    /**
     * State pour des prix de gros
     */
    public function wholesale(): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_price' => fake()->randomFloat(2, 2.00, 15.00),
            'quantity' => fake()->numberBetween(50, 200),
        ]);
    }
}
