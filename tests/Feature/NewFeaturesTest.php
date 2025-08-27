<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\ProductCBD;
use App\Models\Category;

class NewFeaturesTest extends TestCase
{
    /** @test */
    public function can_get_user_order_statistics()
    {
        $auth = $this->createAuthenticatedUser();
        
        $response = $this->graphQL('
            query UserStats($userId: ID) {
                userOrderStatistics(userId: $userId) {
                    userId
                    userName
                    totalOrders
                    totalAmount
                    customerSegment
                    favoriteProducts {
                        productName
                        totalQuantity
                    }
                    behaviorAnalysis {
                        loyaltyScore
                        spendingPattern
                    }
                }
            }
        ', [
            'userId' => $auth['user']->id
        ], $auth['headers']);

        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('userOrderStatistics', $response->json('data'));
        
        $stats = $response->json('data.userOrderStatistics');
        $this->assertEquals($auth['user']->id, $stats['userId']);
        $this->assertEquals($auth['user']->name, $stats['userName']);
    }
    
    /** @test */
    public function can_get_cart_suggestions()
    {
        $auth = $this->createAuthenticatedUser();
        
        $response = $this->graphQL('
            query CartSuggestions {
                cartSuggestions {
                    productId
                    productName
                    price
                    suggestionType
                    reason
                    score
                }
            }
        ', [], $auth['headers']);

        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('cartSuggestions', $response->json('data'));
    }
    
    /** @test */
    public function can_get_saved_carts()
    {
        $auth = $this->createAuthenticatedUser();
        
        $response = $this->graphQL('
            query SavedCarts {
                savedCarts {
                    savedCartId
                    name
                    createdAt
                    itemCount
                    totalValue
                }
            }
        ', [], $auth['headers']);

        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('savedCarts', $response->json('data'));
    }
    
    /** @test */
    public function can_get_user_activity_timeline()
    {
        $auth = $this->createAuthenticatedUser();
        
        $response = $this->graphQL('
            query UserTimeline($userId: ID) {
                userActivityTimeline(userId: $userId) {
                    userId
                    userName
                    periods {
                        period
                        orders
                        revenue
                        activityScore
                    }
                    insights {
                        trend
                        averageOrdersPerPeriod
                        averageRevenuePerPeriod
                    }
                    summary {
                        totalOrders
                        totalRevenue
                        engagementLevel
                    }
                }
            }
        ', [
            'userId' => $auth['user']->id
        ], $auth['headers']);

        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('userActivityTimeline', $response->json('data'));
    }
    
    /** @test */
    public function admin_can_get_product_insights()
    {
        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        
        $response = $this->graphQL('
            query ProductInsights {
                productPerformanceInsights {
                    topPerformers {
                        productId
                        productName
                        revenue
                        growthRate
                        trendDirection
                    }
                    globalInsights {
                        totalProductsAnalyzed
                        averageGrowthRate
                        topPerformingCategory
                    }
                    summary {
                        topPerformersCount
                        overallHealthScore
                    }
                }
            }
        ', [], $auth['headers']);

        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('productPerformanceInsights', $response->json('data'));
    }
    
    /** @test */
    public function admin_can_get_category_trends()
    {
        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        
        $response = $this->graphQL('
            query CategoryTrends {
                categoryTrends {
                    categoryId
                    categoryName
                    totalRevenue
                    totalOrders
                    trend
                    growthRate
                    periods {
                        period
                        revenue
                        orders
                    }
                }
            }
        ', [], $auth['headers']);

        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('categoryTrends', $response->json('data'));
    }
    
    /** @test */
    public function can_save_current_cart()
    {
        $auth = $this->createAuthenticatedUser();
        
        $response = $this->graphQL('
            mutation SaveCart($name: String) {
                saveCurrentCart(name: $name) {
                    success
                    message
                    savedCartId
                    name
                }
            }
        ', [
            'name' => 'Test Cart'
        ], $auth['headers']);

        if ($response->json('errors')) {
            // Le panier est vide, c'est normal
            $this->assertStringContains('Aucun article dans le panier', $response->json('errors.0.message'));
        } else {
            $this->assertArrayHasKey('data', $response->json());
            $this->assertArrayHasKey('saveCurrentCart', $response->json('data'));
        }
    }
}
