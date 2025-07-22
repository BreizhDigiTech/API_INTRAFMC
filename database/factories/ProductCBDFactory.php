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
        $productTypes = [
            'Huile CBD', 'Fleur CBD', 'E-liquide CBD', 'Crème CBD', 'Baume CBD',
            'Capsule CBD', 'Résine CBD', 'Thé CBD', 'Bonbon CBD', 'Chocolat CBD'
        ];
        
        $concentrations = ['5%', '10%', '15%', '20%', '25%', '30%'];
        $varieties = [
            'OG Kush', 'Amnesia Haze', 'Purple Haze', 'Lemon Haze', 'White Widow',
            'Jack Herer', 'Northern Lights', 'Skunk', 'Cheese', 'Blueberry'
        ];
        
        $productType = fake()->randomElement($productTypes);
        $name = $productType;
        
        // Ajouter des détails selon le type
        if (str_contains($productType, 'Huile') || str_contains($productType, 'E-liquide')) {
            $name .= ' ' . fake()->randomElement($concentrations);
        } elseif (str_contains($productType, 'Fleur') || str_contains($productType, 'Résine')) {
            $name .= ' ' . fake()->randomElement($varieties);
        }
        
        $price = $this->generateRealisticPrice($productType);
        $stock = fake()->numberBetween(0, 500);
        
        return [
            'name' => $name,
            'description' => $this->generateDescription($productType, $name),
            'price' => $price,
            'stock' => $stock,
            'images' => $this->generateImages($productType),
            'analysis_file' => $this->generateAnalysisFile($name),
            'category_id' => function() {
                // Utiliser une catégorie existante ou en créer une seule fois
                return Category::inRandomOrder()->first()?->id ?? Category::factory()->create()->id;
            },
        ];
    }

    private function generateRealisticPrice(string $productType): float
    {
        $basePrices = [
            'Huile CBD' => [25, 150],
            'Fleur CBD' => [5, 25],
            'E-liquide CBD' => [15, 50],
            'Crème CBD' => [20, 80],
            'Baume CBD' => [15, 60],
            'Capsule CBD' => [30, 120],
            'Résine CBD' => [8, 40],
            'Thé CBD' => [10, 30],
            'Bonbon CBD' => [5, 25],
            'Chocolat CBD' => [8, 35]
        ];
        
        $range = $basePrices[$productType] ?? [10, 50];
        return fake()->randomFloat(2, $range[0], $range[1]);
    }

    private function generateDescription(string $productType, string $name): string
    {
        $baseDescriptions = [
            'Huile CBD' => 'Huile de CBD premium extraite par CO2 supercritique. ',
            'Fleur CBD' => 'Fleur de CBD cultivée biologiquement en indoor. ',
            'E-liquide CBD' => 'E-liquide au CBD compatible avec toutes e-cigarettes. ',
            'Crème CBD' => 'Crème topique au CBD pour application locale. ',
            'Baume CBD' => 'Baume réparateur enrichi au CBD naturel. ',
            'Capsule CBD' => 'Capsules de CBD faciles à doser et discrètes. ',
            'Résine CBD' => 'Résine de CBD artisanale de haute qualité. ',
            'Thé CBD' => 'Infusion relaxante au CBD biologique. ',
            'Bonbon CBD' => 'Bonbons gélifiés infusés au CBD naturel. ',
            'Chocolat CBD' => 'Chocolat artisanal enrichi au CBD premium. '
        ];
        
        $base = $baseDescriptions[$productType] ?? 'Produit CBD de qualité premium. ';
        
        $additionalInfo = [
            'Extraction respectueuse de l\'environnement.',
            'Testé en laboratoire indépendant.',
            'Sans pesticides ni métaux lourds.',
            'Cultivé en France selon les normes bio.',
            'Effet relaxant et bien-être garanti.',
            'Dosage précis et contrôlé.',
            'Ingrédients naturels sélectionnés.',
            'Fabrication artisanale française.'
        ];
        
        return $base . fake()->randomElement($additionalInfo) . ' ' . fake()->sentence();
    }

    private function generateImages(string $productType): array
    {
        $slug = strtolower(str_replace(' ', '_', $productType));
        $imageCount = fake()->numberBetween(1, 4);
        
        $images = [];
        for ($i = 1; $i <= $imageCount; $i++) {
            $images[] = "products/{$slug}_{$i}.jpg";
        }
        
        return $images;
    }

    private function generateAnalysisFile(string $name): ?string
    {
        // 70% de chance d'avoir un fichier d'analyse
        if (fake()->boolean(70)) {
            $slug = strtolower(str_replace(' ', '_', $name));
            return "analyses/{$slug}_analyse.pdf";
        }
        
        return null;
    }

    /**
     * Configure the factory to attach categories after creation.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (ProductCBD $product) {
            // La catégorie est déjà assignée via category_id
        });
    }

    /**
     * Create a product with low stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => fake()->numberBetween(0, 10),
        ]);
    }

    /**
     * Create a popular product.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '🔥 ' . $attributes['name'],
            'stock' => fake()->numberBetween(100, 500),
            'description' => '⭐ Produit populaire - ' . $attributes['description'],
        ]);
    }

    /**
     * Create a premium product.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Premium ' . $attributes['name'],
            'price' => $attributes['price'] * 1.5, // 50% plus cher
            'description' => '💎 Gamme premium - ' . $attributes['description'],
        ]);
    }

    /**
     * Create a discounted product.
     */
    public function discounted(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '🏷️ ' . $attributes['name'],
            'price' => $attributes['price'] * 0.8, // 20% de réduction
            'description' => '💰 Prix spécial - ' . $attributes['description'],
        ]);
    }
}
