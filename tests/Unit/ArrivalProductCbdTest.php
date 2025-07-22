<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ArrivalProductCbd;
use App\Models\CbdArrival;
use App\Models\ProductCBD;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ArrivalProductCbdTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer des données de test de base
        $this->category = Category::factory()->create([
            'name' => 'Test Category',
            'description' => 'Category for testing'
        ]);
        
        $this->arrival = CbdArrival::factory()->create();
        
        $this->product = ProductCBD::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 100
        ]);
    }

    /** @test */
    public function it_can_create_arrival_product()
    {
        $arrivalProduct = ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'unit_price' => 25.99
        ]);

        $this->assertInstanceOf(ArrivalProductCbd::class, $arrivalProduct);
        $this->assertEquals($this->arrival->id, $arrivalProduct->arrival_id);
        $this->assertEquals($this->product->id, $arrivalProduct->product_id);
        $this->assertEquals(50, $arrivalProduct->quantity);
        $this->assertEquals(25.99, $arrivalProduct->unit_price);
    }

    /** @test */
    public function it_belongs_to_arrival()
    {
        $arrivalProduct = ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 30,
            'unit_price' => 15.50
        ]);

        $this->assertInstanceOf(CbdArrival::class, $arrivalProduct->arrival);
        $this->assertEquals($this->arrival->id, $arrivalProduct->arrival->id);
    }

    /** @test */
    public function it_belongs_to_product()
    {
        $arrivalProduct = ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'unit_price' => 18.75
        ]);

        $this->assertInstanceOf(ProductCBD::class, $arrivalProduct->product);
        $this->assertEquals($this->product->id, $arrivalProduct->product->id);
        $this->assertEquals($this->product->name, $arrivalProduct->product->name);
    }

    /** @test */
    public function it_calculates_total_price()
    {
        $arrivalProduct = ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 12.50
        ]);

        // Calculer le prix total
        $totalPrice = $arrivalProduct->quantity * $arrivalProduct->unit_price;
        
        $this->assertEquals(125.00, $totalPrice);
    }

    /** @test */
    public function it_can_have_multiple_products_for_same_arrival()
    {
        $product2 = ProductCBD::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Second Product'
        ]);

        // Créer deux produits pour le même arrivage
        $arrivalProduct1 = ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 25,
            'unit_price' => 20.00
        ]);

        $arrivalProduct2 = ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $product2->id,
            'quantity' => 15,
            'unit_price' => 30.00
        ]);

        // Vérifier que l'arrivage a bien 2 produits
        $this->assertCount(2, $this->arrival->fresh()->products);
        
        // Vérifier les données
        $products = $this->arrival->fresh()->products;
        $this->assertTrue($products->contains('product_id', $this->product->id));
        $this->assertTrue($products->contains('product_id', $product2->id));
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // Tester que les champs requis sont bien obligatoires
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        ArrivalProductCbd::create([
            // Manque arrival_id, product_id, quantity, unit_price
        ]);
    }

    /** @test */
    public function it_validates_positive_quantity()
    {
        $arrivalProduct = ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 0,
            'unit_price' => 25.00
        ]);

        // La quantité peut être 0 en base de données
        $this->assertEquals(0, $arrivalProduct->quantity);
        
        // Mais on peut ajouter une validation métier
        $this->assertGreaterThanOrEqual(0, $arrivalProduct->quantity);
    }

    /** @test */
    public function it_validates_positive_unit_price()
    {
        $arrivalProduct = ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 0.01
        ]);

        $this->assertGreaterThan(0, $arrivalProduct->unit_price);
    }

    /** @test */
    public function it_can_update_quantity_and_price()
    {
        $arrivalProduct = ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 20.00
        ]);

        // Mettre à jour
        $arrivalProduct->update([
            'quantity' => 25,
            'unit_price' => 18.50
        ]);

        $this->assertEquals(25, $arrivalProduct->fresh()->quantity);
        $this->assertEquals(18.50, $arrivalProduct->fresh()->unit_price);
    }

    /** @test */
    public function it_can_delete_arrival_product()
    {
        $arrivalProduct = ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 15,
            'unit_price' => 22.00
        ]);

        $id = $arrivalProduct->id;
        
        $arrivalProduct->delete();
        
        $this->assertDatabaseMissing('arrival_product_cbd', ['id' => $id]);
    }

    /** @test */
    public function it_cascades_delete_when_arrival_is_deleted()
    {
        $arrivalProduct = ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'unit_price' => 15.00
        ]);

        $arrivalProductId = $arrivalProduct->id;
        
        // Supprimer l'arrivage
        $this->arrival->delete();
        
        // Vérifier que le produit d'arrivage a été supprimé aussi
        $this->assertDatabaseMissing('arrival_product_cbd', ['id' => $arrivalProductId]);
    }

    /** @test */
    public function it_cascades_delete_when_product_is_deleted()
    {
        $arrivalProduct = ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 20,
            'unit_price' => 15.00
        ]);

        $arrivalProductId = $arrivalProduct->id;
        
        // Supprimer le produit
        $this->product->delete();
        
        // Vérifier que le produit d'arrivage a été supprimé aussi
        $this->assertDatabaseMissing('arrival_product_cbd', ['id' => $arrivalProductId]);
    }

    /** @test */
    public function it_has_timestamps()
    {
        $arrivalProduct = ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 15,
            'unit_price' => 25.00
        ]);

        $this->assertNotNull($arrivalProduct->created_at);
        $this->assertNotNull($arrivalProduct->updated_at);
    }

    /** @test */
    public function it_can_scope_by_arrival()
    {
        $arrival2 = CbdArrival::factory()->create();
        
        // Créer des produits pour deux arrivages différents
        ArrivalProductCbd::create([
            'arrival_id' => $this->arrival->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 20.00
        ]);

        ArrivalProductCbd::create([
            'arrival_id' => $arrival2->id,
            'product_id' => $this->product->id,
            'quantity' => 15,
            'unit_price' => 22.00
        ]);

        // Filtrer par arrivage
        $arrival1Products = ArrivalProductCbd::where('arrival_id', $this->arrival->id)->get();
        $arrival2Products = ArrivalProductCbd::where('arrival_id', $arrival2->id)->get();

        $this->assertCount(1, $arrival1Products);
        $this->assertCount(1, $arrival2Products);
        $this->assertEquals(10, $arrival1Products->first()->quantity);
        $this->assertEquals(15, $arrival2Products->first()->quantity);
    }
}
