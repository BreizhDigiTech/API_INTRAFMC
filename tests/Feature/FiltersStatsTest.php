<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\ProductCBD;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Carbon\Carbon;

class FiltersStatsTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestData();
    }

    private function createTestData()
    {
        // Créer des utilisateurs avec différents statuts
        User::factory()->create(['name' => 'Admin User', 'is_admin' => true, 'is_active' => true]);
        User::factory()->create(['name' => 'Active User', 'is_admin' => false, 'is_active' => true]);
        User::factory()->create(['name' => 'Inactive User', 'is_admin' => false, 'is_active' => false]);
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@test.com', 'is_admin' => false, 'is_active' => true]);
        
        // Créer des catégories
        $cat1 = Category::factory()->create(['name' => 'Huiles CBD']);
        $cat2 = Category::factory()->create(['name' => 'Crèmes CBD']);
        
        // Créer des produits avec stocks différents
        $product1 = ProductCBD::factory()->create(['name' => 'Huile CBD 10%', 'price' => 25.99, 'stock' => 0]); // En rupture
        $product2 = ProductCBD::factory()->create(['name' => 'Huile CBD 20%', 'price' => 45.99, 'stock' => 5]); // Stock faible
        $product3 = ProductCBD::factory()->create(['name' => 'Crème CBD', 'price' => 19.99, 'stock' => 50]); // Stock normal
        
        // Associer produits aux catégories
        $product1->categories()->attach($cat1->id);
        $product2->categories()->attach($cat1->id);
        $product3->categories()->attach($cat2->id);
        
        // Créer des commandes avec différents statuts et dates
        Order::factory()->create(['status' => 'pending', 'total' => 25.99, 'created_at' => Carbon::now()]);
        Order::factory()->create(['status' => 'validated', 'total' => 45.99, 'created_at' => Carbon::now()->subDays(5)]);
        Order::factory()->create(['status' => 'cancelled', 'total' => 19.99, 'created_at' => Carbon::now()->subMonth()]);
        Order::factory()->create(['status' => 'delivered', 'total' => 65.98, 'created_at' => Carbon::now()->subDays(2)]);
    }

    public function test_users_search_filter_by_name()
    {
        $response = $this->graphQL('
            query {
                usersSearch(search: "john", first: 10) {
                    data {
                        id
                        name
                        email
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.usersSearch');
        
        // Vérifier qu'on trouve John Doe
        $this->assertGreaterThan(0, $data['paginatorInfo']['total']);
        $this->assertStringContainsString('john', strtolower($data['data'][0]['name']) . strtolower($data['data'][0]['email']));
    }

    public function test_users_search_filter_by_role()
    {
        $response = $this->graphQL('
            query {
                usersSearch(role: "admin", first: 10) {
                    data {
                        id
                        name
                        is_admin
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.usersSearch');
        
        // Vérifier qu'on ne trouve que les admins
        $this->assertEquals(1, $data['paginatorInfo']['total']);
        $this->assertTrue($data['data'][0]['is_admin']);
    }

    public function test_users_search_filter_by_status()
    {
        $response = $this->graphQL('
            query {
                usersSearch(status: "inactive", first: 10) {
                    data {
                        id
                        name
                        is_active
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.usersSearch');
        
        // Vérifier qu'on ne trouve que les utilisateurs inactifs
        $this->assertEquals(1, $data['paginatorInfo']['total']);
        $this->assertFalse($data['data'][0]['is_active']);
    }

    public function test_products_search_filter_by_name()
    {
        $response = $this->graphQL('
            query {
                productsSearch(search: "huile", first: 10) {
                    data {
                        id
                        name
                        price
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.productsSearch');
        
        // Vérifier qu'on trouve les huiles CBD
        $this->assertGreaterThan(0, $data['paginatorInfo']['total']);
        $this->assertStringContainsString('Huile', $data['data'][0]['name']);
    }

    public function test_products_search_filter_by_price_range()
    {
        $response = $this->graphQL('
            query {
                productsSearch(minPrice: 20.0, maxPrice: 30.0, first: 10) {
                    data {
                        id
                        name
                        price
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.productsSearch');
        
        // Vérifier que tous les produits sont dans la fourchette de prix
        foreach ($data['data'] as $product) {
            $this->assertGreaterThanOrEqual(20.0, $product['price']);
            $this->assertLessThanOrEqual(30.0, $product['price']);
        }
    }

    public function test_products_search_filter_by_stock()
    {
        $response = $this->graphQL('
            query {
                productsSearch(inStock: false, first: 10) {
                    data {
                        id
                        name
                        stock
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.productsSearch');
        
        // Vérifier qu'on ne trouve que les produits en rupture
        foreach ($data['data'] as $product) {
            $this->assertEquals(0, $product['stock']);
        }
    }

    public function test_products_search_filter_by_category()
    {
        $category = Category::where('name', 'Huiles CBD')->first();
        
        $response = $this->graphQL('
            query($categoryId: ID!) {
                productsSearch(category: $categoryId, first: 10) {
                    data {
                        id
                        name
                        categories {
                            id
                            name
                        }
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ', ['categoryId' => $category->id]);

        $response->assertStatus(200);
        $data = $response->json('data.productsSearch');
        
        // Vérifier que tous les produits appartiennent à la catégorie Huiles CBD
        foreach ($data['data'] as $product) {
            $categoryFound = false;
            foreach ($product['categories'] as $cat) {
                if ($cat['name'] === 'Huiles CBD') {
                    $categoryFound = true;
                    break;
                }
            }
            $this->assertTrue($categoryFound, "Product should belong to 'Huiles CBD' category");
        }
    }

    public function test_orders_summary_calculations()
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
        
        // Vérifier les calculs
        $this->assertEquals(4, $data['totalOrders']);
        $this->assertEquals(1, $data['pendingOrders']);
        $this->assertEquals(2, $data['validatedOrders']); // validated + delivered
        $this->assertEquals(1, $data['cancelledOrders']);
        
        // Le chiffre d'affaires doit exclure les commandes annulées
        $expectedRevenue = 25.99 + 45.99 + 65.98; // pending + validated + delivered
        $this->assertEquals($expectedRevenue, $data['totalRevenue']);
    }

    public function test_ecommerce_summary_stock_calculations()
    {
        $response = $this->graphQL('
            query {
                ecommerceSummary {
                    totalProducts
                    totalCategories
                    lowStockProducts
                    outOfStockProducts
                    averagePrice
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.ecommerceSummary');
        
        // Vérifier les calculs de stock
        $this->assertEquals(3, $data['totalProducts']);
        $this->assertEquals(2, $data['totalCategories']);
        $this->assertEquals(1, $data['lowStockProducts']); // stock <= 10 mais > 0
        $this->assertEquals(1, $data['outOfStockProducts']); // stock = 0
        
        // Prix moyen: (25.99 + 45.99 + 19.99) / 3 = 30.66
        $expectedAvgPrice = round((25.99 + 45.99 + 19.99) / 3, 2);
        $this->assertEquals($expectedAvgPrice, $data['averagePrice']);
    }

    public function test_dashboard_stats_growth_calculations()
    {
        $response = $this->graphQL('
            query {
                dashboardStatsOptimized {
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
                }
            }
        ');

        $response->assertStatus(200);
        $data = $response->json('data.dashboardStatsOptimized');
        
        // Vérifier les statistiques de commandes
        $this->assertEquals(4, $data['orders']['total']);
        $this->assertEquals(3, $data['orders']['thisMonth']); // Commandes ce mois
        $this->assertEquals(1, $data['orders']['lastMonth']); // Commande le mois dernier
        
        // Growth = ((3-1)/1) * 100 = 200%
        $this->assertEquals(200.0, $data['orders']['growth']);
        
        // Vérifier les revenus
        $thisMonthRevenue = 25.99 + 45.99 + 65.98; // Revenue ce mois
        $this->assertEquals($thisMonthRevenue, $data['revenue']['thisMonth']);
    }
}
