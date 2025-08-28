<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\ProductCBD;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class OptimizationAPIsTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer des données de test
        $this->createTestData();
    }

    private function createTestData()
    {
        // Créer des utilisateurs
        User::factory()->count(5)->create();
        User::factory()->admin()->count(2)->create();
        
        // Créer des catégories
        Category::factory()->count(3)->create();
        
        // Créer des produits
        ProductCBD::factory()->count(10)->create();
        
        // Créer des commandes
        Order::factory()->count(8)->create(['status' => 'pending']);
        Order::factory()->count(5)->create(['status' => 'validated']);
        Order::factory()->count(2)->create(['status' => 'cancelled']);
    }

    public function test_orders_summary_api()
    {
        $response = $this->graphQL('
            query {
                ordersSummary {
                    totalOrders
                    pendingOrders
                    validatedOrders
                    cancelledOrders
                    totalRevenue
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.ordersSummary');
        
        $this->assertEquals(15, $data['totalOrders']); // 8+5+2
        $this->assertEquals(8, $data['pendingOrders']);
        $this->assertEquals(5, $data['validatedOrders']);
        $this->assertEquals(2, $data['cancelledOrders']);
        $this->assertIsFloat($data['totalRevenue']);
    }

    public function test_users_summary_api()
    {
        $response = $this->graphQL('
            query {
                usersSummary {
                    totalUsers
                    activeUsers
                    inactiveUsers
                    adminUsers
                    recentRegistrations
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.usersSummary');
        
        $this->assertEquals(7, $data['totalUsers']); // 5+2
        $this->assertIsInt($data['activeUsers']);
        $this->assertEquals(2, $data['adminUsers']);
    }

    public function test_categories_list_api()
    {
        $response = $this->graphQL('
            query {
                categoriesList {
                    id
                    name
                    slug
                    products_count
                }
            }
        ');

        $response->assertStatus(200);
        $categories = $response->json('data.categoriesList');
        
        $this->assertCount(3, $categories);
        $this->assertArrayHasKey('products_count', $categories[0]);
    }

    public function test_ecommerce_summary_api()
    {
        $response = $this->graphQL('
            query {
                ecommerceSummary {
                    totalProducts
                    totalCategories
                    lowStockProducts
                    outOfStockProducts
                    totalValue
                    averagePrice
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.ecommerceSummary');
        
        $this->assertEquals(10, $data['totalProducts']);
        $this->assertEquals(3, $data['totalCategories']);
        $this->assertIsFloat($data['averagePrice']);
    }

    public function test_dashboard_stats_api()
    {
        $response = $this->graphQL('
            query {
                dashboardStats {
                    orders {
                        total
                        thisMonth
                        lastMonth
                        growth
                    }
                    revenue {
                        total
                        thisMonth
                        lastMonth
                        growth
                    }
                    users {
                        total
                        active
                        newThisMonth
                    }
                    products {
                        total
                        lowStock
                        outOfStock
                    }
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.dashboardStats');
        
        $this->assertArrayHasKey('orders', $data);
        $this->assertArrayHasKey('revenue', $data);
        $this->assertArrayHasKey('users', $data);
        $this->assertArrayHasKey('products', $data);
        
        $this->assertEquals(15, $data['orders']['total']);
        $this->assertEquals(7, $data['users']['total']);
        $this->assertEquals(10, $data['products']['total']);
    }

    public function test_users_search_api()
    {
        $response = $this->graphQL('
            query {
                usersSearch(search: "test", first: 5) {
                    data {
                        id
                        name
                        email
                        is_admin
                        is_active
                    }
                    paginatorInfo {
                        total
                        currentPage
                        lastPage
                    }
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.usersSearch');
        
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('paginatorInfo', $data);
    }

    public function test_products_search_api()
    {
        $response = $this->graphQL('
            query {
                productsSearch(first: 5, inStock: true) {
                    data {
                        id
                        name
                        price
                        stock
                        categories {
                            id
                            name
                        }
                    }
                    paginatorInfo {
                        total
                        currentPage
                    }
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.productsSearch');
        
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('paginatorInfo', $data);
    }

    public function test_validate_token_api_without_auth()
    {
        $response = $this->graphQL('
            query {
                validateToken {
                    valid
                    expires_at
                    user {
                        id
                        name
                    }
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.validateToken');
        
        $this->assertFalse($data['valid']);
        $this->assertNull($data['user']);
    }

    public function test_my_profile_complete_requires_auth()
    {
        $response = $this->graphQL('
            query {
                myProfileComplete {
                    id
                    name
                    email
                    orders_count
                    total_spent
                    preferences {
                        theme
                        language
                        notifications
                    }
                }
            }
        ');

        $response->assertStatus(200);
        $response->assertJsonPath('errors.0.extensions.category', 'authentication');
    }

    public function test_my_profile_complete_with_auth()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'api')->graphQL('
            query {
                myProfileComplete {
                    id
                    name
                    email
                    orders_count
                    total_spent
                    preferences {
                        theme
                        language
                        notifications
                    }
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.myProfileComplete');
        
        $this->assertEquals($user->id, $data['id']);
        $this->assertEquals($user->name, $data['name']);
        $this->assertEquals($user->email, $data['email']);
        $this->assertIsInt($data['orders_count']);
        $this->assertIsFloat($data['total_spent']);
        $this->assertArrayHasKey('preferences', $data);
    }
}
