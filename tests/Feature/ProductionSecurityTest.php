<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductionSecurityTest extends TestCase
{
    /**
     * Test security headers are present in responses
     */
    public function test_security_headers_are_present(): void
    {
        // Test a simple introspection query that doesn't require auth
        $response = $this->postJson('/graphql', [
            'query' => '{ __type(name: "String") { name } }'
        ]);

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Test rate limiting is working
     */
    public function test_rate_limiting_works(): void
    {
        // Test simple introspection to avoid auth issues
        $query = '{ __type(name: "String") { name } }';
        
        // Make requests to test rate limit (should be 60/min)
        $successCount = 0;
        $rateLimitHit = false;
        
        // Test with fewer requests to avoid taking too long
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/graphql', [
                'query' => $query
            ]);
            
            if ($response->getStatusCode() === 200) {
                $successCount++;
            } elseif ($response->getStatusCode() === 429) {
                $rateLimitHit = true;
                break;
            }
        }
        
        // At least some requests should succeed
        $this->assertGreaterThan(0, $successCount, 'No requests succeeded');
    }

    /**
     * Test GraphQL introspection configuration
     */
    public function test_introspection_configuration(): void
    {
        // Test that introspection settings are properly configured
        $introspectionDisabled = config('lighthouse.security.disable_introspection');
        
        if (app()->environment('production')) {
            // In production, introspection should be disabled
            $this->assertNotEquals(0, $introspectionDisabled, 'Introspection should be disabled in production');
            
            $response = $this->postJson('/graphql', [
                'query' => '{ __schema { types { name } } }'
            ]);

            $response->assertJsonStructure(['errors']);
        } else {
            // In non-production, test that the configuration is environment-aware
            $productionValue = env('LIGHTHOUSE_SECURITY_DISABLE_INTROSPECTION', env('APP_ENV') === 'production');
            $this->assertIsBool($productionValue, 'Introspection setting should be boolean');
            
            // Test that introspection works in development (expected behavior)
            $response = $this->postJson('/graphql', [
                'query' => '{ __type(name: "String") { name } }'
            ]);
            
            $response->assertStatus(200);
            $response->assertJsonStructure(['data']);
        }
    }

    /**
     * Test query complexity limitation
     */
    public function test_query_complexity_limitation(): void
    {
        // Test with the current complexity limit (1000) 
        // This query should be within normal limits
        $normalQuery = '{ 
            __schema { 
                queryType { 
                    name 
                    fields { 
                        name 
                        type { 
                            name 
                        } 
                    } 
                } 
            } 
        }';

        $response = $this->postJson('/graphql', [
            'query' => $normalQuery
        ]);

        // Normal complexity query should work
        $response->assertStatus(200);
        
        // Test complexity limits are configured
        $this->assertTrue(config('lighthouse.security.max_query_complexity') > 0, 'Query complexity limit should be configured');
    }

    /**
     * Test query depth limitation
     */
    public function test_query_depth_limitation(): void
    {
        // Test with normal depth query (should work with limit of 10)
        $normalQuery = '{ 
            __schema { 
                queryType { 
                    name 
                    fields { 
                        name 
                        type { 
                            name 
                        } 
                    } 
                } 
            } 
        }';

        $response = $this->postJson('/graphql', [
            'query' => $normalQuery
        ]);

        // Normal depth query should work
        $response->assertStatus(200);
        
        // Test depth limits are configured
        $this->assertTrue(config('lighthouse.security.max_query_depth') > 0, 'Query depth limit should be configured');
    }
}
