<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can update profile with all available fields.
     */
    public function test_user_can_update_profile_with_all_fields()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'phone' => null,
            'address' => null,
            'birth_date' => null,
            'avatar' => null
        ]);
        $user = $auth['user'];

        $mutation = '
            mutation UpdateProfile(
                $id: ID!,
                $name: String,
                $email: String,
                $phone: String,
                $address: String,
                $birth_date: Date,
                $avatar: String
            ) {
                updateProfile(
                    id: $id,
                    name: $name,
                    email: $email,
                    phone: $phone,
                    address: $address,
                    birth_date: $birth_date,
                    avatar: $avatar
                ) {
                    id
                    name
                    email
                    phone
                    address
                    birth_date
                    avatar
                    updated_at
                }
            }
        ';

        $variables = [
            'id' => $user->id,
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@email.com',
            'phone' => '+33 6 12 34 56 78',
            'address' => '123 Rue de la Paix, 75001 Paris',
            'birth_date' => '1990-05-15',
            'avatar' => 'avatars/jean-dupont.jpg'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables, $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.updateProfile.name', 'Jean Dupont');
        $response->assertJsonPath('data.updateProfile.email', 'jean.dupont@email.com');
        $response->assertJsonPath('data.updateProfile.phone', '+33 6 12 34 56 78');
        $response->assertJsonPath('data.updateProfile.address', '123 Rue de la Paix, 75001 Paris');
        $response->assertJsonPath('data.updateProfile.birth_date', '1990-05-15');
        $response->assertJsonPath('data.updateProfile.avatar', 'http://localhost/storage/avatars/jean-dupont.jpg');

        // Check database - stocker le chemin relatif dans la base
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@email.com',
            'phone' => '+33 6 12 34 56 78',
            'address' => '123 Rue de la Paix, 75001 Paris'
        ]);
        
        // Vérifier la date séparément
        $updatedUser = User::find($user->id);
        $this->assertEquals('1990-05-15', $updatedUser->birth_date->format('Y-m-d'));
        $this->assertEquals('avatars/jean-dupont.jpg', $updatedUser->getAttributes()['avatar']);
    }

    /**
     * Test user can update profile with partial fields.
     */
    public function test_user_can_update_profile_with_partial_fields()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'phone' => '+33 6 00 00 00 00',
            'address' => 'Original Address',
            'birth_date' => '1985-01-01',
            'avatar' => 'avatars/original.jpg'
        ]);
        $user = $auth['user'];

        $mutation = '
            mutation UpdateProfile($id: ID!, $name: String, $phone: String) {
                updateProfile(id: $id, name: $name, phone: $phone) {
                    id
                    name
                    email
                    phone
                    address
                    birth_date
                    avatar
                }
            }
        ';

        $variables = [
            'id' => $user->id,
            'name' => 'Updated Name',
            'phone' => '+33 6 11 11 11 11'
        ];

        // Act
        $response = $this->graphQL($mutation, $variables, $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.updateProfile.name', 'Updated Name');
        $response->assertJsonPath('data.updateProfile.email', 'original@example.com'); // Unchanged
        $response->assertJsonPath('data.updateProfile.phone', '+33 6 11 11 11 11');
        $response->assertJsonPath('data.updateProfile.address', 'Original Address'); // Unchanged
        $response->assertJsonPath('data.updateProfile.birth_date', '1985-01-01'); // Unchanged
        $response->assertJsonPath('data.updateProfile.avatar', 'http://localhost/storage/avatars/original.jpg'); // Unchanged

        // Check database - stocker le chemin relatif dans la base
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'original@example.com',
            'phone' => '+33 6 11 11 11 11',
            'address' => 'Original Address'
        ]);
        
        // Vérifier la date et l'avatar séparément
        $updatedUser = User::find($user->id);
        $this->assertEquals('1985-01-01', $updatedUser->birth_date->format('Y-m-d'));
        $this->assertEquals('avatars/original.jpg', $updatedUser->getAttributes()['avatar']);
    }

    /**
     * Test user cannot update profile with duplicate email.
     */
    public function test_user_cannot_update_profile_with_duplicate_email()
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);
        
        $auth = $this->createAuthenticatedUser([
            'email' => 'user@example.com'
        ]);

        $mutation = '
            mutation UpdateProfile($id: ID!, $email: String) {
                updateProfile(id: $id, email: $email) {
                    id
                    email
                }
            }
        ';

        $variables = [
            'id' => $auth['user']->id,
            'email' => 'existing@example.com' // Email déjà utilisé
        ];

        // Act
        $response = $this->graphQL($mutation, $variables, $auth['headers']);

        // Assert
        $this->assertGraphQLError($response, 'Email déjà utilisé');
    }

    /**
     * Test user can update profile with same email (no change).
     */
    public function test_user_can_update_profile_with_same_email()
    {
        // Arrange
        $auth = $this->createAuthenticatedUser([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $mutation = '
            mutation UpdateProfile($id: ID!, $name: String, $email: String) {
                updateProfile(id: $id, name: $name, email: $email) {
                    id
                    name
                    email
                }
            }
        ';

        $variables = [
            'id' => $auth['user']->id,
            'name' => 'Updated Name',
            'email' => 'test@example.com' // Même email
        ];

        // Act
        $response = $this->graphQL($mutation, $variables, $auth['headers']);

        // Assert
        $this->assertGraphQLSuccess($response);
        $response->assertJsonPath('data.updateProfile.name', 'Updated Name');
        $response->assertJsonPath('data.updateProfile.email', 'test@example.com');
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
            mutation UpdateProfile($id: ID!, $name: String) {
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
}
