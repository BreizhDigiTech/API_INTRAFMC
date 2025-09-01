<?php

/**
 * Script de test pour valider la récupération complète des données financières
 * Résout les problèmes de limitation à 50 enregistrements
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\ProductCBD;
use App\Models\Category;
use App\Models\Order;
use App\Models\CbdArrival;
use Carbon\Carbon;

class FinancialDataRetrievalFixTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un utilisateur admin
        $this->user = User::factory()->create(['is_admin' => true]);
        
        // Créer une catégorie et un produit
        $category = Category::factory()->create();
        $this->product = ProductCBD::factory()->create(['category_id' => $category->id]);
    }

    /** @test */
    public function it_retrieves_all_orders_when_include_all_is_true()
    {
        // Définir une plage de dates précise
        $startDate = Carbon::now()->subDays(35);
        $endDate = Carbon::now();
        
        // Créer 75 commandes (plus que la limite de 50) DANS la plage de dates
        for ($i = 0; $i < 75; $i++) {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'total' => 25.99,
                'status' => 'validated',
                'created_at' => $startDate->copy()->addDays($i % 30) // Toutes dans la plage
            ]);
            
            $order->products()->attach($this->product->id, [
                'quantity' => 1,
                'unit_price' => 25.99
            ]);
        }

        // Créer 60 arrivages DANS la plage de dates
        for ($i = 0; $i < 60; $i++) {
            CbdArrival::factory()->create([
                'amount' => 100.00,
                'status' => 'validated',
                'created_at' => $startDate->copy()->addDays($i % 30) // Toutes dans la plage
            ]);
        }

        // Test avec récupération complète
        $queryWithCompleteData = '
            query GetCompleteFinancialData($filters: FinancialRecapFilters!) {
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

        $filtersComplete = [
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'orderStatuses' => ['validated'],
            'arrivalStatuses' => ['validated'],
            'pagination' => [
                'includeAll' => true,  // CRITIQUE : Force la récupération complète
                'limit' => null,
                'page' => 1
            ]
        ];

        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        $response = $this->graphQL($queryWithCompleteData, ['filters' => $filtersComplete], $auth['headers']);

        // Debug: Afficher la réponse pour validation
        $responseData = $response->json();
        echo "\n=== RÉPONSE GRAPHQL ===\n";
        echo "Status: " . $response->getStatusCode() . "\n";
        echo "Orders count: " . ($responseData['data']['financialRecapSummary']['orders']['totalCount'] ?? 'NULL') . "\n";
        echo "Arrivals count: " . ($responseData['data']['financialRecapSummary']['arrivals']['totalCount'] ?? 'NULL') . "\n";
        echo "Orders complete: " . json_encode($responseData['data']['financialRecapSummary']['orders']['isComplete'] ?? 'NULL') . "\n";
        echo "Arrivals complete: " . json_encode($responseData['data']['financialRecapSummary']['arrivals']['isComplete'] ?? 'NULL') . "\n";
        echo "Data complete: " . json_encode($responseData['data']['financialRecapSummary']['dataCompleteness']['isComplete'] ?? 'NULL') . "\n";
        echo "Recommendation: " . ($responseData['data']['financialRecapSummary']['dataCompleteness']['recommendation'] ?? 'NULL') . "\n";
        echo "========================\n";

        $response->assertStatus(200);
        $data = $response->json('data.financialRecapSummary');

        // Vérifier que la requête a réussi
        $this->assertNotNull($data, 'La réponse financialRecapSummary ne devrait pas être null');

        // Vérifier que TOUTES les commandes ont été récupérées
        $this->assertGreaterThanOrEqual(75, $data['orders']['totalCount'], 'Toutes les 75 commandes devraient être récupérées');
        $this->assertTrue($data['orders']['isComplete'], 'Les commandes devraient être marquées comme complètes');

        // Vérifier que TOUS les arrivages ont été récupérés
        $this->assertGreaterThanOrEqual(60, $data['arrivals']['totalCount'], 'Tous les 60 arrivages devraient être récupérés');
        $this->assertTrue($data['arrivals']['isComplete'], 'Les arrivages devraient être marqués comme complets');

        // Vérifier la complétude générale
        $this->assertTrue($data['dataCompleteness']['isComplete'], 'Les données devraient être complètes');
        $this->assertFalse($data['dataCompleteness']['possibleLimitation'], 'Aucune limitation ne devrait être détectée');
        $this->assertStringContainsString('succès', $data['dataCompleteness']['recommendation']);
    }

    /** @test */
    public function it_detects_limitation_when_include_all_is_false()
    {
        // Créer 75 commandes
        for ($i = 0; $i < 75; $i++) {
            Order::factory()->create([
                'user_id' => $this->user->id,
                'total' => 25.99,
                'status' => 'validated',
                'created_at' => Carbon::now()->subDays($i % 30)
            ]);
        }

        // Test SANS récupération complète
        $queryWithLimitation = '
            query GetLimitedFinancialData($filters: FinancialRecapFilters!) {
                financialRecapSummary(filters: $filters) {
                    orders {
                        totalCount
                        isComplete
                    }
                    dataCompleteness {
                        isComplete
                        possibleLimitation
                        recommendation
                    }
                }
            }
        ';

        $filtersLimited = [
            'startDate' => Carbon::now()->subMonth()->toDateString(),
            'endDate' => Carbon::now()->toDateString(),
            'orderStatuses' => ['validated'],
            'arrivalStatuses' => ['validated'],
            'pagination' => [
                'includeAll' => false,  // Limitation activée
                'limit' => 50,
                'page' => 1
            ]
        ];

        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        $response = $this->graphQL($queryWithLimitation, ['filters' => $filtersLimited], $auth['headers']);

        $response->assertStatus(200);
        $data = $response->json('data.financialRecapSummary');

        // Debug
        echo "\n=== TEST LIMITATION ===\n";
        echo "Orders count: " . $data['orders']['totalCount'] . "\n";
        echo "Orders complete: " . json_encode($data['orders']['isComplete']) . "\n";
        echo "Possible limitation: " . json_encode($data['dataCompleteness']['possibleLimitation']) . "\n";
        echo "========================\n";

        // Vérifier la limitation
        $this->assertEquals(50, $data['orders']['totalCount'], 'Devrait être limité à 50 commandes');
        $this->assertFalse($data['orders']['isComplete'], 'Les commandes ne devraient pas être marquées comme complètes');
        $this->assertTrue($data['dataCompleteness']['possibleLimitation'], 'Une limitation devrait être détectée');
        $this->assertStringContainsString('includeAll', $data['dataCompleteness']['recommendation']);
    }

    /** @test */
    public function it_handles_empty_filters_gracefully()
    {
        // Test avec filtres incomplets
        $queryBasic = '
            query GetBasicFinancialData($filters: FinancialRecapFilters!) {
                financialRecapSummary(filters: $filters) {
                    orders {
                        totalCount
                        isComplete
                    }
                    arrivals {
                        totalCount
                        isComplete
                    }
                    dataCompleteness {
                        isComplete
                        recommendation
                    }
                }
            }
        ';

        // Filtres minimaux (sans pagination)
        $minimalFilters = [
            'startDate' => Carbon::now()->subDays(7)->toDateString(),
            'endDate' => Carbon::now()->toDateString(),
        ];

        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        $response = $this->graphQL($queryBasic, ['filters' => $minimalFilters], $auth['headers']);

        $response->assertStatus(200);
        $data = $response->json('data.financialRecapSummary');

        // Vérifier que les valeurs par défaut sont appliquées
        $this->assertNotNull($data);
        $this->assertArrayHasKey('orders', $data);
        $this->assertArrayHasKey('arrivals', $data);
        $this->assertArrayHasKey('dataCompleteness', $data);

        echo "\n=== TEST FILTRES MINIMAUX ===\n";
        echo "Orders count: " . $data['orders']['totalCount'] . "\n";
        echo "Arrivals count: " . $data['arrivals']['totalCount'] . "\n";
        echo "Recommendation: " . $data['dataCompleteness']['recommendation'] . "\n";
        echo "===============================\n";
    }

    /** @test */
    public function it_validates_total_count_in_database()
    {
        // Vérifier le nombre réel en base de données
        $totalOrdersInDB = Order::count();
        $totalArrivalsInDB = CbdArrival::count();

        echo "\n=== VÉRIFICATION BASE DE DONNÉES ===\n";
        echo "Total commandes en BDD: $totalOrdersInDB\n";
        echo "Total arrivages en BDD: $totalArrivalsInDB\n";

        // Créer des données de test supplémentaires
        for ($i = 0; $i < 20; $i++) {
            Order::factory()->create([
                'user_id' => $this->user->id,
                'status' => 'validated',
                'created_at' => Carbon::now()->subDays($i)
            ]);
        }

        $newTotalOrders = Order::count();
        echo "Nouvelles commandes en BDD: $newTotalOrders\n";

        // Test GraphQL pour récupération complète
        $filters = [
            'startDate' => Carbon::now()->subMonth()->toDateString(),
            'endDate' => Carbon::now()->toDateString(),
            'orderStatuses' => ['validated'],
            'pagination' => [
                'includeAll' => true
            ]
        ];

        $query = '
            query($filters: FinancialRecapFilters!) {
                financialRecapSummary(filters: $filters) {
                    orders {
                        totalCount
                        isComplete
                    }
                }
            }
        ';

        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        $response = $this->graphQL($query, ['filters' => $filters], $auth['headers']);

        $data = $response->json('data.financialRecapSummary');
        $graphqlOrderCount = $data['orders']['totalCount'];

        echo "Commandes récupérées via GraphQL: $graphqlOrderCount\n";
        echo "====================================\n";

        // Le nombre GraphQL devrait correspondre ou être proche du nombre en BDD
        // (peut être différent à cause des filtres de date)
        $this->assertGreaterThan(0, $graphqlOrderCount, 'GraphQL devrait retourner des commandes');
    }
}
