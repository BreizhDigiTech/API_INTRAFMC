<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;

class DebugTest extends TestCase
{
    public function test_simple_query()
    {
        $query = '
            query {
                __schema {
                    types {
                        name
                    }
                }
            }
        ';

        $response = $this->postJson('/graphql', [
            'query' => $query
        ]);

        dd($response->json());
    }
}
