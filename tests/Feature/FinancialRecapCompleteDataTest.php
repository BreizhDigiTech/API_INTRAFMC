<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\ProductCBD;
use App\Models\Category;
use App\Models\Order;
use App\Models\CbdArrival;
use Carbon\Carbon;

class FinancialRecapCompleteDataTest extends TestCase
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
    public function it_can_retrieve_complete_financial_data_when_include_all_is_true()
    {
        // Créer plus de 50 commandes pour tester la limitation
        for ($i = 0; $i < 75; $i++) {
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'total' => 25.99,
                'status' => 'validated',
                'created_at' => Carbon::now()->subDays($i % 30)
            ]);
            
            $order->products()->attach($this->product->id, [
                'quantity' => 1,
                'unit_price' => 25.99
            ]);
        }

        // Créer plus de 50 arrivages
        for ($i = 0; $i < 60; $i++) {
            CbdArrival::factory()->create([
                'amount' => 100.00,
                'status' => 'validated',
                'created_at' => Carbon::now()->subDays($i % 30)
            ]);
        }

        // Test avec récupération complète
        $queryWithCompleteData = '
            query GetCompleteSummary($filters: FinancialRecapFilters!) {
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
            'startDate' => Carbon::now()->subMonth()->toDateString(),
            'endDate' => Carbon::now()->toDateString(),
            'pagination' => [
                'includeAll' => true
            ]
        ];

        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        $response = $this->graphQL($queryWithCompleteData, ['filters' => $filtersComplete], $auth['headers']);

        // Debug: Afficher la réponse pour voir le contenu
        dump('Financial recap response:', $response->json());

        $response->assertStatus(200);
        $data = $response->json('data.financialRecapSummary');

        // Vérifier que la requête a réussi
        $this->assertNotNull($data, 'La réponse financialRecapSummary ne devrait pas être null');

        // Vérifier que toutes les commandes ont été récupérées (ou proche)
        $this->assertGreaterThanOrEqual(70, $data['orders']['totalCount'], 'Au moins 70 commandes devraient être récupérées');
        $this->assertTrue($data['orders']['isComplete']);

        // Vérifier que tous les arrivages ont été récupérés (ou proche)
        $this->assertGreaterThanOrEqual(55, $data['arrivals']['totalCount'], 'Au moins 55 arrivages devraient être récupérés');
        $this->assertTrue($data['arrivals']['isComplete']);

        // Vérifier la complétude générale
        $this->assertTrue($data['dataCompleteness']['isComplete']);
        $this->assertGreaterThanOrEqual(70, $data['dataCompleteness']['ordersRetrieved']);
        $this->assertGreaterThanOrEqual(55, $data['dataCompleteness']['arrivalsRetrieved']);
        $this->assertFalse($data['dataCompleteness']['possibleLimitation']);
        $this->assertEquals('Données complètes récupérées avec succès.', $data['dataCompleteness']['recommendation']);
    }
}
