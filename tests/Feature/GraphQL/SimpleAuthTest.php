<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SimpleAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user authentication with JWT.
     */
    public function test_user_authentication_flow()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Test token generation
        $auth = $this->createAuthenticatedUser([
            'email' => 'auth-test@example.com',
            'password' => bcrypt('testpass')
        ]);

        $this->assertNotEmpty($auth['token']);
        $this->assertNotEmpty($auth['headers']);
        $this->assertArrayHasKey('Authorization', $auth['headers']);
        $this->assertEquals('auth-test@example.com', $auth['user']->email);
    }

    /**
     * Test GraphQL query with authentication.
     */
    public function test_graphql_with_authentication()
    {
        // Simple introspection query that should work
        $query = '
            query {
                __schema {
                    types {
                        name
                    }
                }
            }
        ';

        // Test without authentication
        $response = $this->graphQL($query);
        $this->assertEquals(200, $response->getStatusCode());

        // Test with authentication and debug response
        $authResponse = $this->authenticatedGraphQL($query);
        $this->assertEquals(200, $authResponse->getStatusCode());
        
        // Debug: let's see what we get
        $jsonResponse = $authResponse->json();
        if (!array_key_exists('data', $jsonResponse)) {
            dump('Response content:', $jsonResponse);
            $this->fail('Expected data key not found in response');
        }
        
        $this->assertArrayHasKey('data', $jsonResponse);
    }
}
