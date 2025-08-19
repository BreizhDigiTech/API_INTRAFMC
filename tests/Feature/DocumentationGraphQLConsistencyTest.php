<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ProductCBD;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Foundation\Testing\WithFaker;

class DocumentationGraphQLConsistencyTest extends TestCase
{
    use WithFaker;

    /**
     * Seed minimal data: one category, one product, one order with pivot.
     */
  protected function seedMinimalData($userId = null): array
    {
        $category = Category::factory()->create();
        $product = ProductCBD::factory()->create([
            'category_id' => $category->id,
            'price' => 19.99,
            'stock' => 5,
            'images' => ['prod.jpg'],
            'image_metadata' => ['alt' => 'p1'],
            'analysis_file' => null,
        ]);

    $order = Order::factory()->create([
      'user_id' => $userId ?? fn() => \App\Models\User::factory()->create()->id,
            'total' => 19.99,
            'status' => 'pending',
        ]);
        $order->products()->attach($product->id, [
            'quantity' => 1,
            'unit_price' => 19.99,
        ]);

        return compact('category', 'product', 'order');
    }

    public function test_productsCBD_query_matches_doc()
    {
  // Authentifier un utilisateur et semer des données liées à lui
  $auth = $this->createAuthenticatedUser();
  $this->seedMinimalData($auth['user']->id);
        $query = <<<'GQL'
        query GetProductsCBD($first: Int, $page: Int) {
          productsCBD(first: $first, page: $page) {
            paginatorInfo {
              currentPage
              hasMorePages
              total
              perPage
              lastPage
            }
            data {
              id
              name
              price
              stock
              images
            }
          }
        }
        GQL;

  $resp = $this->graphQL($query, ['first' => 10, 'page' => 1], $auth['headers']);
        $this->assertGraphQLSuccess($resp);
        $resp->assertJsonStructure([
            'data' => [
                'productsCBD' => [
                    'paginatorInfo' => ['currentPage','hasMorePages','total','perPage','lastPage'],
                    'data' => [['id','name','price','stock','images']]
                ]
            ]
        ]);
    }

    public function test_myOrders_and_orderDetails_match_doc()
    {
  // Authentifier un utilisateur et créer une commande qui lui appartient
  $auth = $this->createAuthenticatedUser();
  $this->seedMinimalData($auth['user']->id);
  // myOrders
        $myOrders = <<<'GQL'
        query MyOrders($first: Int = 10, $page: Int = 1) {
          myOrders(first: $first, page: $page) {
            paginatorInfo { currentPage lastPage hasMorePages total count }
            data {
              id
              user_id
              total
              status
              created_at
              updated_at
              total_items
              product_count
              formatted_status
              user { name email }
            }
          }
        }
        GQL;
  $resp1 = $this->graphQL($myOrders, ['first' => 10, 'page' => 1], $auth['headers']);
        $this->assertGraphQLSuccess($resp1);

        // orderDetails
  $orderId = Order::where('user_id', $auth['user']->id)->first()->id;
        $details = <<<'GQL'
        query GetOrderDetails($id: ID!) {
          orderDetails(id: $id) {
            id
            user_id
            total
            status
            created_at
            updated_at
            formatted_status
            user { id name email }
            products {
              id
              name
              description
              price
              images
              image_urls
              image_metadata
              analysis_file
              analysis_file_url
              analysis_file_original_name
              analysis_file_size
              analysis_file_mime_type
              stock
              categories { id name }
              pivot { quantity unit_price created_at updated_at }
            }
            orderProducts {
              id
              quantity
              unit_price
              created_at
              updated_at
              product { id name description price images categories { id name } }
            }
            total_items
            product_count
          }
        }
        GQL;
  $resp2 = $this->graphQL($details, ['id' => (string)$orderId], $auth['headers']);
  $this->assertGraphQLSuccess($resp2);
  // Diagnostic: imprimer la réponse pour inspection lors d'un échec
  fwrite(STDERR, "\norderDetails response: " . json_encode($resp2->json(), JSON_PRETTY_PRINT) . "\n");
        $resp2->assertJsonStructure([
            'data' => [
                'orderDetails' => [
                    'products' => [[
                        'pivot' => ['quantity','unit_price','created_at','updated_at']
                    ]],
                    'orderProducts' => [[
                        'product' => ['id','name','description','price','images']
                    ]]
                ]
            ]
        ]);
    }
}
