<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Cart;
use App\Models\ProductCBD;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test checkout process - converting cart to order.
     */
    public function test_can_checkout_cart_to_order()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        $user = $auth['user'];
        
        $category = Category::factory()->create();
        $product1 = ProductCBD::factory()->create(['price' => 10.00, 'stock' => 50, 'category_id' => $category->id]);
        $product2 = ProductCBD::factory()->create(['price' => 15.00, 'stock' => 30, 'category_id' => $category->id]);
        
        // Add items to cart
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 2
        ]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'quantity' => 1
        ]);

        $mutation = '
            mutation {
                checkout {
                    id
                    total
                    status
                    products {
                        id
                        name
                        pivot {
                            quantity
                            unit_price
                        }
                    }
                    user {
                        id
                    }
                }
            }
        ';

        // Act
        $response = $this->graphQL($mutation, [], $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.checkout.total', 35); // 2*10 + 1*15
        $response->assertJsonPath('data.checkout.status', 'pending');
        
        // Check database
        $this->assertDatabaseHas('orders', [
            'total' => 35,
            'status' => 'pending',
        ]);
        
        // Check order_product pivot
        $this->assertDatabaseHas('order_product', [
            'product_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => 10.00,
        ]);
        
        // Cart should be empty after checkout
        $this->assertDatabaseCount('carts', 0);
    }

    /**
     * Test getting user's orders.
     */
    public function test_can_get_user_orders()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        $user = $auth['user'];
        
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 25.50
        ]);
        $order->products()->attach($product->id, [
            'quantity' => 2,
            'unit_price' => 12.75
        ]);

        $query = '
            query {
                orders {
                    id
                    total
                    status
                    products {
                        id
                        name
                        pivot {
                            quantity
                            unit_price
                        }
                    }
                }
            }
        ';

        // Act
        $response = $this->graphQL($query, [], $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.orders.0.total', 25.5);
        $response->assertJsonStructure([
            'data' => [
                'orders' => [
                    '*' => [
                        'id',
                        'total',
                        'status',
                        'products' => [
                            '*' => [
                                'id',
                                'name',
                                'pivot' => [
                                    'quantity',
                                    'unit_price'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Test getting a specific order.
     */
    public function test_can_get_single_order()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        $user = $auth['user'];
        
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create(['category_id' => $category->id]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 42.00
        ]);
        $order->products()->attach($product->id, [
            'quantity' => 3,
            'unit_price' => 14.00
        ]);

        $query = '
            query($id: ID!) {
                order(id: $id) {
                    id
                    total
                    status
                    products {
                        id
                        name
                        pivot {
                            quantity
                            unit_price
                        }
                    }
                    user {
                        id
                    }
                }
            }
        ';

        // Act
        $response = $this->graphQL($query, ['id' => $order->id], $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.order.id', (string) $order->id);
        $response->assertJsonPath('data.order.total', 42);
    }

    /**
     * Test cancelling an order.
     */
    public function test_can_cancel_order()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        $user = $auth['user'];
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        $mutation = '
            mutation($id: ID!) {
                cancelOrder(id: $id)
            }
        ';

        // Act
        $response = $this->graphQL($mutation, ['id' => $order->id], $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.cancelOrder', true);
        
        // Check database
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled'
        ]);
    }

    /**
     * Test checkout with empty cart fails.
     */
    public function test_cannot_checkout_empty_cart()
    {
        // Arrange
        $mutation = '
            mutation {
                checkout {
                    id
                    total
                }
            }
        ';

        // Act
        $response = $this->authenticatedGraphQL($mutation);

        // Assert
        $this->assertGraphQLError($response);
    }

    /**
     * Test user can only access their own orders.
     */
    public function test_cannot_access_other_user_orders()
    {
        // Arrange
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $query = '
            query($id: ID!) {
                order(id: $id) {
                    id
                    total
                }
            }
        ';

        // Act
        $response = $this->authenticatedGraphQL($query, ['id' => $order->id]);

        // Assert
        $this->assertGraphQLError($response);
    }
}
