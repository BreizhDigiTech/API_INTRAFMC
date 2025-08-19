<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ProductCBD;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class ProductCBDUploadTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'is_admin' => true
        ]);
        
        // Create a test category
        Category::factory()->create([
            'id' => 1,
            'name' => 'Test Category'
        ]);
        
        // Mock storage
    Storage::fake('product_images');
    Storage::fake('analysis');
    }

    public function test_create_product_with_upload()
    {
        $this->actingAs($this->user, 'api');

        // Create a fake image file
        $image = UploadedFile::fake()->image('product.jpg', 800, 600);

        $operations = [
            'query' => '
                    mutation CreateProductCBD($input: CreateProductCBDInput!) {
                        createProductCBD(input: $input) {
                            id
                            name
                            price
                            images
                        }
                    }
                ',
            'variables' => [
                'input' => [
                    'name' => 'Test Product with Upload',
                    'description' => 'A test product',
                    'price' => 29.99,
                    'stock' => 10,
                    'category_ids' => [1],
                    'images' => [null]
                ]
            ]
        ];
        $map = [ '0' => ['variables.input.images.0'] ];
        $files = [ '0' => $image ];

        $response = $this->multipartGraphQL(
            $operations,
            $map,
            $files
        );

        $response->assertJson([
            'data' => [
                'createProductCBD' => [
                    'name' => 'Test Product with Upload',
                    'price' => 29.99
                ]
            ]
        ]);

        // Verify file was uploaded
        $product = ProductCBD::where('name', 'Test Product with Upload')->first();
        $this->assertNotNull($product);
        $this->assertNotEmpty($product->images);
        
        // Check that the image file exists in storage
        if (!empty($product->images)) {
            Storage::disk('product_images')->assertExists($product->images[0]);
        }
    }

    public function test_upload_scalar_type_is_recognized()
    {
        $this->actingAs($this->user, 'api');

        // Test that the Upload scalar is properly recognized
        $response = $this->graphQL('
            query {
                __schema {
                    types {
                        name
                        kind
                    }
                }
            }
        ');

        $types = collect($response->json('data.__schema.types'));
        $uploadType = $types->firstWhere('name', 'Upload');
        
        $this->assertNotNull($uploadType, 'Upload scalar type should be available in GraphQL schema');
        $this->assertEquals('SCALAR', $uploadType['kind']);
    }

    public function test_file_upload_response_type_exists()
    {
        $this->actingAs($this->user, 'api');

        $response = $this->graphQL('
            query {
                __type(name: "FileUploadResponse") {
                    name
                    fields {
                        name
                        type {
                            name
                        }
                    }
                }
            }
        ');

        $type = $response->json('data.__type');
        $this->assertNotNull($type);
        $this->assertEquals('FileUploadResponse', $type['name']);
        
        $fieldNames = collect($type['fields'])->pluck('name');
        $this->assertContains('success', $fieldNames);
        $this->assertContains('message', $fieldNames);
        $this->assertContains('url', $fieldNames);
        $this->assertContains('path', $fieldNames);
    }
}
