<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;
use App\Models\Cart;
use App\Models\ProductCBD;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test adding product to cart.
     */
    public function test_can_add_product_to_cart()
    {
        // Arrange
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create([
            'category_id' => $category->id,
            'stock' => 50
        ]);

        $query = '
            mutation AddToCart($input: AddToCartInput!) {
                addToCart(input: $input) {
                    id
                    quantity
                    product {
                        id
                        name
                    }
                    user {
                        id
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'product_id' => $product->id,
                'quantity' => 3
            ]
        ];

        // Act
        $response = $this->authenticatedGraphQL($query, $variables);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonFragment([
            'quantity' => 3
        ]);

        $this->assertDatabaseHas('carts', [
            'product_id' => $product->id,
            'quantity' => 3
        ]);
    }

    /**
     * Test getting user's cart items.
     */
    public function test_can_get_cart_items()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser();
        $category = Category::factory()->create();
        $products = ProductCBD::factory()->count(3)->create(['category_id' => $category->id]);
        
        foreach ($products as $index => $product) {
            Cart::create([
                'user_id' => $auth['user']->id,
                'product_id' => $product->id,
                'quantity' => $index + 1
            ]);
        }

        $query = '
            query {
                myCart {
                    id
                    quantity
                    product {
                        id
                        name
                        price
                    }
                }
            }
        ';

        // Act
        $response = $this->graphQL($query, [], $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $this->assertCount(3, $response->json('data.myCart'));
        
        $response->assertJsonStructure([
            'data' => [
                'myCart' => [
                    '*' => [
                        'id',
                        'quantity',
                        'product' => [
                            'id',
                            'name',
                            'price'
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Test updating cart item quantity.
     */
    public function test_can_update_cart_item_quantity()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser();
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);
        
        $cartItem = Cart::create([
            'user_id' => $auth['user']->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $query = '
            mutation UpdateCartItem($id: ID!, $input: UpdateCartItemInput!) {
                updateCartItem(id: $id, input: $input) {
                    id
                    quantity
                    product {
                        name
                    }
                }
            }
        ';

        $variables = [
            'id' => (string) $cartItem->id,
            'input' => [
                'quantity' => 5
            ]
        ];

        // Act
        $response = $this->graphQL($query, $variables, $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonFragment([
            'quantity' => 5
        ]);

        $this->assertDatabaseHas('carts', [
            'id' => $cartItem->id,
            'quantity' => 5
        ]);
    }

    /**
     * Test removing item from cart.
     */
    public function test_can_remove_item_from_cart()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser();
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);
        
        $cartItem = Cart::create([
            'user_id' => $auth['user']->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $query = '
            mutation RemoveFromCart($id: ID!) {
                removeFromCart(id: $id) {
                    message
                }
            }
        ';

        $variables = ['id' => (string) $cartItem->id];

        // Act
        $response = $this->graphQL($query, $variables, $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonFragment([
            'message' => 'Item removed from cart successfully'
        ]);

        $this->assertDatabaseMissing('carts', [
            'id' => $cartItem->id
        ]);
    }

    /**
     * Test calculating cart total.
     */
    public function test_can_calculate_cart_total()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser();
        $category = Category::factory()->create();
        
        $product1 = ProductCBD::factory()->create([
            'category_id' => $category->id,
            'price' => 10.00
        ]);
        $product2 = ProductCBD::factory()->create([
            'category_id' => $category->id,
            'price' => 15.00
        ]);
        
        Cart::create([
            'user_id' => $auth['user']->id,
            'product_id' => $product1->id,
            'quantity' => 2 // 2 × 10.00 = 20.00
        ]);
        
        Cart::create([
            'user_id' => $auth['user']->id,
            'product_id' => $product2->id,
            'quantity' => 3 // 3 × 15.00 = 45.00
        ]);

        $query = '
            query {
                cartTotal {
                    total
                    itemCount
                }
            }
        ';

        // Act
        $response = $this->graphQL($query, [], $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonFragment([
            'total' => 65.00, // 20.00 + 45.00
            'itemCount' => 5  // 2 + 3
        ]);
    }

    /**
     * Test validation error when adding invalid quantity.
     */
    public function test_cannot_add_negative_quantity_to_cart()
    {
        // Arrange
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);

        $query = '
            mutation AddToCart($input: AddToCartInput!) {
                addToCart(input: $input) {
                    id
                    quantity
                }
            }
        ';

        $variables = [
            'input' => [
                'product_id' => $product->id,
                'quantity' => -1 // Invalid negative quantity
            ]
        ];

        // Act
        $response = $this->authenticatedGraphQL($query, $variables);

        // Assert
        $this->assertGraphQLError($response);
    }

    /**
     * Test adding product with insufficient stock.
     */
    public function test_cannot_add_more_than_available_stock()
    {
        // Arrange
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create([
            'category_id' => $category->id,
            'stock' => 5 // Only 5 items in stock
        ]);

        $query = '
            mutation AddToCart($input: AddToCartInput!) {
                addToCart(input: $input) {
                    id
                    quantity
                }
            }
        ';

        $variables = [
            'input' => [
                'product_id' => $product->id,
                'quantity' => 10 // Trying to add more than available
            ]
        ];

        // Act
        $response = $this->authenticatedGraphQL($query, $variables);

        // Assert
        $this->assertGraphQLError($response);
    }
}
