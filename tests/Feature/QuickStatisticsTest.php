<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class QuickStatisticsTest extends TestCase
{
    /** @test */
    public function test_order_statistics_basic()
    {
        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        
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
        ], $auth['headers']);

        if ($response->json('errors')) {
            dd('GraphQL Errors:', $response->json('errors'));
        }
        
        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('orderStatistics', $response->json('data'));
        
        $stats = $response->json('data.orderStatistics');
        $this->assertArrayHasKey('totalRevenue', $stats);
        $this->assertArrayHasKey('totalOrders', $stats);
    }
}
