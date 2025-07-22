<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CbdArrival;
use App\Models\Supplier;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CbdArrival>
 */
class CbdArrivalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'validated'];
        $status = fake()->randomElement($statuses);
        
        $createdAt = fake()->dateTimeBetween('-6 months', 'now');
        
        return [
            'amount' => fake()->randomFloat(2, 500, 10000),
            'status' => $status,
            'created_at' => $createdAt,
            'updated_at' => fake()->dateTimeBetween($createdAt, 'now'),
        ];
    }

    private function generateNotes(): string
    {
        $noteTypes = [
            'Arrivage de qualité premium conforme aux spécifications',
            'Livraison en parfait état, emballage soigné',
            'Produits certifiés bio, analyses conformes',
            'Retard de livraison compensé par un geste commercial',
            'Nouvelle variété à tester, échantillons inclus',
            'Commande urgente traitée en priorité',
            'Conditionnement spécial demandé respecté',
            'Transport réfrigéré maintenu pendant le voyage'
        ];
        
        return fake()->randomElement($noteTypes);
    }

    private function generateInvoiceNumber(): string
    {
        return 'FAC-' . fake()->year() . '-' . fake()->numberBetween(1000, 9999);
    }

    /**
     * Create a pending arrival.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'validated_at' => null,
            'validated_by' => null,
        ]);
    }

    /**
     * Create a validated arrival.
     */
    public function validated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'validated',
            'validated_at' => fake()->dateTimeBetween($attributes['created_at'], 'now'),
            'validated_by' => 'Admin ' . fake()->lastName(),
        ]);
    }

    /**
     * Create a cancelled arrival.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'validated_at' => null,
            'validated_by' => null,
            'notes' => 'Arrivage annulé - ' . fake()->randomElement([
                'Non-conformité qualité',
                'Retard de livraison excessif',
                'Produits endommagés',
                'Erreur de commande'
            ]),
        ]);
    }

    /**
     * Create a large arrival.
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_cost' => fake()->randomFloat(2, 5000, 25000),
            'notes' => 'Commande importante - ' . $attributes['notes'],
        ]);
    }

    /**
     * Create a recent arrival.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
