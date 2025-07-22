<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicGraphQLTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test basic GraphQL endpoint.
     */
    public function test_graphql_endpoint_is_accessible()
    {
        $query = '
            query {
                __schema {
                    queryType {
                        name
                    }
                }
            }
        ';

        $response = $this->postJson('/graphql', [
            'query' => $query
        ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('data', $response->json());
    }

    /**
     * Test user factory works.
     */
    public function test_user_factory_creates_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);

        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('Test User', $user->name);
    }

    /**
     * Test JWT token generation.
     */
    public function test_jwt_token_generation()
    {
        $user = User::factory()->create();
        
        try {
            $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
            $this->assertNotEmpty($token);
            $this->assertIsString($token);
            
            // Test that we can decode the token back
            $decodedUser = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->toUser();
            $this->assertEquals($user->id, $decodedUser->id);
            
        } catch (\Exception $e) {
            $this->fail('JWT token generation failed: ' . $e->getMessage());
        }
    }
}
