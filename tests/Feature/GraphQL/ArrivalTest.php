<?php

namespace Tests\Feature\GraphQL;

use App\Models\User;
use App\Models\CbdArrival;
use App\Models\ProductCBD;
use App\Models\ArrivalProductCbd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArrivalTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un utilisateur admin
        $this->admin = User::factory()->create([
            'is_admin' => true,
            'email' => 'admin@test.com'
        ]);

        // Créer un utilisateur normal
        $this->user = User::factory()->create([
            'is_admin' => false,
            'email' => 'user@test.com'
        ]);

        // Créer un produit pour les tests
        $this->product = ProductCBD::create([
            'name' => 'Produit Test',
            'description' => 'Description test',
            'price' => 29.99,
            'stock' => 10
        ]);
    }

    /** @test */
    public function admin_can_get_all_arrivals()
    {
        // Créer quelques arrivages
        $arrival1 = CbdArrival::create([
            'amount' => 100.50,
            'status' => 'pending'
        ]);
        
        $arrival2 = CbdArrival::create([
            'amount' => 200.75,
            'status' => 'validated'
        ]);

        $response = $this->actingAs($this->admin, 'api')->postJson('/graphql', [
            'query' => '
                query {
                    arrivals {
                        id
                        amount
                        status
                        products {
                            id
                            quantity
                            unit_price
                        }
                    }
                }
            '
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'arrivals' => [
                    '*' => [
                        'id',
                        'amount',
                        'status',
                        'products'
                    ]
                ]
            ]
        ]);

        $arrivals = $response->json('data.arrivals');
        $this->assertCount(2, $arrivals);
    }

    /** @test */
    public function regular_user_cannot_get_arrivals()
    {
        $response = $this->actingAs($this->user, 'api')->postJson('/graphql', [
            'query' => '
                query {
                    arrivals {
                        id
                        amount
                        status
                    }
                }
            '
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('errors.0.message', 'Acces refuse');
        $response->assertJsonPath('errors.0.extensions.reason', 'Vous n\'avez pas les permissions necessaires pour voir la liste des arrivages.');
    }

    /** @test */
    public function admin_can_get_specific_arrival()
    {
        $arrival = CbdArrival::create([
            'amount' => 150.25,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->admin, 'api')->postJson('/graphql', [
            'query' => '
                query($arrival_id: ID!) {
                    arrival(arrival_id: $arrival_id) {
                        id
                        amount
                        status
                        products {
                            id
                        }
                    }
                }
            ',
            'variables' => [
                'arrival_id' => $arrival->id
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'amount' => 150.25,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function admin_can_create_arrival()
    {
        $response = $this->actingAs($this->admin, 'api')->postJson('/graphql', [
            'query' => '
                mutation($input: CreateArrivalInput!) {
                    createArrival(input: $input) {
                        id
                        amount
                        status
                        products {
                            id
                            product_id
                            quantity
                            unit_price
                        }
                    }
                }
            ',
            'variables' => [
                'input' => [
                    'amount' => 200.50,
                    'status' => 'pending',
                    'products' => [
                        [
                            'product_id' => $this->product->id,
                            'quantity' => 5,
                            'unit_price' => 40.10
                        ]
                    ]
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'amount' => 200.50,
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('cbd_arrivals', [
            'amount' => 200.50,
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('arrival_product_cbd', [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 40.10
        ]);
    }

    /** @test */
    public function regular_user_cannot_create_arrival()
    {
        $response = $this->actingAs($this->user, 'api')->postJson('/graphql', [
            'query' => '
                mutation($input: CreateArrivalInput!) {
                    createArrival(input: $input) {
                        id
                        amount
                    }
                }
            ',
            'variables' => [
                'input' => [
                    'amount' => 200.50,
                    'status' => 'pending',
                    'products' => [
                        [
                            'product_id' => $this->product->id,
                            'quantity' => 5,
                            'unit_price' => 40.10
                        ]
                    ]
                ]
            ]
        ]);

        $response->assertStatus(200);
        // Le directive @can(ability: "admin") devrait bloquer les utilisateurs non-admin
        $this->assertTrue($response->json('errors') !== null);
    }

    /** @test */
    public function admin_can_validate_arrival()
    {
        $arrival = CbdArrival::create([
            'amount' => 150.25,
            'status' => 'pending'
        ]);

        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
            'unit_price' => 50.08
        ]);

        $initialStock = $this->product->stock;

        $response = $this->actingAs($this->admin, 'api')->postJson('/graphql', [
            'query' => '
                mutation($arrival_id: ID!) {
                    validateArrival(arrival_id: $arrival_id) {
                        id
                        status
                    }
                }
            ',
            'variables' => [
                'arrival_id' => $arrival->id
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'status' => 'validated'
        ]);

        // Vérifier que le stock a été mis à jour
        $this->product->refresh();
        $this->assertEquals($initialStock + 3, $this->product->stock);
    }

    /** @test */
    public function admin_can_update_arrival()
    {
        $arrival = CbdArrival::create([
            'amount' => 150.25,
            'status' => 'pending'
        ]);

        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
            'unit_price' => 50.08
        ]);

        $response = $this->actingAs($this->admin, 'api')->postJson('/graphql', [
            'query' => '
                mutation($arrival_id: ID!, $input: UpdateArrivalInput!) {
                    updateArrival(arrival_id: $arrival_id, input: $input) {
                        id
                        amount
                        status
                    }
                }
            ',
            'variables' => [
                'arrival_id' => $arrival->id,
                'input' => [
                    'amount' => 300.75,
                    'status' => 'pending'
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'amount' => 300.75,
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('cbd_arrivals', [
            'id' => $arrival->id,
            'amount' => 300.75
        ]);
    }

    /** @test */
    public function admin_can_delete_arrival()
    {
        $arrival = CbdArrival::create([
            'amount' => 150.25,
            'status' => 'pending'  // Seuls les arrivages non validés peuvent être supprimés
        ]);

        $response = $this->actingAs($this->admin, 'api')->postJson('/graphql', [
            'query' => '
                mutation($arrival_id: ID!) {
                    deleteArrival(arrival_id: $arrival_id) {
                        id
                        status
                    }
                }
            ',
            'variables' => [
                'arrival_id' => $arrival->id
            ]
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('cbd_arrivals', [
            'id' => $arrival->id
        ]);
    }

    /** @test */
    public function returns_error_when_arrival_not_found()
    {
        $response = $this->actingAs($this->admin, 'api')->postJson('/graphql', [
            'query' => '
                query($arrival_id: ID!) {
                    arrival(arrival_id: $arrival_id) {
                        id
                        amount
                    }
                }
            ',
            'variables' => [
                'arrival_id' => 999
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('errors.0.message', 'Arrivage introuvable');
        $response->assertJsonPath('errors.0.extensions.reason', "Aucun arrivage n'a ete trouve avec cet identifiant.");
    }
}
