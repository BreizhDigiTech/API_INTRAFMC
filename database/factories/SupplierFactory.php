<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Supplier;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyName = fake()->company() . ' CBD';
        
        return [
            'name' => $companyName,
            'email' => fake()->unique()->companyEmail(),
            'phone' => '+33 ' . fake()->phoneNumber(),
            'address' => fake()->streetAddress() . ', ' . fake()->postcode() . ' ' . fake()->city(),
            'website' => 'https://' . str_replace(' ', '-', strtolower($companyName)) . '.com',
            'contact_person' => fake()->name(),
            'description' => 'Fournisseur spécialisé dans les produits CBD depuis ' . fake()->numberBetween(2015, 2020),
        ];
    }

    /**
     * Create an inactive supplier.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a premium supplier.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Premium ' . $attributes['name'],
            'description' => '⭐ Fournisseur premium certifié - ' . $attributes['description'],
            'min_order' => fake()->randomFloat(2, 500, 2000),
            'delivery_time' => fake()->numberBetween(1, 7) . ' jours',
        ]);
    }

    /**
     * Create a local supplier.
     */
    public function local(): static
    {
        $frenchCities = ['Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nice', 'Nantes', 'Montpellier', 'Bordeaux'];
        
        return $this->state(fn (array $attributes) => [
            'address' => fake()->streetAddress() . ', ' . fake()->postcode() . ' ' . fake()->randomElement($frenchCities),
            'description' => 'Producteur local français - ' . $attributes['description'],
            'delivery_time' => fake()->numberBetween(1, 5) . ' jours',
        ]);
    }

    /**
     * Create an international supplier.
     */
    public function international(): static
    {
        $countries = [
            ['Switzerland', '+41', 'CHE'],
            ['Netherlands', '+31', 'NLD'], 
            ['Italy', '+39', 'ITA'],
            ['Spain', '+34', 'ESP'],
            ['Germany', '+49', 'DEU']
        ];
        
        $country = fake()->randomElement($countries);
        
        return $this->state(fn (array $attributes) => [
            'phone' => $country[1] . ' ' . fake()->phoneNumber(),
            'address' => fake()->streetAddress() . ', ' . fake()->city() . ', ' . $country[0],
            'tva_number' => $country[2] . fake()->numerify('##########'),
            'description' => 'Fournisseur international (' . $country[0] . ') - ' . $attributes['description'],
            'delivery_time' => fake()->numberBetween(5, 21) . ' jours',
            'min_order' => fake()->randomFloat(2, 1000, 5000),
        ]);
    }
}
