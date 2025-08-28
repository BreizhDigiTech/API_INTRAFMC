<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\ProductCBD;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class QuickOptimizationTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer des données minimales
        User::factory()->count(3)->create();
        Category::factory()->count(2)->create();
        ProductCBD::factory()->count(5)->create();
        Order::factory()->count(3)->create(['status' => 'pending']);
    }

    public function test_orders_summary_works()
    {
        $response = $this->graphQL('
            query {
                ordersSummary {
                    totalOrders
                    pendingOrders
                    totalRevenue
                }
            }
        ');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.ordersSummary'));
    }

    public function test_users_summary_works()
    {
        $response = $this->graphQL('
            query {
                usersSummary {
                    totalUsers
                    activeUsers
                }
            }
        ');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.usersSummary'));
    }

    public function test_categories_list_works()
    {
        $response = $this->graphQL('
            query {
                categoriesList {
                    id
                    name
                    products_count
                }
            }
        ');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.categoriesList'));
    }

    public function test_ecommerce_summary_works()
    {
        $response = $this->graphQL('
            query {
                ecommerceSummary {
                    totalProducts
                    totalCategories
                }
            }
        ');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.ecommerceSummary'));
    }
}
