<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\ProductCBD;
use App\Models\Supplier;
use App\Models\Cart;
use App\Models\Order;
use App\Models\CbdArrival;
use App\Models\ArrivalProductCbd;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DevelopmentDataSeeder extends Seeder
{
    /**
     * Génère des données complètes pour le développement frontend
     */
    public function run(): void
    {
        $this->command->info('🚀 Génération des données de développement...');

        // 1. Utilisateurs
        $this->seedUsers();
        
        // 2. Catégories
        $this->seedCategories();
        
        // 3. Fournisseurs
        $this->seedSuppliers();
        
        // 4. Produits CBD
        $this->seedProducts();
        
        // 5. Paniers avec produits
        $this->seedCarts();
        
        // 6. Commandes
        $this->seedOrders();
        
        // 7. Arrivages
        $this->seedArrivals();

        $this->command->info('✅ Génération terminée !');
        $this->displaySummary();
    }

    private function seedUsers(): void
    {
        $this->command->info('👥 Création des utilisateurs...');

        // Vérifier si les utilisateurs existent déjà
        if (User::where('email', 'admin@admin.com')->exists()) {
            $this->command->info('   ⚠️  Utilisateurs déjà existants, passage...');
            return;
        }

        // Admin principal
        User::factory()->create([
            'name' => 'Administrateur Principal',
            'email' => 'admin@admin.com',
            'password' => Hash::make('L15fddef!'),
            'is_admin' => true,
            'is_active' => true,
            'avatar' => 'avatars/admin.jpg',
            'phone' => '+33 6 12 34 56 78',
            'address' => '123 Rue de la Paix, 75001 Paris',
            'birth_date' => '1985-03-15',
        ]);

        // Admin secondaire
        User::factory()->create([
            'name' => 'Manager CBD',
            'email' => 'manager@cbdstore.com',
            'password' => Hash::make('manager123'),
            'is_admin' => true,
            'is_active' => true,
            'avatar' => 'avatars/manager.jpg',
            'phone' => '+33 6 98 76 54 32',
            'address' => '456 Avenue du CBD, 69001 Lyon',
            'birth_date' => '1990-07-22',
        ]);

        // Utilisateurs clients avec profils variés
        $clientProfiles = [
            [
                'name' => 'Marie Dubois',
                'email' => 'marie.dubois@email.com',
                'phone' => '+33 6 11 22 33 44',
                'address' => '789 Rue des Fleurs, 33000 Bordeaux',
                'birth_date' => '1992-05-10',
            ],
            [
                'name' => 'Pierre Martin',
                'email' => 'pierre.martin@email.com',
                'phone' => '+33 6 55 66 77 88',
                'address' => '321 Boulevard Liberté, 13001 Marseille',
                'birth_date' => '1988-12-03',
            ],
            [
                'name' => 'Sophie Bernard',
                'email' => 'sophie.bernard@email.com',
                'phone' => '+33 6 99 88 77 66',
                'address' => '654 Rue de la Santé, 59000 Lille',
                'birth_date' => '1995-08-18',
            ],
            [
                'name' => 'Lucas Petit',
                'email' => 'lucas.petit@email.com',
                'phone' => '+33 6 44 33 22 11',
                'address' => '987 Avenue Verte, 67000 Strasbourg',
                'birth_date' => '1991-11-25',
            ],
            [
                'name' => 'Emma Moreau',
                'email' => 'emma.moreau@email.com',
                'phone' => '+33 6 77 88 99 00',
                'address' => '147 Rue Naturelle, 35000 Rennes',
                'birth_date' => '1993-04-07',
            ]
        ];

        foreach ($clientProfiles as $profile) {
            User::factory()->create(array_merge($profile, [
                'password' => Hash::make('client123'),
                'is_admin' => false,
                'is_active' => true,
                'avatar' => 'avatars/client_' . fake()->numberBetween(1, 10) . '.jpg',
            ]));
        }

        // Quelques utilisateurs inactifs
        User::factory()->count(3)->create([
            'is_admin' => false,
            'is_active' => false,
            'password' => Hash::make('inactive123'),
        ]);
    }

    private function seedCategories(): void
    {
        $this->command->info('📂 Création des catégories...');

        // Vérifier si les catégories existent déjà
        if (Category::where('name', 'Huiles CBD')->exists()) {
            $this->command->info('   ⚠️  Catégories déjà existantes, passage...');
            return;
        }

        $categories = [
            [
                'name' => 'Huiles CBD',
                'description' => 'Huiles de CBD full spectrum et isolat, différentes concentrations disponibles',
            ],
            [
                'name' => 'Fleurs CBD',
                'description' => 'Fleurs de CBD premium, cultivées biologiquement en Europe',
            ],
            [
                'name' => 'E-liquides CBD',
                'description' => 'E-liquides au CBD pour cigarettes électroniques, saveurs variées',
            ],
            [
                'name' => 'Cosmétiques CBD',
                'description' => 'Crèmes, baumes et cosmétiques infusés au CBD',
            ],
            [
                'name' => 'Comestibles CBD',
                'description' => 'Bonbons, chocolats et autres produits alimentaires au CBD',
            ],
            [
                'name' => 'Résines CBD',
                'description' => 'Hash et résines de CBD artisanales, haute qualité',
            ],
            [
                'name' => 'Accessoires',
                'description' => 'Vaporisateurs, pipes et accessoires pour consommation',
            ]
        ];

        foreach ($categories as $categoryData) {
            Category::factory()->create($categoryData);
        }
    }

    private function seedSuppliers(): void
    {
        $this->command->info('🏭 Création des fournisseurs...');

        $suppliers = [
            [
                'name' => 'Green Valley Farms',
                'email' => 'contact@greenvalley.com',
                'phone' => '+33 4 78 90 12 34',
                'address' => 'Zone Agricole CBD, 26000 Valence',
                'website' => 'https://greenvalley-cbd.com',
                'contact_person' => 'Jean-Luc Verdier',
                'description' => 'Producteur bio de fleurs et extraits CBD depuis 2018'
            ],
            [
                'name' => 'CBD Premium Labs',
                'email' => 'info@cbdlabs.fr',
                'phone' => '+33 5 56 78 90 12',
                'address' => 'Parc Technologique, 33600 Pessac',
                'website' => 'https://cbdpremium-labs.fr',
                'contact_person' => 'Dr. Marie Blanchard',
                'description' => 'Laboratoire spécialisé dans l\'extraction et purification CBD'
            ],
            [
                'name' => 'Swiss CBD Import',
                'email' => 'orders@swisscbd.ch',
                'phone' => '+41 22 345 67 89',
                'address' => 'Rue du Commerce 15, 1204 Genève, Suisse',
                'website' => 'https://swiss-cbd-import.ch',
                'contact_person' => 'Klaus Weber',
                'description' => 'Importateur de produits CBD suisses premium'
            ],
            [
                'name' => 'Organic CBD Solutions',
                'email' => 'contact@organic-cbd.eu',
                'phone' => '+33 3 20 45 67 89',
                'address' => 'Zone Industrielle Nord, 59000 Lille',
                'website' => 'https://organic-cbd-solutions.eu',
                'contact_person' => 'Amélie Dupont',
                'description' => 'Fabricant de cosmétiques et comestibles CBD bio'
            ],
            [
                'name' => 'Mediterranean Hemp Co.',
                'email' => 'info@medhempco.com',
                'phone' => '+33 4 91 23 45 67',
                'address' => 'Quartier des Entrepreneurs, 13008 Marseille',
                'website' => 'https://mediterranean-hemp.com',
                'contact_person' => 'Antonio Rossi',
                'description' => 'Coopérative de producteurs méditerranéens'
            ]
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::factory()->create($supplierData);
        }
    }

    private function seedProducts(): void
    {
        $this->command->info('🌿 Création des produits CBD...');

        $categories = Category::all();
        $suppliers = Supplier::all();

        // Produits détaillés par catégorie
        $productsByCategory = [
            'Huiles CBD' => [
                [
                    'name' => 'Huile CBD 5% Full Spectrum',
                    'description' => 'Huile de CBD full spectrum 5% (500mg) dans huile de chanvre bio. Extraction CO2 supercritique. Goût naturel de chanvre.',
                    'price' => 29.90,
                    'stock' => 150,
                    'images' => ['products/huile-5.jpg', 'products/huile-5-detail.jpg'],
                    'analysis_file' => 'analyses/huile-cbd-5-analyse.pdf',
                ],
                [
                    'name' => 'Huile CBD 10% Isolat',
                    'description' => 'Huile de CBD isolat 10% (1000mg) dans huile MCT. Sans THC, goût neutre. Idéale pour débutants.',
                    'price' => 49.90,
                    'stock' => 120,
                    'images' => ['products/huile-10.jpg', 'products/huile-10-pipette.jpg'],
                    'analysis_file' => 'analyses/huile-cbd-10-analyse.pdf',
                ],
                [
                    'name' => 'Huile CBD 20% Premium',
                    'description' => 'Huile de CBD premium 20% (2000mg) full spectrum. Extraction artisanale, chanvre français bio.',
                    'price' => 89.90,
                    'stock' => 80,
                    'images' => ['products/huile-20.jpg', 'products/huile-20-premium.jpg'],
                    'analysis_file' => 'analyses/huile-cbd-20-analyse.pdf',
                ],
            ],
            'Fleurs CBD' => [
                [
                    'name' => 'OG Kush CBD',
                    'description' => 'Fleur OG Kush CBD 18%. Arôme intense, effet relaxant. Cultivée indoor en France.',
                    'price' => 8.50,
                    'stock' => 500,
                    'images' => ['products/og-kush.jpg', 'products/og-kush-detail.jpg'],
                    'analysis_file' => 'analyses/og-kush-analyse.pdf',
                ],
                [
                    'name' => 'Amnesia Haze CBD',
                    'description' => 'Fleur Amnesia Haze CBD 15%. Saveur citronnée, effet énergisant. Culture biologique.',
                    'price' => 7.90,
                    'stock' => 450,
                    'images' => ['products/amnesia.jpg', 'products/amnesia-trichomes.jpg'],
                    'analysis_file' => 'analyses/amnesia-analyse.pdf',
                ],
                [
                    'name' => 'Purple Haze CBD',
                    'description' => 'Fleur Purple Haze CBD 16%. Couleur violette, arôme fruité. Édition limitée.',
                    'price' => 12.00,
                    'stock' => 200,
                    'images' => ['products/purple-haze.jpg', 'products/purple-detail.jpg'],
                    'analysis_file' => 'analyses/purple-haze-analyse.pdf',
                ],
            ],
            'E-liquides CBD' => [
                [
                    'name' => 'E-liquide CBD Menthe 300mg',
                    'description' => 'E-liquide CBD saveur menthe fraîche. 300mg de CBD, 10ml. Compatible toutes e-cigarettes.',
                    'price' => 19.90,
                    'stock' => 300,
                    'images' => ['products/eliquide-menthe.jpg'],
                ],
                [
                    'name' => 'E-liquide CBD Fruits Rouges 500mg',
                    'description' => 'E-liquide CBD saveur fruits rouges. 500mg de CBD, 10ml. Fabrication française.',
                    'price' => 24.90,
                    'stock' => 250,
                    'images' => ['products/eliquide-fruits.jpg'],
                ],
            ],
            'Cosmétiques CBD' => [
                [
                    'name' => 'Crème Anti-Douleur CBD 200mg',
                    'description' => 'Crème topique au CBD pour soulager douleurs musculaires. 200mg CBD, 50ml.',
                    'price' => 34.90,
                    'stock' => 180,
                    'images' => ['products/creme-douleur.jpg'],
                ],
                [
                    'name' => 'Baume Réparateur CBD 150mg',
                    'description' => 'Baume réparateur au CBD pour peaux sèches. Ingrédients naturels, 30ml.',
                    'price' => 22.90,
                    'stock' => 220,
                    'images' => ['products/baume-reparateur.jpg'],
                ],
            ],
        ];

        foreach ($productsByCategory as $categoryName => $products) {
            $category = $categories->where('name', $categoryName)->first();
            if (!$category) continue;

            foreach ($products as $productData) {
                $product = ProductCBD::factory()->create(array_merge($productData, [
                    'category_id' => $category->id,
                ]));

                // Associer avec 1-3 fournisseurs aléatoires
                $randomSuppliers = $suppliers->random(rand(1, 3));
                $product->suppliers()->attach($randomSuppliers);
            }
        }

        // Ajouter quelques produits génériques pour compléter
        ProductCBD::factory()->count(20)->create()->each(function ($product) use ($suppliers) {
            $randomSuppliers = $suppliers->random(rand(1, 2));
            $product->suppliers()->attach($randomSuppliers);
        });
    }

    private function seedCarts(): void
    {
        $this->command->info('🛒 Création des paniers...');

        // Vérifier si des paniers existent déjà
        if (Cart::count() > 0) {
            $this->command->info('   ⚠️  Paniers déjà existants, passage...');
            return;
        }

        $users = User::where('is_admin', false)->where('is_active', true)->get();
        $products = ProductCBD::all();

        foreach ($users as $user) {
            // 70% de chance d'avoir des articles dans le panier
            if (rand(1, 100) <= 70) {
                $numItems = rand(1, 5);
                $selectedProducts = $products->random($numItems);

                foreach ($selectedProducts as $product) {
                    // Vérifier qu'il n'existe pas déjà
                    $existingCart = Cart::where('user_id', $user->id)
                                       ->where('product_id', $product->id)
                                       ->first();
                    
                    if (!$existingCart) {
                        Cart::factory()->create([
                            'user_id' => $user->id,
                            'product_id' => $product->id,
                            'quantity' => rand(1, 3),
                        ]);
                    }
                }
            }
        }
    }

    private function seedOrders(): void
    {
        $this->command->info('📦 Création des commandes...');

        $users = User::where('is_admin', false)->where('is_active', true)->get();
        $products = ProductCBD::all();

        foreach ($users as $user) {
            // Créer 0-3 commandes par utilisateur
            $numOrders = rand(0, 3);

            for ($i = 0; $i < $numOrders; $i++) {
                $order = Order::factory()->create([
                    'user_id' => $user->id,
                    'status' => fake()->randomElement(['pending', 'validated', 'cancelled']),
                    'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
                ]);

                // Ajouter 1-4 produits par commande
                $numProducts = rand(1, 4);
                $selectedProducts = $products->random($numProducts);
                $total = 0;

                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 3);
                    $price = $product->price;
                    $total += $price * $quantity;

                    $order->products()->attach($product->id, [
                        'quantity' => $quantity,
                        'unit_price' => $price,
                    ]);
                }

                $order->update(['total' => $total]);
            }
        }
    }

    private function seedArrivals(): void
    {
        $this->command->info('📋 Création des arrivages...');

        // Vérifier si des arrivages existent déjà
        if (CbdArrival::count() > 0) {
            $this->command->info('   ⚠️  Arrivages déjà existants, passage...');
            return;
        }

        $products = ProductCBD::all();

        // Créer 5-10 arrivages simples
        for ($i = 0; $i < rand(5, 10); $i++) {
            $arrival = CbdArrival::factory()->create();

            // Ajouter 2-5 produits par arrivage
            $numProducts = rand(2, 5);
            $selectedProducts = $products->random($numProducts);

            foreach ($selectedProducts as $product) {
                ArrivalProductCbd::create([
                    'arrival_id' => $arrival->id,
                    'product_id' => $product->id,
                    'quantity' => rand(10, 100),
                    'unit_price' => $product->price * 0.6, // Prix d'achat = 60% du prix de vente
                ]);
            }
        }
    }

    private function displaySummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 RÉSUMÉ DES DONNÉES GÉNÉRÉES');
        $this->command->info('================================');
        $this->command->info('👥 Utilisateurs: ' . User::count() . ' (dont ' . User::where('is_admin', true)->count() . ' admins)');
        $this->command->info('📂 Catégories: ' . Category::count());
        $this->command->info('🏭 Fournisseurs: ' . Supplier::count());
        $this->command->info('🌿 Produits CBD: ' . ProductCBD::count());
        $this->command->info('🛒 Articles panier: ' . Cart::count());
        $this->command->info('📦 Commandes: ' . Order::count());
        $this->command->info('📋 Arrivages: ' . CbdArrival::count());
        $this->command->info('');
        $this->command->info('✅ Prêt pour le développement frontend !');
        $this->command->info('');
        $this->command->info('🔑 COMPTES DE TEST:');
        $this->command->info('Admin: admin@admin.com / L15fddef!');
        $this->command->info('Manager: manager@cbdstore.com / manager123');
        $this->command->info('Client: marie.dubois@email.com / client123');
    }
}
