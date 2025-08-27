<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\ProductCBD;
use App\Models\Category;

class SimpleStatisticsTest extends TestCase
{
    /** @test */
    public function can_test_basic_graphql()
    {
        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        
        $response = $this->graphQL('
            query {
                users {
                    data {
                        id
                        name
                        email
                    }
                }
            }
        ', [], $auth['headers']);

        $response->assertSuccessful();
        $this->assertArrayHasKey('data', $response->json());
    }

    /** @test */
    public function can_test_categories_with_counts()
    {
        $auth = $this->createAuthenticatedUser();
        $category = Category::factory()->create(['name' => 'Test Category']);
        
        $response = $this->graphQL('
            query {
                categoriesWithCounts {
                    id
                    name
                    productCount
                }
            }
        ', [], $auth['headers']);

        dd($response->json());
    }
}
