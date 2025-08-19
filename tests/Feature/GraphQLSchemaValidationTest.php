<?php

namespace Tests\Feature;

use Tests\TestCase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class GraphQLSchemaValidationTest extends TestCase
{
    use MakesGraphQLRequests;

    public function test_upload_scalar_type_is_recognized()
    {
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

        $response->assertStatus(200);
        
        $types = collect($response->json('data.__schema.types'));
        $uploadType = $types->firstWhere('name', 'Upload');
        
        $this->assertNotNull($uploadType, 'Upload scalar type should be available in GraphQL schema');
        $this->assertEquals('SCALAR', $uploadType['kind']);
    }

    public function test_file_upload_response_type_exists()
    {
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

        $response->assertStatus(200);
        
        $type = $response->json('data.__type');
        $this->assertNotNull($type);
        $this->assertEquals('FileUploadResponse', $type['name']);
        
        $fieldNames = collect($type['fields'])->pluck('name');
        $this->assertContains('success', $fieldNames);
        $this->assertContains('message', $fieldNames);
        $this->assertContains('url', $fieldNames);
        $this->assertContains('path', $fieldNames);
    }

    public function test_product_cbd_create_mutation_has_upload_support()
    {
        $response = $this->graphQL('
            query {
                __type(name: "CreateProductCBDInput") {
                    name
                    inputFields {
                        name
                        type {
                            name
                            ofType {
                                name
                                ofType {
                                    name
                                }
                            }
                        }
                    }
                }
            }
        ');

        $response->assertStatus(200);
        
        $type = $response->json('data.__type');
        $this->assertNotNull($type);
        
        $inputFields = collect($type['inputFields']);
        $imagesField = $inputFields->firstWhere('name', 'images');
        $analysisImageField = $inputFields->firstWhere('name', 'analysis_image');
        
        $this->assertNotNull($imagesField, 'CreateProductCBDInput should have images field');
        $this->assertNotNull($analysisImageField, 'CreateProductCBDInput should have analysis_image field');
        
        // Verify that images field accepts Upload type (it's a list of Upload)
        $this->assertNull($imagesField['type']['name']); // List type has null name
        $this->assertEquals('Upload', $imagesField['type']['ofType']['name']);
        
        // Verify that analysis_image field accepts Upload type
        $this->assertEquals('Upload', $analysisImageField['type']['name']);
    }
}
