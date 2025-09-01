<?php

/**
 * Test spécifique pour récupérer TOUTES les commandes (84) sans limitation
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\CbdArrival;
use Carbon\Carbon;

class GetAllOrdersUnlimitedTest extends TestCase
{
    /** @test */
    public function it_retrieves_all_existing_orders_without_any_limitation()
    {
        echo "\n🚀 TEST : Récupération de TOUTES les commandes existantes\n";
        echo "=====================================================\n";

        // Compter les commandes existantes
        $totalOrdersInDB = Order::count();
        $totalArrivalsInDB = CbdArrival::count();
        $validatedOrdersInDB = Order::where('status', 'validated')->count();

        echo "📊 Données en BDD:\n";
        echo "   - Total commandes: {$totalOrdersInDB}\n";
        echo "   - Commandes validées: {$validatedOrdersInDB}\n";
        echo "   - Total arrivages: {$totalArrivalsInDB}\n\n";

        // Requête GraphQL pour récupération ABSOLUMENT TOTALE
        $queryUnlimited = '
            query GetAbsolutelyAllData($filters: FinancialRecapFilters!) {
                financialRecapSummary(filters: $filters) {
                    orders {
                        totalCount
                        totalAmount
                        isComplete
                    }
                    arrivals {
                        totalCount
                        totalAmount
                        isComplete
                    }
                    dataCompleteness {
                        isComplete
                        ordersRetrieved
                        arrivalsRetrieved
                        possibleLimitation
                        recommendation
                    }
                }
            }
        ';

        // Filtres pour récupérer TOUTES les données (toutes statuts, toutes dates)
        $filtersUnlimited = [
            'startDate' => '2020-01-01',  // Date très ancienne
            'endDate' => '2030-12-31',    // Date très future
            'orderStatuses' => ['validated', 'pending', 'cancelled', 'processing', 'shipped'], // TOUS les statuts
            'arrivalStatuses' => ['validated', 'pending', 'cancelled'], // TOUS les statuts
            'pagination' => [
                'includeAll' => true,     // FORCE récupération complète
                'limit' => null,          // AUCUNE limite
                'page' => 1,
                'maxRecords' => 999999    // Limite très haute
            ]
        ];

        echo "🔧 Filtres ultra-complets appliqués:\n";
        echo "   - Dates: 2020-01-01 à 2030-12-31\n";
        echo "   - Tous les statuts inclus\n";
        echo "   - includeAll: true\n";
        echo "   - limit: null\n\n";

        // Créer un utilisateur admin pour les permissions
        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        
        // Exécuter la requête
        $response = $this->graphQL($queryUnlimited, ['filters' => $filtersUnlimited], $auth['headers']);

        echo "📡 Réponse GraphQL:\n";
        echo "   - Status HTTP: " . $response->getStatusCode() . "\n";

        $response->assertStatus(200);
        $data = $response->json('data.financialRecapSummary');

        // Afficher les résultats détaillés
        echo "   - Commandes récupérées: " . ($data['orders']['totalCount'] ?? 'NULL') . "\n";
        echo "   - Arrivages récupérés: " . ($data['arrivals']['totalCount'] ?? 'NULL') . "\n";
        echo "   - Orders complete: " . json_encode($data['orders']['isComplete'] ?? 'NULL') . "\n";
        echo "   - Arrivals complete: " . json_encode($data['arrivals']['isComplete'] ?? 'NULL') . "\n";
        echo "   - Data complete: " . json_encode($data['dataCompleteness']['isComplete'] ?? 'NULL') . "\n";
        echo "   - Possible limitation: " . json_encode($data['dataCompleteness']['possibleLimitation'] ?? 'NULL') . "\n";
        echo "   - Recommendation: " . ($data['dataCompleteness']['recommendation'] ?? 'NULL') . "\n\n";

        // Vérifications strictes
        $this->assertNotNull($data, 'La réponse ne devrait pas être null');

        // CRITIQUE: Vérifier qu'on récupère TOUTES les commandes de la BDD
        $ordersRetrieved = $data['orders']['totalCount'];
        $arrivalsRetrieved = $data['arrivals']['totalCount'];

        echo "🔍 VÉRIFICATION CRITIQUE:\n";
        echo "   - BDD contient: {$totalOrdersInDB} commandes\n";
        echo "   - GraphQL récupère: {$ordersRetrieved} commandes\n";
        echo "   - Écart: " . ($totalOrdersInDB - $ordersRetrieved) . "\n\n";

        // Assertion principale: On DOIT récupérer TOUTES les commandes
        if ($ordersRetrieved < $totalOrdersInDB) {
            echo "❌ PROBLÈME DÉTECTÉ: Limitation encore active!\n";
            echo "   GraphQL ne récupère que {$ordersRetrieved}/{$totalOrdersInDB} commandes\n";
            
            // Analyser pourquoi toutes les commandes ne sont pas récupérées
            $this->analyzeMissingOrders($totalOrdersInDB, $ordersRetrieved);
        } else {
            echo "✅ SUCCÈS: Toutes les commandes récupérées!\n";
        }

        // Assertions finales
        $this->assertGreaterThanOrEqual($totalOrdersInDB, $ordersRetrieved, 
            "GraphQL devrait récupérer TOUTES les {$totalOrdersInDB} commandes de la BDD, mais n'en récupère que {$ordersRetrieved}");
        
        $this->assertTrue($data['orders']['isComplete'], 
            'Les commandes devraient être marquées comme complètes');
        
        $this->assertTrue($data['dataCompleteness']['isComplete'], 
            'Les données devraient être complètes');
        
        $this->assertFalse($data['dataCompleteness']['possibleLimitation'], 
            'Aucune limitation ne devrait être détectée');

        echo "🎉 TEST RÉUSSI: Récupération de TOUTES les commandes confirmée!\n";
        echo "=============================================================\n";
    }

    private function analyzeMissingOrders($totalInDB, $retrieved)
    {
        echo "\n🔍 ANALYSE DES COMMANDES MANQUANTES:\n";
        
        // Analyser par statut
        $statusCounts = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
            
        echo "   Répartition par statut:\n";
        foreach ($statusCounts as $status) {
            echo "   - {$status->status}: {$status->count} commandes\n";
        }
        
        // Analyser par date
        $oldestOrder = Order::orderBy('created_at', 'asc')->first();
        $newestOrder = Order::orderBy('created_at', 'desc')->first();
        
        if ($oldestOrder && $newestOrder) {
            echo "   Plage de dates:\n";
            echo "   - Plus ancienne: {$oldestOrder->created_at}\n";
            echo "   - Plus récente: {$newestOrder->created_at}\n";
        }
        
        echo "   Commandes manquantes: " . ($totalInDB - $retrieved) . "\n";
    }
}
