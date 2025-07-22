<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        
        return [
            'name' => $firstName . ' ' . $lastName,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_admin' => false,
            'is_active' => true,
            'avatar' => 'avatars/default_' . fake()->numberBetween(1, 8) . '.jpg',
            'phone' => '+33 ' . fake()->phoneNumber(),
            'address' => fake()->streetAddress() . ', ' . fake()->postcode() . ' ' . fake()->city(),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
            'name' => 'Admin ' . fake()->lastName(),
            'avatar' => 'avatars/admin_' . fake()->numberBetween(1, 3) . '.jpg',
        ]);
    }

    /**
     * Create an inactive user.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a premium customer.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'VIP ' . fake()->name(),
            'avatar' => 'avatars/vip_' . fake()->numberBetween(1, 5) . '.jpg',
        ]);
    }
}
