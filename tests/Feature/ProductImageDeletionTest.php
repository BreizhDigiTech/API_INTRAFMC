<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ProductCBD;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductImageDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ProductCBD $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['is_admin' => true]);
        $this->product = ProductCBD::factory()->create([
            'name' => 'Test Product',
            'price' => 25.99,
            'images' => [
                'product_images/1/image1.jpg',
                'product_images/1/image2.png',
                'product_images/1/image3.gif'
            ]
        ]);
        
        // Créer de faux fichiers dans le storage
        Storage::fake('public_web');
        foreach ($this->product->images as $path) {
            Storage::disk('public_web')->put($path, 'fake image content');
        }
    }

    public function test_can_remove_specific_images()
    {
        $this->actingAs($this->user, 'api');

        $mutation = '
        mutation RemoveProductImages($productId: ID!, $imagePaths: [String!]!) {
            removeProductImages(product_id: $productId, image_paths: $imagePaths) {
                id
                images
            }
        }';

        $response = $this->graphQL($mutation, [
            'productId' => $this->product->id,
            'imagePaths' => [
                'product_images/1/image1.jpg',
                'product_images/1/image3.gif'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'removeProductImages' => [
                    'id' => (string) $this->product->id,
                    'images' => ['product_images/1/image2.png']
                ]
            ]
        ]);

        // Vérifier que les fichiers ont été supprimés du storage
        Storage::disk('public_web')->assertMissing('product_images/1/image1.jpg');
        Storage::disk('public_web')->assertMissing('product_images/1/image3.gif');
        Storage::disk('public_web')->assertExists('product_images/1/image2.png');
    }

    public function test_can_clear_all_images()
    {
        $this->actingAs($this->user, 'api');

        $mutation = '
        mutation ClearProductImages($productId: ID!) {
            clearProductImages(product_id: $productId) {
                id
                images
            }
        }';

        $response = $this->graphQL($mutation, [
            'productId' => $this->product->id
        ]);

        $response->assertJson([
            'data' => [
                'clearProductImages' => [
                    'id' => (string) $this->product->id,
                    'images' => []
                ]
            ]
        ]);

        // Vérifier que tous les fichiers ont été supprimés
        foreach ($this->product->images as $path) {
            Storage::disk('public_web')->assertMissing($path);
        }
    }

    public function test_remove_images_requires_authentication()
    {
        $mutation = '
        mutation RemoveProductImages($productId: ID!, $imagePaths: [String!]!) {
            removeProductImages(product_id: $productId, image_paths: $imagePaths) {
                id
            }
        }';

        $response = $this->graphQL($mutation, [
            'productId' => $this->product->id,
            'imagePaths' => ['product_images/1/image1.jpg']
        ]);

        $this->assertStringContainsString('Unauthenticated', $response->json('errors.0.message'));
    }

    public function test_remove_images_requires_product_update_permission()
    {
        // Créer un utilisateur non-admin
        $regularUser = User::factory()->create(['is_admin' => false]);
        $this->actingAs($regularUser, 'api');

        $mutation = '
        mutation RemoveProductImages($productId: ID!, $imagePaths: [String!]!) {
            removeProductImages(product_id: $productId, image_paths: $imagePaths) {
                id
            }
        }';

        $response = $this->graphQL($mutation, [
            'productId' => $this->product->id,
            'imagePaths' => ['product_images/1/image1.jpg']
        ]);

        $this->assertStringContainsString('unauthorized', $response->json('errors.0.message'));
    }

    public function test_remove_nonexistent_images_is_safe()
    {
        $this->actingAs($this->user, 'api');

        $mutation = '
        mutation RemoveProductImages($productId: ID!, $imagePaths: [String!]!) {
            removeProductImages(product_id: $productId, image_paths: $imagePaths) {
                id
                images
            }
        }';

        // Essayer de supprimer des images qui n'existent pas
        $response = $this->graphQL($mutation, [
            'productId' => $this->product->id,
            'imagePaths' => [
                'product_images/1/nonexistent.jpg',
                'product_images/1/image1.jpg' // Cette image existe
            ]
        ]);

        $response->assertJson([
            'data' => [
                'removeProductImages' => [
                    'id' => (string) $this->product->id,
                    'images' => [
                        'product_images/1/image2.png',
                        'product_images/1/image3.gif'
                    ]
                ]
            ]
        ]);
    }

    public function test_remove_empty_image_paths_returns_product_unchanged()
    {
        $this->actingAs($this->user, 'api');

        $mutation = '
        mutation RemoveProductImages($productId: ID!, $imagePaths: [String!]!) {
            removeProductImages(product_id: $productId, image_paths: $imagePaths) {
                id
                images
            }
        }';

        $response = $this->graphQL($mutation, [
            'productId' => $this->product->id,
            'imagePaths' => []
        ]);

        $response->assertJson([
            'data' => [
                'removeProductImages' => [
                    'id' => (string) $this->product->id,
                    'images' => $this->product->images
                ]
            ]
        ]);
    }
}
