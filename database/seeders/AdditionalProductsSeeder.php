<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCBD;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class AdditionalProductsSeeder extends Seeder
{
    /**
     * Génère 150 produits CBD supplémentaires
     */
    public function run(): void
    {
        $this->command->info('🌿 Génération de 150 produits CBD supplémentaires...');

        // Récupérer les catégories et fournisseurs existants
        $categories = Category::all();
        $suppliers = Supplier::all();

        if ($categories->isEmpty()) {
            $this->command->error('❌ Aucune catégorie trouvée. Veuillez d\'abord exécuter le DevelopmentDataSeeder.');
            return;
        }

        if ($suppliers->isEmpty()) {
            $this->command->error('❌ Aucun fournisseur trouvé. Veuillez d\'abord exécuter le DevelopmentDataSeeder.');
            return;
        }

        // Templates de produits variés
        $productTemplates = [
            // Huiles CBD
            'Huile CBD {percentage}% {type}' => [
                'description' => 'Huile de CBD {type} dosée à {percentage}%. Extraction CO2 supercritique pour une qualité optimale.',
                'price_range' => [19.90, 120.00],
                'stock_range' => [50, 200],
                'category_keywords' => ['huile', 'cbd'],
                'images' => [
                    'https://example.com/huile-cbd-{percentage}.jpg',
                    'https://example.com/huile-cbd-{percentage}-2.jpg'
                ]
            ],
            
            // Fleurs CBD
            '{strain} CBD' => [
                'description' => 'Fleur de CBD {strain} cultivée en intérieur. Taux de CBD élevé, THC < 0.2%.',
                'price_range' => [6.50, 15.90],
                'stock_range' => [100, 1000],
                'category_keywords' => ['fleur', 'indoor'],
                'images' => [
                    'https://example.com/fleur-{strain}.jpg',
                    'https://example.com/fleur-{strain}-bud.jpg'
                ]
            ],
            
            // Résines CBD
            'Résine {hashtype} CBD' => [
                'description' => 'Résine de CBD {hashtype} de qualité premium. Texture {texture}, arômes authentiques.',
                'price_range' => [4.90, 12.90],
                'stock_range' => [200, 800],
                'category_keywords' => ['résine', 'hash'],
                'images' => [
                    'https://example.com/resine-{hashtype}.jpg',
                    'https://example.com/resine-{hashtype}-texture.jpg'
                ]
            ],
            
            // E-liquides CBD
            'E-liquide CBD {flavor} {mg}mg' => [
                'description' => 'E-liquide au CBD saveur {flavor}. Dosage {mg}mg de CBD par flacon de 10ml.',
                'price_range' => [12.90, 35.90],
                'stock_range' => [80, 300],
                'category_keywords' => ['e-liquide', 'vape'],
                'images' => [
                    'https://example.com/eliquide-{flavor}.jpg',
                    'https://example.com/eliquide-{flavor}-bottle.jpg'
                ]
            ],
            
            // Cosmétiques CBD
            '{cosmetictype} au CBD' => [
                'description' => '{cosmetictype} enrichi au CBD. Propriétés hydratantes et apaisantes pour la peau.',
                'price_range' => [15.90, 45.90],
                'stock_range' => [30, 150],
                'category_keywords' => ['cosmétique', 'soin'],
                'images' => [
                    'https://example.com/cosmetique-{cosmetictype}.jpg',
                    'https://example.com/cosmetique-{cosmetictype}-2.jpg'
                ]
            ],
            
            // Accessoires
            '{accessory}' => [
                'description' => '{accessory} de qualité premium. Parfait pour une consommation optimale.',
                'price_range' => [8.90, 89.90],
                'stock_range' => [20, 100],
                'category_keywords' => ['accessoire', 'matériel'],
                'images' => [
                    'https://example.com/accessoire-{accessory}.jpg'
                ]
            ]
        ];

        // Données pour remplir les templates
        $data = [
            'percentage' => ['2.5', '5', '10', '15', '20', '30', '40'],
            'type' => ['Full Spectrum', 'Broad Spectrum', 'Isolat', 'Premium', 'Bio', 'Naturel'],
            'strain' => [
                'OG Kush', 'Amnesia Haze', 'White Widow', 'AK-47', 'Bubble Gum',
                'Lemon Haze', 'Purple Haze', 'Gorilla Glue', 'Girl Scout Cookies',
                'Blue Dream', 'Sour Diesel', 'Jack Herer', 'Northern Lights',
                'Granddaddy Purple', 'Green Crack', 'Strawberry Cough',
                'Pineapple Express', 'Skywalker OG', 'Wedding Cake', 'Gelato',
                'Zkittlez', 'Mimosa', 'Runtz', 'Cookies', 'Tangie'
            ],
            'hashtype' => [
                'Afghan', 'Marocain', 'Libanais', 'Indien', 'Népalais',
                'Caramelo', 'Bubble Hash', 'Charas', 'Ketama', 'Polm'
            ],
            'texture' => ['malléable', 'friable', 'compacte', 'souple', 'crémeuse'],
            'flavor' => [
                'Menthe', 'Fraise', 'Vanille', 'Mangue', 'Citron',
                'Myrtille', 'Pomme Verte', 'Pêche', 'Ananas', 'Cerise',
                'Orange', 'Banane', 'Kiwi', 'Pastèque', 'Coconut'
            ],
            'mg' => ['100', '200', '300', '500', '1000'],
            'cosmetictype' => [
                'Crème hydratante', 'Baume apaisant', 'Sérum anti-âge',
                'Masque purifiant', 'Huile de massage', 'Gel douche',
                'Shampoing', 'Après-shampoing', 'Crème de nuit',
                'Contour des yeux', 'Baume à lèvres'
            ],
            'accessory' => [
                'Grinder en aluminium', 'Pipe en verre', 'Vaporisateur portable',
                'Balance de précision', 'Boîte de rangement', 'Papers slim',
                'Filtre en carton', 'Briquet tempête', 'Cendrier en céramique',
                'Plateau de roulage', 'Loupe de précision', 'Humidificateur'
            ]
        ];

        $products = [];
        $productNames = []; // Pour éviter les doublons
        
        // Générer 150 produits
        for ($i = 0; $i < 150; $i++) {
            $attempts = 0;
            do {
                // Choisir un template aléatoire
                $templateName = array_rand($productTemplates);
                $template = $productTemplates[$templateName];
                
                // Générer un nom de produit en remplaçant les placeholders
                $productName = $templateName;
                foreach ($data as $key => $values) {
                    if (strpos($productName, "{{$key}}") !== false) {
                        $randomValue = $values[array_rand($values)];
                        $productName = str_replace("{{$key}}", $randomValue, $productName);
                    }
                }
                
                // Générer la description
                $description = $template['description'];
                foreach ($data as $key => $values) {
                    if (strpos($description, "{{$key}}") !== false) {
                        $randomValue = $values[array_rand($values)];
                        $description = str_replace("{{$key}}", $randomValue, $description);
                    }
                }
                
                $attempts++;
            } while (in_array($productName, $productNames) && $attempts < 10);
            
            if ($attempts >= 10) {
                $productName .= ' #' . ($i + 31); // Numéro unique basé sur l'index
            }
            
            $productNames[] = $productName;
            
            // Générer les images
            $images = $template['images'];
            foreach ($images as &$image) {
                foreach ($data as $key => $values) {
                    if (strpos($image, "{{$key}}") !== false) {
                        $randomValue = strtolower(str_replace(' ', '-', $values[array_rand($values)]));
                        $image = str_replace("{{$key}}", $randomValue, $image);
                    }
                }
            }
            
            // Trouver une catégorie appropriée
            $category = $categories->random();
            foreach ($template['category_keywords'] as $keyword) {
                $matchingCategory = $categories->filter(function($cat) use ($keyword) {
                    return stripos($cat->name, $keyword) !== false;
                })->first();
                
                if ($matchingCategory) {
                    $category = $matchingCategory;
                    break;
                }
            }
            
            $products[] = [
                'name' => $productName,
                'description' => $description,
                'price' => round(rand($template['price_range'][0] * 100, $template['price_range'][1] * 100) / 100, 2),
                'images' => json_encode($images),
                'stock' => rand($template['stock_range'][0], $template['stock_range'][1]),
                'analysis_file' => rand(1, 3) == 1 ? 'analyse_' . strtolower(str_replace(' ', '_', $productName)) . '.pdf' : null,
                'category_id' => $category->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Insérer par lots de 50 pour optimiser les performances
            if (count($products) >= 50) {
                DB::table('cbd_products')->insert($products);
                $this->command->info("✅ Batch de " . count($products) . " produits créé...");
                $products = [];
            }
        }
        
        // Insérer le dernier lot
        if (!empty($products)) {
            DB::table('cbd_products')->insert($products);
            $this->command->info("✅ Dernier batch de " . count($products) . " produits créé...");
        }
        
        // Associer aléatoirement les produits aux fournisseurs
        $this->command->info('🔗 Association des produits aux fournisseurs...');
        $newProducts = ProductCBD::orderBy('id', 'desc')->take(150)->get();
        
        foreach ($newProducts as $product) {
            // Associer 1 à 3 fournisseurs aléatoirement
            $randomSuppliers = $suppliers->random(rand(1, min(3, $suppliers->count())));
            $product->suppliers()->attach($randomSuppliers->pluck('id'));
        }
        
        $totalProducts = ProductCBD::count();
        $this->command->info("✅ 150 produits supplémentaires créés avec succès !");
        $this->command->info("📊 Total des produits dans la base : {$totalProducts}");
        
        // Afficher un résumé par catégorie
        $this->command->info("\n📋 RÉPARTITION PAR CATÉGORIE :");
        foreach ($categories as $category) {
            $count = $category->products()->count();
            $this->command->info("   📂 {$category->name}: {$count} produits");
        }
    }
}
