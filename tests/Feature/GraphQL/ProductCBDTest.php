<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;
use App\Models\ProductCBD;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCBDTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting all products.
     */
    public function test_can_get_all_products()
    {
        // Arrange
        $category = Category::factory()->create(['name' => 'CBD Oils']);
        ProductCBD::factory()->count(3)->create(['category_id' => $category->id]);

        $query = '
            query {
                products {
                    data {
                        id
                        name
                        description
                        price
                        stock
                        category_id
                    }
                    pagination {
                        total
                        per_page
                        current_page
                    }
                }
            }
        ';

        // Act
        $response = $this->authenticatedGraphQL($query, [], ['is_admin' => true]);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'products' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'price',
                            'stock',
                            'category_id',
                        ]
                    ],
                    'pagination' => [
                        'total',
                        'per_page',
                        'current_page',
                    ]
                ]
            ]
        ]);
    }

    /**
     * Test getting a single product.
     */
    public function test_can_get_single_product()
    {
        // Arrange
        $category = Category::factory()->create(['name' => 'CBD Oils']);
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);

        $query = '
            query($id: ID!) {
                product(id: $id) {
                    id
                    name
                    description
                    price
                    stock
                    category_id
                }
            }
        ';

        // Act
        $response = $this->authenticatedGraphQL($query, ['id' => $product->id], ['is_admin' => true]);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.product.id', (string) $product->id);
        $response->assertJsonPath('data.product.name', $product->name);
        $response->assertJsonPath('data.product.price', $product->price);
    }

    /**
     * Test creating a product.
     */
    public function test_can_create_product()
    {
        // Arrange
        $category = Category::factory()->create(['name' => 'CBD Oils']);
        
        $mutation = '
            mutation($input: CreateProductInput!) {
                createProduct(input: $input) {
                    id
                    name
                    description
                    price
                    stock
                    category_id
                }
            }
        ';

        $variables = [
            'input' => [
                'name' => 'New CBD Product',
                'description' => 'A great CBD product',
                'price' => 29.99,
                'stock' => 100,
                'category_id' => $category->id,
            ]
        ];

        // Act
        $response = $this->authenticatedGraphQL($mutation, $variables, ['is_admin' => true]);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.createProduct.name', 'New CBD Product');
        $response->assertJsonPath('data.createProduct.price', 29.99);
        $response->assertJsonPath('data.createProduct.stock', 100);

        // Check database
        $this->assertDatabaseHas('cbd_products', [
            'name' => 'New CBD Product',
            'price' => 29.99,
            'category_id' => $category->id,
        ]);
    }

    /**
     * Test updating a product.
     */
    public function test_can_update_product()
    {
        // Arrange
        $category = Category::factory()->create(['name' => 'CBD Oils']);
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);

        $mutation = '
            mutation($id: ID!, $input: UpdateProductInput!) {
                updateProduct(id: $id, input: $input) {
                    id
                    name
                    description
                    price
                    stock
                    category_id
                }
            }
        ';

        $variables = [
            'id' => $product->id,
            'input' => [
                'name' => 'Updated Product Name',
                'price' => 39.99,
            ]
        ];

        // Act
        $response = $this->authenticatedGraphQL($mutation, $variables, ['is_admin' => true]);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.updateProduct.id', (string) $product->id);
        $response->assertJsonPath('data.updateProduct.name', 'Updated Product Name');
        $response->assertJsonPath('data.updateProduct.price', 39.99);

        // Check database
        $this->assertDatabaseHas('cbd_products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'price' => 39.99,
        ]);
    }

    /**
     * Test deleting a product.
     */
    public function test_can_delete_product()
    {
        // Arrange
        $category = Category::factory()->create(['name' => 'CBD Oils']);
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);

        $mutation = '
            mutation($id: ID!) {
                deleteProduct(id: $id) {
                    message
                }
            }
        ';

        // Act
        $response = $this->authenticatedGraphQL($mutation, ['id' => $product->id], ['is_admin' => true]);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.deleteProduct.message', 'Product deleted successfully');

        // Check database
        $this->assertDatabaseMissing('cbd_products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Test product creation validation.
     */
    public function test_product_creation_validation()
    {
        // Test création de deux produits avec le même nom pour déclencher la validation unique
        $category = Category::factory()->create(['name' => 'CBD Oils']);
        ProductCBD::factory()->create(['name' => 'Existing Product', 'category_id' => $category->id]);

        $mutation = '
            mutation($input: CreateProductInput!) {
                createProduct(input: $input) {
                    id
                    name
                }
            }
        ';

        $variables = [
            'input' => [
                'name' => 'Existing Product', // Nom déjà existant - devrait déclencher la validation unique
                'price' => 29.99,
                'stock' => 100,
                'category_id' => $category->id,
            ]
        ];

        // Act
        $response = $this->authenticatedGraphQL($mutation, $variables, ['is_admin' => true]);

        // Assert - Vérifie qu'il y a une erreur GraphQL (validation Laravel)
        $this->assertGraphQLError($response);
    }

    /**
     * Test authentication required for mutations.
     */
    public function test_authentication_required_for_mutations()
    {
        // Arrange
        $mutation = '
            mutation($input: CreateProductInput!) {
                createProduct(input: $input) {
                    id
                    name
                }
            }
        ';

        $variables = [
            'input' => [
                'name' => 'Test Product',
                'price' => 29.99,
                'stock' => 100,
            ]
        ];

        // Act - No authentication
        $response = $this->graphQL($mutation, $variables);

        // Assert
        $this->assertGraphQLError($response);
    }
}
