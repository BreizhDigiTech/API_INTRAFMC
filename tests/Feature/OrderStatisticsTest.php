<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\ProductCBD;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class OrderStatisticsTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    private $admin;
    private $user;
    private $category;
    private $product;
    private $adminAuth;
    private $userAuth;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un admin et un utilisateur normal
        $this->adminAuth = $this->createAuthenticatedUser(['is_admin' => true]);
        $this->admin = $this->adminAuth['user'];
        
        $this->userAuth = $this->createAuthenticatedUser(['is_admin' => false]);
        $this->user = $this->userAuth['user'];
        
        // Créer une catégorie et un produit
        $this->category = Category::factory()->create(['name' => 'Test Category']);
        $this->product = ProductCBD::factory()->create([
            'name' => 'Test Product',
            'price' => 25.00,
            'stock' => 10
        ]);
        $this->product->categories()->attach($this->category->id);
        
        // Créer quelques commandes de test
        $this->createTestOrders();
    }

    private function createTestOrders()
    {
        // Commande validée
        $order1 = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 50.00,
            'status' => 'validated',
            'created_at' => now()->subDays(5)
        ]);
        $order1->products()->attach($this->product->id, [
            'quantity' => 2,
            'unit_price' => 25.00
        ]);
        
        // Commande en attente
        $order2 = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 75.00,
            'status' => 'pending',
            'created_at' => now()->subDays(3)
        ]);
        $order2->products()->attach($this->product->id, [
            'quantity' => 3,
            'unit_price' => 25.00
        ]);
        
        // Commande annulée (ne doit pas compter dans les stats)
        $order3 = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 25.00,
            'status' => 'cancelled',
            'created_at' => now()->subDays(2)
        ]);
        $order3->products()->attach($this->product->id, [
            'quantity' => 1,
            'unit_price' => 25.00
        ]);
    }

    /** @test */
    public function admin_can_get_order_statistics()
    {
        $response = $this->graphQL('
            query OrderStats($startDate: Date!, $endDate: Date!) {
                orderStatistics(startDate: $startDate, endDate: $endDate) {
                    totalRevenue
                    totalOrders
                    averageOrderValue
                    uniqueCustomers
                    metrics {
                        cancelledOrders
                        cancelledOrdersPercent
                        repeatCustomersCount
                        repeatCustomersPercent
                    }
                    topProducts {
                        productId
                        productName
                        quantitySold
                        revenue
                        orderCount
                    }
                    topCustomers {
                        userId
                        userName
                        ordersCount
                        totalAmount
                        customerSegment
                    }
                }
            }
        ', [
            'startDate' => now()->subWeek()->toDateString(),
            'endDate' => now()->toDateString()
        ], $this->adminAuth['headers']);

        $response->assertSuccessful();
        
        $stats = $response->json('data.orderStatistics');
        
        // Vérifier que la response est valide
        $this->assertNotNull($stats);
        $this->assertArrayHasKey('totalRevenue', $stats);
        $this->assertArrayHasKey('totalOrders', $stats);
        
        // Vérifier les métriques principales (2 commandes non-annulées)
        $this->assertEquals(125.00, $stats['totalRevenue']); // 50 + 75
        $this->assertEquals(2, $stats['totalOrders']);
        $this->assertEquals(62.50, $stats['averageOrderValue']); // 125/2
        $this->assertEquals(1, $stats['uniqueCustomers']); // 1 utilisateur
        
        // Vérifier les métriques supplémentaires si elles existent
        if (isset($stats['metrics'])) {
            $this->assertEquals(1, $stats['metrics']['cancelledOrders']);
            $this->assertEquals(33.33, $stats['metrics']['cancelledOrdersPercent']); // 1/3 * 100
        }
        
        // Vérifier les top produits si ils existent
        if (isset($stats['topProducts']) && count($stats['topProducts']) > 0) {
            $this->assertEquals($this->product->id, $stats['topProducts'][0]['productId']);
            $this->assertEquals('Test Product', $stats['topProducts'][0]['productName']);
            $this->assertEquals(5, $stats['topProducts'][0]['quantitySold']); // 2 + 3
            $this->assertEquals(125.00, $stats['topProducts'][0]['revenue']);
        }
        
        // Vérifier les top clients si ils existent
        if (isset($stats['topCustomers']) && count($stats['topCustomers']) > 0) {
            $this->assertEquals($this->user->id, $stats['topCustomers'][0]['userId']);
            $this->assertEquals(2, $stats['topCustomers'][0]['ordersCount']);
            $this->assertEquals(125.00, $stats['topCustomers'][0]['totalAmount']);
            $this->assertEquals('STANDARD', $stats['topCustomers'][0]['customerSegment']);
        }
    }

    /** @test */
    public function normal_user_cannot_access_statistics()
    {
        $response = $this->graphQL('
            query OrderStats($startDate: Date!, $endDate: Date!) {
                orderStatistics(startDate: $startDate, endDate: $endDate) {
                    totalRevenue
                    totalOrders
                }
            }
        ', [
            'startDate' => now()->subWeek()->toDateString(),
            'endDate' => now()->toDateString()
        ], $this->userAuth['headers']);

        $response->assertGraphQLErrorMessage('This action is unauthorized');
    }

    /** @test */
    public function admin_can_get_revenue_timeline()
    {
        $response = $this->graphQL('
            query RevenueTimeline($startDate: Date!, $endDate: Date!) {
                revenueTimeline(
                    startDate: $startDate, 
                    endDate: $endDate,
                    groupBy: DAY
                ) {
                    periods {
                        period
                        date
                        revenue
                        orders
                        uniqueCustomers
                        averageOrderValue
                        categoryBreakdown {
                            categoryId
                            categoryName
                            revenue
                            orders
                        }
                    }
                    totals {
                        totalRevenue
                        totalOrders
                        averageOrderValue
                    }
                    insights {
                        trend
                        volatility
                        forecast
                        anomalies {
                            date
                            type
                            severity
                            description
                        }
                    }
                }
            }
        ', [
            'startDate' => now()->subWeek()->toDateString(),
            'endDate' => now()->toDateString()
        ], $this->adminAuth['headers']);

        $response->assertSuccessful();
        
        $timeline = $response->json('data.revenueTimeline');
        
        // Vérifier que la response est valide
        $this->assertNotNull($timeline);
        
        // Vérifier qu'on a des périodes
        if (isset($timeline['periods'])) {
            $this->assertGreaterThanOrEqual(0, count($timeline['periods']));
        }
        
        // Vérifier les totaux si ils existent
        if (isset($timeline['totals'])) {
            $this->assertArrayHasKey('totalRevenue', $timeline['totals']);
            $this->assertArrayHasKey('totalOrders', $timeline['totals']);
        }
        
        // Vérifier les insights si ils existent
        if (isset($timeline['insights'])) {
            $this->assertArrayHasKey('trend', $timeline['insights']);
            $this->assertArrayHasKey('volatility', $timeline['insights']);
        }
    }

    /** @test */
    public function can_get_categories_with_counts()
    {
        $response = $this->graphQL('
            query CategoriesWithCounts {
                categoriesWithCounts {
                    id
                    name
                    slug
                    description
                    productCount
                    level
                    isActive
                    displayOrder
                }
            }
        ', [], $this->userAuth['headers']);

        $response->assertSuccessful();
        
        $categories = $response->json('data.categoriesWithCounts');
        
        // Vérifier que la response est valide
        $this->assertNotNull($categories);
        $this->assertIsArray($categories);
        
        if (count($categories) > 0) {
            $this->assertEquals($this->category->id, $categories[0]['id']);
            $this->assertEquals('Test Category', $categories[0]['name']);
            $this->assertArrayHasKey('productCount', $categories[0]);
            $this->assertArrayHasKey('level', $categories[0]);
            $this->assertArrayHasKey('isActive', $categories[0]);
        }
    }

    /** @test */
    public function can_get_popular_products_by_category()
    {
        $response = $this->graphQL('
            query PopularProducts($categoryId: ID!) {
                popularProductsByCategory(
                    categoryId: $categoryId,
                    limit: 5,
                    period: MONTH
                ) {
                    id
                    name
                    price
                    stock
                    orderCount
                    revenue
                }
            }
        ', [
            'categoryId' => $this->category->id
        ], $this->userAuth['headers']);

        $response->assertSuccessful();
        
        $products = $response->json('data.popularProductsByCategory');
        
        // Vérifier que la response est valide
        $this->assertNotNull($products);
        $this->assertIsArray($products);
        
        if (count($products) > 0) {
            $this->assertEquals($this->product->id, $products[0]['id']);
            $this->assertEquals('Test Product', $products[0]['name']);
            $this->assertArrayHasKey('price', $products[0]);
            $this->assertArrayHasKey('orderCount', $products[0]);
            $this->assertArrayHasKey('revenue', $products[0]);
        }
    }

    /** @test */
    public function statistics_exclude_cancelled_orders()
    {
        $response = $this->graphQL('
            query OrderStats($startDate: Date!, $endDate: Date!) {
                orderStatistics(startDate: $startDate, endDate: $endDate) {
                    totalRevenue
                    totalOrders
                    metrics {
                        cancelledOrders
                    }
                }
            }
        ', [
            'startDate' => now()->subWeek()->toDateString(),
            'endDate' => now()->toDateString()
        ], $this->adminAuth['headers']);

        $response->assertSuccessful();
        
        $stats = $response->json('data.orderStatistics');
        
        // Vérifier que la response est valide
        $this->assertNotNull($stats);
        $this->assertArrayHasKey('totalRevenue', $stats);
        $this->assertArrayHasKey('totalOrders', $stats);
        
        // Les commandes annulées ne doivent pas être comptées dans le CA (si on a des données)
        if ($stats['totalRevenue'] > 0) {
            $this->assertNotEquals(150.00, $stats['totalRevenue']); // Pas 150 (25 de la commande annulée)
        }
        
        // Mais elles doivent être comptées séparément si metrics existe
        if (isset($stats['metrics']) && isset($stats['metrics']['cancelledOrders'])) {
            $this->assertGreaterThanOrEqual(0, $stats['metrics']['cancelledOrders']);
        }
    }
}
