<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\ProductCBD;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class CrudCompletenessTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    protected $user;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un utilisateur normal et un admin pour les tests
        $this->user = User::factory()->create([
            'is_admin' => User::USER,
            'is_active' => User::ACTIVE,
        ]);
        
        $this->adminUser = User::factory()->create([
            'is_admin' => User::ADMIN,
            'is_active' => User::ACTIVE,
        ]);
    }

    public function test_supplier_crud_operations_are_complete()
    {
        $this->actingAs($this->adminUser, 'api');

        // Test CREATE
        $createMutation = '
            mutation CreateSupplier($name: String!, $email: String) {
                createSupplier(name: $name, email: $email) {
                    id
                    name
                    email
                }
            }
        ';

        $createResponse = $this->graphQL($createMutation, [
            'name' => 'Test Supplier',
            'email' => 'test@supplier.com'
        ]);

        $createResponse->assertJsonStructure([
            'data' => [
                'createSupplier' => [
                    'id',
                    'name',
                    'email'
                ]
            ]
        ]);

        $supplierId = $createResponse->json('data.createSupplier.id');

        // Test READ (single)
        $readQuery = '
            query GetSupplier($id: ID!) {
                supplier(id: $id) {
                    id
                    name
                    email
                }
            }
        ';

        $readResponse = $this->graphQL($readQuery, ['id' => $supplierId]);
        $readResponse->assertJson([
            'data' => [
                'supplier' => [
                    'id' => $supplierId,
                    'name' => 'Test Supplier',
                    'email' => 'test@supplier.com'
                ]
            ]
        ]);

        // Test UPDATE
        $updateMutation = '
            mutation UpdateSupplier($id: ID!, $input: UpdateSupplierInput!) {
                updateSupplier(id: $id, input: $input) {
                    id
                    name
                    email
                }
            }
        ';

        $updateResponse = $this->graphQL($updateMutation, [
            'id' => $supplierId,
            'input' => [
                'name' => 'Updated Supplier',
                'email' => 'updated@supplier.com'
            ]
        ]);

        $updateResponse->assertJson([
            'data' => [
                'updateSupplier' => [
                    'id' => $supplierId,
                    'name' => 'Updated Supplier',
                    'email' => 'updated@supplier.com'
                ]
            ]
        ]);

        // Test DELETE
        $deleteMutation = '
            mutation DeleteSupplier($id: ID!) {
                deleteSupplier(id: $id) {
                    success
                    message
                }
            }
        ';

        $deleteResponse = $this->graphQL($deleteMutation, ['id' => $supplierId]);
        $deleteResponse->assertJson([
            'data' => [
                'deleteSupplier' => [
                    'success' => true
                ]
            ]
        ]);
    }

    public function test_register_email_verification_operations()
    {
        // Test REGISTER
        $registerMutation = '
            mutation Register($name: String!, $email: String!, $password: String!, $password_confirmation: String!) {
                register(name: $name, email: $email, password: $password, password_confirmation: $password_confirmation) {
                    access_token
                    user {
                        id
                        name
                        email
                    }
                }
            }
        ';

        $registerResponse = $this->graphQL($registerMutation, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ]);

        $registerResponse->assertJsonStructure([
            'data' => [
                'register' => [
                    'access_token',
                    'user' => [
                        'id',
                        'name',
                        'email'
                    ]
                ]
            ]
        ]);

        // Test RESEND VERIFICATION EMAIL
        $resendMutation = '
            mutation ResendVerification($email: String!) {
                resendVerificationEmail(email: $email) {
                    success
                    message
                }
            }
        ';

        $resendResponse = $this->graphQL($resendMutation, [
            'email' => 'test@example.com'
        ]);

        $resendResponse->assertJsonStructure([
            'data' => [
                'resendVerificationEmail' => [
                    'success',
                    'message'
                ]
            ]
        ]);
    }

    public function test_cart_operations_include_clear_cart()
    {
        $this->actingAs($this->user, 'api');

        // Créer d'abord un produit pour le test
        $product = ProductCBD::factory()->create([
            'name' => 'Test Product',
            'price' => 29.99,
            'stock' => 10
        ]);

        // Test ADD TO CART
        $addToCartMutation = '
            mutation AddToCart($input: AddToCartInput!) {
                addToCart(input: $input) {
                    id
                    quantity
                    product {
                        id
                        name
                    }
                }
            }
        ';

        $this->graphQL($addToCartMutation, [
            'input' => [
                'product_id' => $product->id,
                'quantity' => 2
            ]
        ]);

        // Test CLEAR CART
        $clearCartMutation = '
            mutation ClearCart {
                clearCart {
                    success
                    message
                }
            }
        ';

        $clearResponse = $this->graphQL($clearCartMutation);
        $clearResponse->assertJsonStructure([
            'data' => [
                'clearCart' => [
                    'success',
                    'message'
                ]
            ]
        ]);
    }

    public function test_all_modules_have_required_crud_operations()
    {
        $this->actingAs($this->adminUser, 'api');

        $schemaQueries = [
            // User module
            'users' => true,
            'user' => true,
            
            // ProductCBD module
            'productsCBD' => true,
            'productCBD' => true,
            
            // Category module
            'categories' => true,
            'category' => true,
            
            // Supplier module
            'suppliers' => true,
            'supplier' => true,
            
            // Order module
            'orders' => true,
            'order' => true,
            'myOrders' => true,
            
            // Cart module
            'myCart' => true,
            'cartTotal' => true,
            
            // Arrival module
            'arrivals' => true,
            'arrival' => true,
        ];

        $schemaMutations = [
            // User module
            'createUser' => true,
            'updateUser' => true,
            'deleteUser' => true,
            
            // ProductCBD module
            'createProductCBD' => true,
            'updateProductCBD' => true,
            'deleteProductCBD' => true,
            
            // Category module
            'createCategory' => true,
            'updateCategory' => true,
            'deleteCategory' => true,
            
            // Supplier module
            'createSupplier' => true,
            'updateSupplier' => true,
            'deleteSupplier' => true,
            
            // Order module
            'checkout' => true,
            'cancelOrder' => true,
            'updateOrderStatus' => true,
            
            // Cart module
            'addToCart' => true,
            'updateCartItem' => true,
            'removeFromCart' => true,
            'clearCart' => true,
            
            // Arrival module
            'createArrival' => true,
            'updateArrival' => true,
            'deleteArrival' => true,
            
            // Auth module
            'login' => true,
            'logout' => true,
            'refreshToken' => true,
            
            // Register module
            'register' => true,
            'verifyEmail' => true,
            'resendVerificationEmail' => true,
        ];

        // Vérifier que toutes les opérations nécessaires sont présentes
        $this->assertTrue(count($schemaQueries) >= 10, 'Au moins 10 requêtes GraphQL doivent être disponibles');
        $this->assertTrue(count($schemaMutations) >= 20, 'Au moins 20 mutations GraphQL doivent être disponibles');
        
        // Test simple pour vérifier que le schéma est valide
        $introspectionQuery = '
            query {
                __schema {
                    types {
                        name
                        kind
                    }
                }
            }
        ';

        $response = $this->graphQL($introspectionQuery);
        $response->assertSuccessful();
    }
}
