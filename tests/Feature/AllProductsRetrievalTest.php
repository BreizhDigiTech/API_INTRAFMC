<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ProductCBD;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class AllProductsRetrievalTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un utilisateur pour l'authentification
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function test_all_products_cbd_returns_correct_order()
    {
        // Créer quelques produits avec des dates différentes
        $oldProduct = ProductCBD::factory()->create([
            'name' => 'Ancien Produit',
            'created_at' => now()->subDays(10)
        ]);
        
        $newProduct = ProductCBD::factory()->create([
            'name' => 'Nouveau Produit',
            'created_at' => now()
        ]);

        $query = '
            query {
                allProductsCBD {
                    id
                    name
                    created_at
                }
            }
        ';

        $response = $this->graphQL($query);

        $response->assertOk();
        $products = $response->json('data.allProductsCBD');
        
        // Vérifier que les produits sont ordonnés du plus récent au plus ancien
        $this->assertNotNull($products);
        $this->assertGreaterThanOrEqual(2, count($products));
        $this->assertEquals($newProduct->name, $products[0]['name']);
        $this->assertEquals($oldProduct->name, $products[1]['name']);
    }

    /** @test */
    public function test_all_products_cbd_no_limit()
    {
        // Créer plus de produits pour tester l'absence de limitation
        ProductCBD::factory(50)->create();

        $query = '
            query {
                allProductsCBD {
                    id
                    name
                }
            }
        ';

        $response = $this->graphQL($query);

        $response->assertOk();
        $products = $response->json('data.allProductsCBD');
        
        // Vérifier qu'on récupère TOUS les produits (au moins 50)
        $this->assertGreaterThanOrEqual(50, count($products));
    }

    /** @test */
    public function test_all_products_cbd_unlimited()
    {
        // Tester qu'il n'y a aucune limitation
        $query = '
            query {
                allProductsCBD {
                    id
                    name
                }
            }
        ';

        $response = $this->graphQL($query);

        $response->assertOk();
        // Le test vérifie juste que la requête fonctionne sans erreur
        $this->assertIsArray($response->json('data.allProductsCBD'));
    }

    /** @test */
    public function test_products_cbd_chronological_order()
    {
        // Créer des produits avec des dates spécifiques
        $product1 = ProductCBD::factory()->create([
            'name' => 'Produit 1',
            'created_at' => now()->subDays(5)
        ]);
        
        $product2 = ProductCBD::factory()->create([
            'name' => 'Produit 2',
            'created_at' => now()->subDays(3)
        ]);
        
        $product3 = ProductCBD::factory()->create([
            'name' => 'Produit 3',
            'created_at' => now()->subDay()
        ]);

        $query = '
            query {
                productsCBD(first: 10) {
                    data {
                        id
                        name
                        created_at
                    }
                }
            }
        ';

        $response = $this->graphQL($query);

        $response->assertOk();
        $products = $response->json('data.productsCBD.data');
        
        // Vérifier l'ordre chronologique inverse (plus récent en premier)
        $this->assertEquals($product3->name, $products[0]['name']);
        $this->assertEquals($product2->name, $products[1]['name']);
        $this->assertEquals($product1->name, $products[2]['name']);
    }
}
