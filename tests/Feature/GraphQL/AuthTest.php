<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user login.
     */
    public function test_user_can_login_with_valid_credentials()
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $query = '
            mutation Login($email: String!, $password: String!) {
                login(email: $email, password: $password) {
                    access_token
                    token_type
                    expires_in
                    user {
                        id
                        name
                        email
                    }
                }
            }
        ';

        $variables = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        // Act
        $response = $this->graphQL($query, $variables);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'login' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ]
                ]
            ]
        ]);

        $responseData = $response->json('data.login');
        $this->assertEquals('bearer', $responseData['token_type']);
        $this->assertEquals($user->email, $responseData['user']['email']);
        $this->assertNotEmpty($responseData['access_token']);
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        // Arrange
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $query = '
            mutation Login($email: String!, $password: String!) {
                login(email: $email, password: $password) {
                    access_token
                }
            }
        ';

        $variables = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];

        // Act
        $response = $this->graphQL($query, $variables);

        // Assert
        $this->assertGraphQLError($response, 'Invalid credentials');
    }

    /**
     * Test user logout.
     */
    public function test_authenticated_user_can_logout()
    {
        // Arrange
        $query = '
            mutation {
                logout {
                    message
                }
            }
        ';

        // Act
        $response = $this->authenticatedGraphQL($query);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonFragment([
            'data' => [
                'logout' => [
                    'message' => 'Successfully logged out'
                ]
            ]
        ]);
    }

    /**
     * Test getting current user.
     */
    public function test_authenticated_user_can_get_profile()
    {
        // Arrange
        $query = '
            query {
                me {
                    id
                    name
                    email
                    is_admin
                    is_active
                }
            }
        ';

        // Act
        $response = $this->authenticatedGraphQL($query, [], [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'me' => [
                    'id',
                    'name',
                    'email',
                    'is_admin',
                    'is_active'
                ]
            ]
        ]);

        $response->assertJsonFragment([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    /**
     * Test unauthenticated user cannot access protected routes.
     */
    public function test_unauthenticated_user_cannot_access_me_query()
    {
        // Arrange
        $query = '
            query {
                me {
                    id
                    name
                    email
                }
            }
        ';

        // Act
        $response = $this->graphQL($query);

        // Assert
        $this->assertGraphQLError($response, 'Unauthenticated.');
    }
}
