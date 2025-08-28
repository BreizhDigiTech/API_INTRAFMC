<?php

namespace Tests\Feature;

use Tests\TestCase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class SimpleOptimizationTest extends TestCase
{
    use MakesGraphQLRequests;

    public function test_simple_orders_summary()
    {
        $response = $this->graphQL('
            query {
                ordersSummary {
                    totalOrders
                }
            }
        ');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'ordersSummary' => [
                    'totalOrders'
                ]
            ]
        ]);
    }

    public function test_simple_users_summary()
    {
        $response = $this->graphQL('
            query {
                usersSummary {
                    totalUsers
                }
            }
        ');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'usersSummary' => [
                    'totalUsers'
                ]
            ]
        ]);
    }

    public function test_simple_ecommerce_summary()
    {
        $response = $this->graphQL('
            query {
                ecommerceSummary {
                    totalProducts
                }
            }
        ');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'ecommerceSummary' => [
                    'totalProducts'
                ]
            ]
        ]);
    }

    public function test_debug_categories_response()
    {
        $response = $this->graphQL('
            query {
                categoriesList {
                    id
                    name
                }
            }
        ');

        // Debugger la réponse complète
        echo "\n\nDEBUG CATEGORIES RESPONSE:\n";
        echo json_encode($response->json(), JSON_PRETTY_PRINT);
        echo "\n\n";

        $response->assertStatus(200);
    }
}
