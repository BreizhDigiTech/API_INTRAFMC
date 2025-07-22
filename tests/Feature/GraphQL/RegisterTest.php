<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user registration.
     */
    public function test_user_can_register_with_valid_data()
    {
        // Arrange
        $mutation = '
            mutation Register(
                $name: String!,
                $email: String!,
                $password: String!,
                $password_confirmation: String!,
                $avatar: String
            ) {
                register(
                    name: $name,
                    email: $email,
                    password: $password,
                    password_confirmation: $password_confirmation,
                    avatar: $avatar
                ) {
                    access_token
                    token_type
                    expires_in
                    user {
                        id
                        name
                        email
                        avatar
                        is_admin
                        is_active
                    }
                }
            }
        ';

        $variables = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'avatar' => 'avatars/john.jpg'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'register' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'avatar',
                        'is_admin',
                        'is_active'
                    ]
                ]
            ]
        ]);

        $responseData = $response->json('data.register');
        $this->assertEquals('bearer', $responseData['token_type']);
        $this->assertEquals('John Doe', $responseData['user']['name']);
        $this->assertEquals('john.doe@example.com', $responseData['user']['email']);
        $this->assertEquals('http://localhost/storage/avatars/john.jpg', $responseData['user']['avatar']);
        $this->assertFalse($responseData['user']['is_admin']);
        $this->assertTrue($responseData['user']['is_active']);
        $this->assertNotEmpty($responseData['access_token']);

        // Check database
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'avatar' => 'avatars/john.jpg',
            'is_admin' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Test registration with default avatar.
     */
    public function test_user_can_register_with_default_avatar()
    {
        // Arrange
        $mutation = '
            mutation Register(
                $name: String!,
                $email: String!,
                $password: String!,
                $password_confirmation: String!
            ) {
                register(
                    name: $name,
                    email: $email,
                    password: $password,
                    password_confirmation: $password_confirmation
                ) {
                    user {
                        avatar
                    }
                }
            }
        ';

        $variables = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.register.user.avatar', 'http://localhost/storage/avatars/avatar-default-symbolic.svg');
    }

    /**
     * Test registration with missing required fields.
     */
    public function test_user_cannot_register_with_missing_required_fields()
    {
        // Arrange - test with too short password
        $mutation = '
            mutation Register(
                $name: String!,
                $email: String!,
                $password: String!,
                $password_confirmation: String!
            ) {
                register(
                    name: $name,
                    email: $email,
                    password: $password,
                    password_confirmation: $password_confirmation
                ) {
                    access_token
                }
            }
        ';

        $variables = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => '123', // Trop court (< 8 caractères)
            'password_confirmation' => '123'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables);

        // Assert
        $this->assertGraphQLError($response, 'Erreur de validation');
    }

    /**
     * Test registration with duplicate email.
     */
    public function test_user_cannot_register_with_duplicate_email()
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);

        $mutation = '
            mutation Register(
                $name: String!,
                $email: String!,
                $password: String!,
                $password_confirmation: String!
            ) {
                register(
                    name: $name,
                    email: $email,
                    password: $password,
                    password_confirmation: $password_confirmation
                ) {
                    access_token
                }
            }
        ';

        $variables = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables);

        // Assert
        $this->assertGraphQLError($response, 'Erreur de validation');
    }

    /**
     * Test registration with weak password.
     */
    public function test_user_cannot_register_with_weak_password()
    {
        // Arrange
        $mutation = '
            mutation Register(
                $name: String!,
                $email: String!,
                $password: String!,
                $password_confirmation: String!
            ) {
                register(
                    name: $name,
                    email: $email,
                    password: $password,
                    password_confirmation: $password_confirmation
                ) {
                    access_token
                }
            }
        ';

        $variables = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables);

        // Assert
        $this->assertGraphQLError($response, 'Erreur de validation');
    }

    /**
     * Test registration with mismatched password confirmation.
     */
    public function test_user_cannot_register_with_mismatched_password_confirmation()
    {
        // Arrange
        $mutation = '
            mutation Register(
                $name: String!,
                $email: String!,
                $password: String!,
                $password_confirmation: String!
            ) {
                register(
                    name: $name,
                    email: $email,
                    password: $password,
                    password_confirmation: $password_confirmation
                ) {
                    access_token
                }
            }
        ';

        $variables = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables);

        // Assert
        $this->assertGraphQLError($response, 'Erreur de validation');
    }

    /**
     * Test registration with invalid email format.
     */
    public function test_user_cannot_register_with_invalid_email()
    {
        // Arrange
        $mutation = '
            mutation Register(
                $name: String!,
                $email: String!,
                $password: String!,
                $password_confirmation: String!
            ) {
                register(
                    name: $name,
                    email: $email,
                    password: $password,
                    password_confirmation: $password_confirmation
                ) {
                    access_token
                }
            }
        ';

        $variables = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables);

        // Assert
        $this->assertGraphQLError($response, 'Erreur de validation');
    }
}
