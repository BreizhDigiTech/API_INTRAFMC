<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;
use App\Models\Category;
use App\Models\ProductCBD;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting all categories.
     */
    public function test_can_get_all_categories()
    {
        // Arrange
        Category::factory()->count(3)->create();

        $query = '
            query {
                categories {
                    id
                    name
                    description
                    created_at
                }
            }
        ';

        // Act
        $response = $this->authenticatedGraphQL($query);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'categories' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'created_at'
                    ]
                ]
            ]
        ]);

        $this->assertCount(3, $response->json('data.categories'));
    }

    /**
     * Test getting category with products.
     */
    public function test_can_get_category_with_products()
    {
        // Arrange
        $category = Category::factory()->create(['name' => 'CBD Oils']);
        ProductCBD::factory()->count(2)->create(['category_id' => $category->id]);

        $query = '
            query GetCategory($id: ID!) {
                category(id: $id) {
                    id
                    name
                    products {
                        id
                        name
                        price
                    }
                }
            }
        ';

        $variables = ['id' => (string) $category->id];

        // Act
        $response = $this->authenticatedGraphQL($query, $variables, ['is_admin' => true]);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonFragment(['name' => 'CBD Oils']);
        $this->assertCount(2, $response->json('data.category.products'));
    }

    /**
     * Test creating a new category.
     */
    public function test_can_create_category()
    {
        // Arrange
        $query = '
            mutation CreateCategory($input: CreateCategoryInput!) {
                createCategory(input: $input) {
                    id
                    name
                    description
                }
            }
        ';

        $variables = [
            'input' => [
                'name' => 'New Category',
                'description' => 'A new category for testing'
            ]
        ];

        // Act
        $response = $this->authenticatedGraphQL($query, $variables, ['is_admin' => true]);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonFragment([
            'name' => 'New Category',
            'description' => 'A new category for testing'
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'New Category'
        ]);
    }

    /**
     * Test updating a category.
     */
    public function test_can_update_category()
    {
        // Arrange
        $category = Category::factory()->create([
            'name' => 'Original Category',
            'description' => 'Original description'
        ]);

        $query = '
            mutation UpdateCategory($id: ID!, $input: UpdateCategoryInput!) {
                updateCategory(id: $id, input: $input) {
                    id
                    name
                    description
                }
            }
        ';

        $variables = [
            'id' => (string) $category->id,
            'input' => [
                'name' => 'Updated Category',
                'description' => 'Updated description'
            ]
        ];

        // Act
        $response = $this->authenticatedGraphQL($query, $variables, ['is_admin' => true]);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonFragment([
            'name' => 'Updated Category',
            'description' => 'Updated description'
        ]);
    }

    /**
     * Test deleting a category.
     */
    public function test_can_delete_category()
    {
        // Arrange
        $category = Category::factory()->create();

        $query = '
            mutation DeleteCategory($id: ID!) {
                deleteCategory(id: $id) {
                    message
                }
            }
        ';

        $variables = ['id' => (string) $category->id];

        // Act
        $response = $this->authenticatedGraphQL($query, $variables, ['is_admin' => true]);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonFragment([
            'message' => 'Category deleted successfully'
        ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    /**
     * Test category name uniqueness validation.
     */
    public function test_category_name_must_be_unique()
    {
        // Arrange
        Category::factory()->create(['name' => 'Existing Category']);

        $query = '
            mutation CreateCategory($input: CreateCategoryInput!) {
                createCategory(input: $input) {
                    id
                    name
                }
            }
        ';

        $variables = [
            'input' => [
                'name' => 'Existing Category', // Duplicate name
                'description' => 'Test description'
            ]
        ];

        // Act
        $response = $this->authenticatedGraphQL($query, $variables, ['is_admin' => true]);

        // Assert
        $this->assertGraphQLError($response, 'The name has already been taken.');
        $response->assertJsonFragment(['validation' => ['name' => ['The name has already been taken.']]]);
    }
}
