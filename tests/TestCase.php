<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations for testing
        $this->artisan('migrate');
    }

    /**
     * Execute a GraphQL query.
     */
    protected function graphQL(string $query, array $variables = [], array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/graphql', [
            'query' => $query,
            'variables' => $variables,
        ], $headers);
    }

    /**
     * Create an authenticated user and return the JWT token.
     */
    protected function createAuthenticatedUser(array $attributes = []): array
    {
        $user = User::factory()->create($attributes);
        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token,
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ]
        ];
    }

    /**
     * Execute a GraphQL query with authentication.
     */
    protected function authenticatedGraphQL(string $query, array $variables = [], array $userAttributes = []): \Illuminate\Testing\TestResponse
    {
        $auth = $this->createAuthenticatedUser($userAttributes);
        
        return $this->graphQL($query, $variables, $auth['headers']);
    }

    /**
     * Assert GraphQL response has no errors.
     */
    protected function assertGraphQLSuccess(\Illuminate\Testing\TestResponse $response): void
    {
        $response->assertStatus(200);
        $response->assertJsonMissing(['errors']);
    }

    /**
     * Assert GraphQL response has specific errors.
     */
    protected function assertGraphQLError(\Illuminate\Testing\TestResponse $response, string $expectedMessage = null): void
    {
        $response->assertStatus(200);
        $response->assertJsonStructure(['errors']);
        
        if ($expectedMessage) {
            $response->assertJsonFragment(['message' => $expectedMessage]);
        }
    }

    /**
     * Assert GraphQL validation errors.
     */
    protected function assertGraphQLValidationError(\Illuminate\Testing\TestResponse $response, array $expectedFields = []): void
    {
        $this->assertGraphQLError($response, 'Validation failed for the field');
        
        foreach ($expectedFields as $field) {
            $response->assertJsonFragment(['path' => [$field]]);
        }
    }
}
