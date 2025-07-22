<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\ProductCBD;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FactoriesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test User factory.
     */
    public function test_user_factory()
    {
        $user = User::factory()->create();
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Test Category factory.
     */
    public function test_category_factory()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category'
        ]);
        
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Test Category',
        ]);
    }

    /**
     * Test ProductCBD factory.
     */
    public function test_product_cbd_factory()
    {
        $product = ProductCBD::factory()->create([
            'name' => 'Test Product',
            'price' => 29.99,
        ]);
        
        $this->assertDatabaseHas('cbd_products', [
            'id' => $product->id,
            'name' => 'Test Product',
            'price' => 29.99,
        ]);
        
        // Verify category relationship (many-to-many)
        $this->assertGreaterThan(0, $product->categories->count());
        $this->assertInstanceOf(Category::class, $product->categories->first());
    }

    /**
     * Test Cart factory.
     */
    public function test_cart_factory()
    {
        $cart = Cart::factory()->create();
        
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
        ]);
        
        // Verify relationships
        $this->assertNotNull($cart->user);
        $this->assertInstanceOf(User::class, $cart->user);
        
        // Check that products were attached
        $this->assertGreaterThan(0, $cart->products->count());
        $this->assertInstanceOf(ProductCBD::class, $cart->products->first());
    }

    /**
     * Test factory relationships.
     */
    public function test_factory_relationships()
    {
        // Create product with specific category
        $category = Category::factory()->create(['name' => 'CBD Oils']);
        
        // Create product without auto-category to control the relationship
        $product = ProductCBD::factory()->create();
        
        // Clear any auto-attached categories and attach our specific one
        $product->categories()->detach();
        $product->categories()->attach($category->id);
        
        $this->assertTrue($product->categories->contains($category));
        $this->assertEquals('CBD Oils', $product->categories->first()->name);
        
        // Create cart with specific user
        $user = User::factory()->create(['name' => 'John Doe']);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        
        // Clear auto-attached products and attach our specific product
        $cart->products()->detach();
        $cart->products()->attach($product->id, ['quantity' => 3]);
        
        $this->assertEquals($user->id, $cart->user_id);
        $this->assertEquals('John Doe', $cart->user->name);
        
        // Test the many-to-many relationship
        $this->assertTrue($cart->products->contains($product));
        $this->assertEquals(3, $cart->products->first()->pivot->quantity);
    }
}
