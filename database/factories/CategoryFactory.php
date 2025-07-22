<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Huiles CBD',
            'Fleurs CBD', 
            'E-liquides CBD',
            'Cosmétiques CBD',
            'Comestibles CBD',
            'Résines CBD',
            'Accessoires CBD',
            'Capsules CBD',
            'Baumes CBD',
            'Thés CBD'
        ];

        $categoryName = fake()->unique()->randomElement($categories);
        
        return [
            'name' => $categoryName,
            'description' => $this->generateDescription($categoryName),
        ];
    }

    private function generateDescription(string $categoryName): string
    {
        $descriptions = [
            'Huiles CBD' => 'Découvrez notre gamme d\'huiles CBD premium, extraites selon les méthodes les plus pures. Différentes concentrations disponibles.',
            'Fleurs CBD' => 'Fleurs de CBD cultivées biologiquement, séchées et triées à la main. Variétés indica et sativa disponibles.',
            'E-liquides CBD' => 'E-liquides au CBD pour cigarette électronique. Saveurs naturelles et artificielles, dosages variés.',
            'Cosmétiques CBD' => 'Soins cosmétiques enrichis au CBD. Crèmes, baumes et sérums pour le visage et le corps.',
            'Comestibles CBD' => 'Produits alimentaires infusés au CBD. Bonbons, chocolats, miels et boissons.',
            'Résines CBD' => 'Hash et résines de CBD artisanales. Extractions traditionnelles et modernes.',
            'Accessoires CBD' => 'Tout l\'équipement pour consommer le CBD : vaporisateurs, pipes, grinders.',
            'Capsules CBD' => 'Capsules de CBD faciles à doser. Absorption lente et effet prolongé.',
            'Baumes CBD' => 'Baumes topiques au CBD pour application locale. Soulagement ciblé.',
            'Thés CBD' => 'Infusions et thés au CBD. Mélanges relaxants pour tous les moments de la journée.'
        ];

        return $descriptions[$categoryName] ?? 'Produits CBD de qualité premium dans la catégorie ' . $categoryName;
    }
}
