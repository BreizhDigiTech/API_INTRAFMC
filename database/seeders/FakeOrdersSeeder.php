<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\ProductCBD;
use Carbon\Carbon;

class FakeOrdersSeeder extends Seeder
{
    /**
     * Génère 80 commandes fake pour les tests
     */
    public function run(): void
    {
        $this->command->info('🛒 Génération de 80 commandes fake...');

        // Récupérer tous les utilisateurs et produits
        $users = User::all();
        $products = ProductCBD::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ Aucun utilisateur trouvé. Veuillez d\'abord exécuter le seeder des utilisateurs.');
            return;
        }

        if ($products->isEmpty()) {
            $this->command->error('❌ Aucun produit trouvé. Veuillez d\'abord exécuter le seeder des produits.');
            return;
        }

        $statuses = ['pending', 'validated', 'cancelled'];
        
        // Templates de commandes variées
        $orderTemplates = [
            // Commandes petites (1-2 produits)
            ['products_count' => 1, 'min_qty' => 1, 'max_qty' => 2, 'weight' => 40],
            ['products_count' => 2, 'min_qty' => 1, 'max_qty' => 3, 'weight' => 30],
            
            // Commandes moyennes (3-5 produits)
            ['products_count' => 3, 'min_qty' => 1, 'max_qty' => 4, 'weight' => 20],
            ['products_count' => 4, 'min_qty' => 1, 'max_qty' => 3, 'weight' => 15],
            ['products_count' => 5, 'min_qty' => 1, 'max_qty' => 2, 'weight' => 10],
            
            // Grosses commandes (6+ produits)
            ['products_count' => 6, 'min_qty' => 1, 'max_qty' => 2, 'weight' => 8],
            ['products_count' => 8, 'min_qty' => 1, 'max_qty' => 3, 'weight' => 5],
            ['products_count' => 10, 'min_qty' => 1, 'max_qty' => 2, 'weight' => 2],
        ];

        for ($i = 1; $i <= 80; $i++) {
            // Sélectionner un template selon les poids
            $template = $this->selectTemplate($orderTemplates);
            
            // Sélectionner un utilisateur aléatoire
            $user = $users->random();
            
            // Créer une date aléatoire dans les 6 derniers mois
            $createdAt = Carbon::now()->subDays(rand(1, 180));
            
            // Sélectionner un statut (plus de commandes livrées que annulées)
            $status = $this->selectStatus($statuses, $createdAt);
            
            // Créer la commande
            $order = Order::create([
                'user_id' => $user->id,
                'total' => 0, // Sera calculé après
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addHours(rand(0, 72)),
            ]);

            // Sélectionner des produits aléatoires
            $selectedProducts = $products->random($template['products_count']);
            $total = 0;

            foreach ($selectedProducts as $product) {
                $quantity = rand($template['min_qty'], $template['max_qty']);
                $unitPrice = $product->price;
                $lineTotal = $quantity * $unitPrice;
                $total += $lineTotal;

                // Attacher le produit à la commande avec les détails
                $order->products()->attach($product->id, [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            // Mettre à jour le total de la commande
            $order->update(['total' => $total]);

            if ($i % 10 == 0) {
                $this->command->info("✅ {$i} commandes générées...");
            }
        }

        $this->command->info('🎉 80 commandes fake générées avec succès !');
        
        // Afficher des statistiques
        $this->displayStatistics();
    }

    /**
     * Sélectionne un template selon les poids
     */
    private function selectTemplate(array $templates): array
    {
        $totalWeight = array_sum(array_column($templates, 'weight'));
        $random = rand(1, $totalWeight);
        $currentWeight = 0;

        foreach ($templates as $template) {
            $currentWeight += $template['weight'];
            if ($random <= $currentWeight) {
                return $template;
            }
        }

        return $templates[0]; // Fallback
    }

    /**
     * Sélectionne un statut selon la logique métier et la date
     */
    private function selectStatus(array $statuses, Carbon $createdAt): string
    {
        $daysSinceCreation = $createdAt->diffInDays(Carbon::now());

        // Logique de statut selon l'ancienneté
        if ($daysSinceCreation > 30) {
            // Commandes anciennes : plus probablement validées ou annulées
            $weights = [
                'pending' => 10,
                'validated' => 80,
                'cancelled' => 10
            ];
        } elseif ($daysSinceCreation > 7) {
            // Commandes moyennes : en cours de traitement
            $weights = [
                'pending' => 20,
                'validated' => 70,
                'cancelled' => 10
            ];
        } else {
            // Commandes récentes : plutôt en attente
            $weights = [
                'pending' => 60,
                'validated' => 35,
                'cancelled' => 5
            ];
        }

        return $this->weightedRandom($weights);
    }

    /**
     * Sélection aléatoire pondérée
     */
    private function weightedRandom(array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        $currentWeight = 0;

        foreach ($weights as $status => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $status;
            }
        }

        return array_key_first($weights); // Fallback
    }

    /**
     * Affiche des statistiques sur les commandes générées
     */
    private function displayStatistics(): void
    {
        $this->command->info("\n📊 Statistiques des commandes générées :");
        $this->command->info("=====================================");

        // Statistiques par statut
        $statusStats = Order::selectRaw('status, COUNT(*) as count, AVG(total) as avg_total')
            ->groupBy('status')
            ->get();

        foreach ($statusStats as $stat) {
            $this->command->info("• {$stat->status}: {$stat->count} commandes (Moyenne: " . number_format($stat->avg_total, 2) . "€)");
        }

        // Total général
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total');
        $avgOrderValue = Order::avg('total');

        $this->command->info("\n💰 Résumé général :");
        $this->command->info("• Total commandes: {$totalOrders}");
        $this->command->info("• Chiffre d'affaires total: " . number_format($totalRevenue, 2) . "€");
        $this->command->info("• Panier moyen: " . number_format($avgOrderValue, 2) . "€");
    }
}
