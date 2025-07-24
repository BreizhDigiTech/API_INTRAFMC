<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CbdArrival;
use App\Models\ProductCBD;
use App\Models\ArrivalProductCbd;
use Illuminate\Support\Facades\DB;

class AdditionalArrivalsSeeder extends Seeder
{
    /**
     * Génère des arrivages supplémentaires pour les nouveaux produits
     */
    public function run(): void
    {
        $this->command->info('📦 Génération d\'arrivages supplémentaires...');

        // Récupérer les nouveaux produits (les 150 derniers)
        $newProducts = ProductCBD::orderBy('id', 'desc')->take(150)->get();
        
        // Créer 8 nouveaux arrivages
        for ($i = 1; $i <= 8; $i++) {
            $status = rand(1, 3) == 1 ? 'pending' : 'validated';
            
            // Créer l'arrivage
            $arrival = CbdArrival::create([
                'amount' => rand(2000, 8000) + (rand(0, 99) / 100), // Entre 2000.00 et 8000.99
                'status' => $status,
            ]);
            
            // Sélectionner 5 à 15 produits aléatoires pour cet arrivage
            $selectedProducts = $newProducts->random(rand(5, 15));
            
            $arrivalProducts = [];
            foreach ($selectedProducts as $product) {
                $quantity = rand(10, 200);
                $unitPrice = $product->price * rand(60, 85) / 100; // Prix d'achat = 60-85% du prix de vente
                
                $arrivalProducts[] = [
                    'arrival_id' => $arrival->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => round($unitPrice, 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Insérer les produits de l'arrivage
            DB::table('arrival_product_cbd')->insert($arrivalProducts);
            
            // Recalculer le montant total
            $totalAmount = collect($arrivalProducts)->sum(function($item) {
                return $item['quantity'] * $item['unit_price'];
            });
            
            $arrival->update(['amount' => $totalAmount]);
            
            $this->command->info("✅ Arrivage {$arrival->id} créé avec " . count($arrivalProducts) . " produits (Montant: {$totalAmount}€, Statut: {$status})");
        }
        
        $totalArrivals = CbdArrival::count();
        $totalArrivalProducts = ArrivalProductCbd::count();
        
        $this->command->info("✅ 8 arrivages supplémentaires créés !");
        $this->command->info("📊 Total des arrivages: {$totalArrivals}");
        $this->command->info("📊 Total des produits dans les arrivages: {$totalArrivalProducts}");
    }
}
