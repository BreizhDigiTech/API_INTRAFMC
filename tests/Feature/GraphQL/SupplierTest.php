<?php

namespace Tests\Feature\GraphQL;

use App\Models\User;
use App\Models\Supplier;
use App\Models\ProductCBD;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;

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
    }

    /** @test */
    public function admin_can_get_all_suppliers()
    {
        // Créer quelques fournisseurs
        $supplier1 = Supplier::create([
            'name' => 'Fournisseur 1',
            'email' => 'fournisseur1@test.com',
            'phone' => '0123456789'
        ]);
        
        $supplier2 = Supplier::create([
            'name' => 'Fournisseur 2',
            'email' => 'fournisseur2@test.com',
            'phone' => '0987654321'
        ]);

        $response = $this->actingAs($this->admin, 'api')->postJson('/graphql', [
            'query' => '
                query {
                    suppliers {
                        id
                        name
                        email
                        phone
                        products {
                            id
                        }
                    }
                }
            '
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'suppliers' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'products'
                    ]
                ]
            ]
        ]);

        $suppliers = $response->json('data.suppliers');
        $this->assertCount(2, $suppliers);
        $this->assertEquals('Fournisseur 1', $suppliers[0]['name']);
        $this->assertEquals('Fournisseur 2', $suppliers[1]['name']);
    }

    /** @test */
    public function regular_user_cannot_get_suppliers()
    {
        $response = $this->actingAs($this->user, 'api')->postJson('/graphql', [
            'query' => '
                query {
                    suppliers {
                        id
                        name
                    }
                }
            '
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('errors.0.message', 'Acces refuse');
        $response->assertJsonPath('errors.0.extensions.reason', 'Vous n\'avez pas les permissions necessaires pour voir la liste des fournisseurs.');
    }

    /** @test */
    public function admin_can_get_specific_supplier()
    {
        $supplier = Supplier::create([
            'name' => 'Fournisseur Test',
            'email' => 'test@test.com',
            'phone' => '0123456789'
        ]);

        $response = $this->actingAs($this->admin, 'api')->postJson('/graphql', [
            'query' => '
                query($id: ID!) {
                    supplier(id: $id) {
                        id
                        name
                        email
                        phone
                        products {
                            id
                        }
                    }
                }
            ',
            'variables' => [
                'id' => $supplier->id
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Fournisseur Test',
            'email' => 'test@test.com',
            'phone' => '0123456789'
        ]);
    }

    /** @test */
    public function admin_can_create_supplier()
    {
        $response = $this->actingAs($this->admin, 'api')->postJson('/graphql', [
            'query' => '
                mutation($name: String!, $email: String, $phone: String) {
                    createSupplier(name: $name, email: $email, phone: $phone) {
                        id
                        name
                        email
                        phone
                    }
                }
            ',
            'variables' => [
                'name' => 'Nouveau Fournisseur',
                'email' => 'nouveau@test.com',
                'phone' => '0123456789'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Nouveau Fournisseur',
            'email' => 'nouveau@test.com',
            'phone' => '0123456789'
        ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Nouveau Fournisseur',
            'email' => 'nouveau@test.com'
        ]);
    }

    /** @test */
    public function regular_user_cannot_create_supplier()
    {
        $response = $this->actingAs($this->user, 'api')->postJson('/graphql', [
            'query' => '
                mutation($name: String!) {
                    createSupplier(name: $name) {
                        id
                        name
                    }
                }
            ',
            'variables' => [
                'name' => 'Nouveau Fournisseur'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('errors.0.message', 'Acces refuse');
        $response->assertJsonPath('errors.0.extensions.reason', 'Vous n\'avez pas les permissions necessaires pour creer un fournisseur.');
    }

    /** @test */
    public function admin_can_attach_supplier_to_product()
    {
        $supplier = Supplier::create([
            'name' => 'Fournisseur Test',
            'email' => 'test@test.com'
        ]);

        $product = ProductCBD::create([
            'name' => 'Produit Test',
            'description' => 'Description test',
            'price' => 29.99,
            'stock' => 100
        ]);

        $response = $this->actingAs($this->admin, 'api')->postJson('/graphql', [
            'query' => '
                mutation($supplier_id: ID!, $product_id: ID!) {
                    attachSupplierToProduct(supplier_id: $supplier_id, product_id: $product_id) {
                        id
                        name
                        products {
                            id
                            name
                        }
                    }
                }
            ',
            'variables' => [
                'supplier_id' => $supplier->id,
                'product_id' => $product->id
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Fournisseur Test'
        ]);

        // Vérifier que la relation a été créée
        $this->assertDatabaseHas('product_supplier', [
            'supplier_id' => $supplier->id,
            'product_id' => $product->id
        ]);
    }

    /** @test */
    public function admin_can_detach_supplier_from_product()
    {
        $supplier = Supplier::create([
            'name' => 'Fournisseur Test',
            'email' => 'test@test.com'
        ]);

        $product = ProductCBD::create([
            'name' => 'Produit Test',
            'description' => 'Description test',
            'price' => 29.99,
            'stock' => 100
        ]);

        // Attacher d'abord
        $supplier->products()->attach($product->id);

        $response = $this->actingAs($this->admin, 'api')->postJson('/graphql', [
            'query' => '
                mutation($supplier_id: ID!, $product_id: ID!) {
                    detachSupplierFromProduct(supplier_id: $supplier_id, product_id: $product_id) {
                        id
                        name
                        products {
                            id
                            name
                        }
                    }
                }
            ',
            'variables' => [
                'supplier_id' => $supplier->id,
                'product_id' => $product->id
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Fournisseur Test'
        ]);

        // Vérifier que la relation a été supprimée
        $this->assertDatabaseMissing('product_supplier', [
            'supplier_id' => $supplier->id,
            'product_id' => $product->id
        ]);
    }

    /** @test */
    public function returns_error_when_supplier_not_found()
    {
        $response = $this->actingAs($this->admin, 'api')->postJson('/graphql', [
            'query' => '
                query($id: ID!) {
                    supplier(id: $id) {
                        id
                        name
                    }
                }
            ',
            'variables' => [
                'id' => 999
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('errors.0.message', 'Fournisseur introuvable');
        $response->assertJsonPath('errors.0.extensions.reason', "Aucun fournisseur n'a ete trouve avec cet identifiant.");
    }
}
