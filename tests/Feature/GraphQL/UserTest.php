<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can get users list.
     */
    public function test_admin_can_get_users_list()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        $admin = $auth['user'];
        
        // Create some users
        $user1 = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $user2 = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $query = '
            query {
                users {
                    data {
                        id
                        name
                        email
                        is_admin
                        is_active
                    }
                    pagination {
                        total
                        current_page
                    }
                }
            }
        ';

        // Act
        $response = $this->graphQL($query, [], $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'users' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'is_admin',
                            'is_active'
                        ]
                    ],
                    'pagination' => [
                        'total',
                        'current_page'
                    ]
                ]
            ]
        ]);

        $users = $response->json('data.users.data');
        $this->assertCount(3, $users); // admin + 2 created users
    }

    /**
     * Test non-admin cannot get users list.
     */
    public function test_non_admin_cannot_get_users_list()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser(['is_admin' => false]);

        $query = '
            query {
                users {
                    data {
                        id
                        name
                    }
                }
            }
        ';

        // Act
        $response = $this->graphQL($query, [], $auth['headers']);

        // Assert
        $this->assertGraphQLError($response, 'Accès refusé');
    }

    /**
     * Test admin can get specific user.
     */
    public function test_admin_can_get_specific_user()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        $targetUser = User::factory()->create([
            'name' => 'Target User',
            'email' => 'target@example.com'
        ]);

        $query = '
            query($id: ID!) {
                user(id: $id) {
                    id
                    name
                    email
                    is_admin
                    is_active
                }
            }
        ';

        // Act
        $response = $this->graphQL($query, ['id' => $targetUser->id], $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.user.name', 'Target User');
        $response->assertJsonPath('data.user.email', 'target@example.com');
    }

    /**
     * Test admin can update user.
     */
    public function test_admin_can_update_user()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        $targetUser = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'is_active' => true
        ]);

        $mutation = '
            mutation($id: ID!, $name: String, $email: String, $is_active: Boolean) {
                updateUser(id: $id, name: $name, email: $email, is_active: $is_active) {
                    id
                    name
                    email
                    is_active
                }
            }
        ';

        $variables = [
            'id' => $targetUser->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'is_active' => false
        ];

        // Act
        $response = $this->graphQL($mutation, $variables, $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.updateUser.name', 'New Name');
        $response->assertJsonPath('data.updateUser.email', 'new@example.com');
        $response->assertJsonPath('data.updateUser.is_active', false);

        // Check database
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'is_active' => false
        ]);
    }

    /**
     * Test non-admin cannot update other users.
     */
    public function test_non_admin_cannot_update_other_users()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser(['is_admin' => false]);
        $targetUser = User::factory()->create();

        $mutation = '
            mutation($id: ID!, $name: String) {
                updateUser(id: $id, name: $name) {
                    id
                    name
                }
            }
        ';

        $variables = [
            'id' => $targetUser->id,
            'name' => 'New Name'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables, $auth['headers']);

        // Assert
        $this->assertGraphQLError($response, 'Acces refuse');
    }

    /**
     * Test admin can delete user.
     */
    public function test_admin_can_delete_user()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser(['is_admin' => true]);
        $targetUser = User::factory()->create();

        $mutation = '
            mutation($id: ID!) {
                deleteUser(id: $id) {
                    success
                    message
                }
            }
        ';

        // Act
        $response = $this->graphQL($mutation, ['id' => $targetUser->id], $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.deleteUser.success', true);

        // Check database
        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id
        ]);
    }

    /**
     * Test user can update own profile.
     */
    public function test_user_can_update_own_profile()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser([
            'name' => 'Old Name',
            'email' => 'old@example.com'
        ]);
        $user = $auth['user'];

        $mutation = '
            mutation($id: ID!, $name: String, $email: String, $avatar: String) {
                updateProfile(id: $id, name: $name, email: $email, avatar: $avatar) {
                    id
                    name
                    email
                    avatar
                }
            }
        ';

        $variables = [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'avatar' => 'avatars/new-avatar.jpg'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables, $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.updateProfile.name', 'New Name');
        $response->assertJsonPath('data.updateProfile.email', 'new@example.com');

        // Check database
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'avatar' => 'avatars/new-avatar.jpg'
        ]);
    }

    /**
     * Test user cannot update other user's profile.
     */
    public function test_user_cannot_update_other_users_profile()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser();
        $otherUser = User::factory()->create();

        $mutation = '
            mutation($id: ID!, $name: String) {
                updateProfile(id: $id, name: $name) {
                    id
                    name
                }
            }
        ';

        $variables = [
            'id' => $otherUser->id,
            'name' => 'Hacked Name'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables, $auth['headers']);

        // Assert
        $this->assertGraphQLError($response, 'Acces refuse');
    }

    /**
     * Test user can change password.
     */
    public function test_user_can_change_password()
    {
        // Arrange
        $currentPassword = 'OldPassword123!';
        $auth = $this->createAuthenticatedUser([
            'password' => bcrypt($currentPassword)
        ]);

        $mutation = '
            mutation($current_password: String!, $new_password: String!) {
                changePassword(current_password: $current_password, new_password: $new_password) {
                    success
                    message
                }
            }
        ';

        $variables = [
            'current_password' => $currentPassword,
            'new_password' => 'NewPassword123!'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables, $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.changePassword.success', true);
    }

    /**
     * Test user cannot change password with wrong current password.
     */
    public function test_user_cannot_change_password_with_wrong_current_password()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser([
            'password' => bcrypt('RealPassword123!')
        ]);

        $mutation = '
            mutation($current_password: String!, $new_password: String!) {
                changePassword(current_password: $current_password, new_password: $new_password) {
                    success
                    message
                }
            }
        ';

        $variables = [
            'current_password' => 'WrongPassword123!',
            'new_password' => 'NewPassword123!'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables, $auth['headers']);

        // Assert
        $this->assertGraphQLError($response);
    }

    /**
     * Test unauthenticated user cannot access user endpoints.
     */
    public function test_unauthenticated_user_cannot_access_user_endpoints()
    {
        // Arrange
        $query = '
            query {
                users {
                    data {
                        id
                    }
                }
            }
        ';

        // Act
        $response = $this->graphQL($query);

        // Assert
        $this->assertGraphQLError($response, 'Unauthenticated.');
    }
}
