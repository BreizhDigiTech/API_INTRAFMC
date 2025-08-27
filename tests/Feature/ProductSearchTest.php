<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ProductCBD;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase, MakesGraphQLRequests;

    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer une catégorie pour les tests
        $this->category = Category::factory()->create(['name' => 'CBD Flowers']);
        
        // Créer des produits de test avec différents noms et prix
        ProductCBD::factory()->create([
            'name' => 'Orange Bud CBD',
            'description' => 'Fleur de CBD orange premium',
            'price' => 25.50,
            'stock' => 10
        ])->categories()->attach($this->category->id);
        
        ProductCBD::factory()->create([
            'name' => 'Lemon Haze',
            'description' => 'Variété citronné excellente qualité',
            'price' => 30.00,
            'stock' => 5
        ])->categories()->attach($this->category->id);
        
        ProductCBD::factory()->create([
            'name' => 'Purple Kush',
            'description' => 'Indica relaxante',
            'price' => 35.00,
            'stock' => 0 // Rupture de stock
        ])->categories()->attach($this->category->id);
        
        ProductCBD::factory()->create([
            'name' => 'Green Orange',
            'description' => 'Mélange orange et menthe',
            'price' => 20.00,
            'stock' => 15
        ])->categories()->attach($this->category->id);
    }

    /** @test */
    public function it_can_search_products_by_name()
    {
        $response = $this->graphQL('
            query SearchProducts($query: String!) {
                searchProducts(query: $query) {
                    id
                    name
                    description
                    price
                    stock
                    categories {
                        id
                        name
                    }
                }
            }
        ', [
            'query' => 'Orange'
        ]);

        $response->assertSuccessful();
        
        $products = $response->json('data.searchProducts');
        $this->assertCount(2, $products);
        
        // Vérifier que "Orange Bud CBD" apparaît en premier (correspondance exacte prioritaire)
        $productNames = collect($products)->pluck('name')->toArray();
        $this->assertContains('Orange Bud CBD', $productNames);
        $this->assertContains('Green Orange', $productNames);
    }

    /** @test */
    public function it_can_search_products_by_description()
    {
        $response = $this->graphQL('
            query SearchProducts($query: String!) {
                searchProducts(query: $query) {
                    id
                    name
                    description
                }
            }
        ', [
            'query' => 'citronné'
        ]);

        $response->assertSuccessful();
        
        $products = $response->json('data.searchProducts');
        $this->assertCount(1, $products);
        $this->assertEquals('Lemon Haze', $products[0]['name']);
    }

    /** @test */
    public function it_can_filter_by_price_range()
    {
        $response = $this->graphQL('
            query SearchProducts($minPrice: Float, $maxPrice: Float) {
                searchProducts(min_price: $minPrice, max_price: $maxPrice) {
                    id
                    name
                    price
                }
            }
        ', [
            'minPrice' => 20.0,
            'maxPrice' => 30.0
        ]);

        $response->assertSuccessful();
        
        $products = $response->json('data.searchProducts');
        
        // Doit retourner Orange Bud CBD (25.50), Lemon Haze (30.00), Green Orange (20.00)
        $this->assertCount(3, $products);
        
        foreach ($products as $product) {
            $this->assertGreaterThanOrEqual(20.0, $product['price']);
            $this->assertLessThanOrEqual(30.0, $product['price']);
        }
    }

    /** @test */
    public function it_can_filter_by_stock_availability()
    {
        $response = $this->graphQL('
            query SearchProducts($inStock: Boolean) {
                searchProducts(in_stock: $inStock) {
                    id
                    name
                    stock
                }
            }
        ', [
            'inStock' => true
        ]);

        $response->assertSuccessful();
        
        $products = $response->json('data.searchProducts');
        
        // Ne doit pas retourner Purple Kush (stock = 0)
        $this->assertCount(3, $products);
        
        foreach ($products as $product) {
            $this->assertGreaterThan(0, $product['stock']);
        }
        
        $productNames = collect($products)->pluck('name')->toArray();
        $this->assertNotContains('Purple Kush', $productNames);
    }

    /** @test */
    public function it_can_filter_by_category()
    {
        // Créer une autre catégorie
        $otherCategory = Category::factory()->create(['name' => 'Oils']);
        $productInOtherCategory = ProductCBD::factory()->create([
            'name' => 'CBD Oil 10%',
            'price' => 45.00,
            'stock' => 8
        ]);
        $productInOtherCategory->categories()->attach($otherCategory->id);

        $response = $this->graphQL('
            query SearchProducts($categoryId: ID) {
                searchProducts(category_id: $categoryId) {
                    id
                    name
                    categories {
                        id
                        name
                    }
                }
            }
        ', [
            'categoryId' => $this->category->id
        ]);

        $response->assertSuccessful();
        
        $products = $response->json('data.searchProducts');
        
        // Doit retourner seulement les produits de la catégorie CBD Flowers
        $this->assertCount(4, $products);
        
        foreach ($products as $product) {
            $categoryNames = collect($product['categories'])->pluck('name')->toArray();
            $this->assertContains('CBD Flowers', $categoryNames);
        }
    }

    /** @test */
    public function it_can_search_products_by_name_quickly()
    {
        $response = $this->graphQL('
            query SearchByName($name: String!, $limit: Int) {
                searchProductsByName(name: $name, limit: $limit) {
                    id
                    name
                }
            }
        ', [
            'name' => 'Lemon',
            'limit' => 5
        ]);

        $response->assertSuccessful();
        
        $products = $response->json('data.searchProductsByName');
        $this->assertCount(1, $products);
        $this->assertEquals('Lemon Haze', $products[0]['name']);
    }

    /** @test */
    public function it_can_get_product_suggestions()
    {
        $response = $this->graphQL('
            query GetSuggestions($query: String!) {
                productSuggestions(query: $query)
            }
        ', [
            'query' => 'Or'
        ]);

        $response->assertSuccessful();
        
        $suggestions = $response->json('data.productSuggestions');
        
        // Doit retourner les noms commençant par "Or"
        $this->assertContains('Orange Bud CBD', $suggestions);
        $this->assertNotContains('Lemon Haze', $suggestions);
    }

    /** @test */
    public function it_handles_empty_search_query()
    {
        $response = $this->graphQL('
            query SearchProducts {
                searchProducts {
                    id
                    name
                }
            }
        ');

        $response->assertSuccessful();
        
        // Doit retourner tous les produits si query vide
        $products = $response->json('data.searchProducts');
        $this->assertCount(4, $products);
    }

    /** @test */
    public function search_results_are_properly_ordered()
    {
        $response = $this->graphQL('
            query SearchProducts($query: String!) {
                searchProducts(query: $query) {
                    id
                    name
                }
            }
        ', [
            'query' => 'Orange'
        ]);

        $response->assertSuccessful();
        
        $products = $response->json('data.searchProducts');
        $productNames = collect($products)->pluck('name')->toArray();
        
        // "Orange Bud CBD" devrait apparaître avant "Green Orange" 
        // car il contient "Orange" au début
        $orangeBudIndex = array_search('Orange Bud CBD', $productNames);
        $greenOrangeIndex = array_search('Green Orange', $productNames);
        
        $this->assertLessThan($greenOrangeIndex, $orangeBudIndex);
    }
}
