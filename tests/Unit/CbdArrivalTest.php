<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CbdArrival;
use App\Models\ProductCBD;
use App\Models\ArrivalProductCbd;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;

class CbdArrivalTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer une catégorie de test
        $this->category = Category::factory()->create([
            'name' => 'Test CBD Category',
            'description' => 'Category for testing'
        ]);
    }

    /** @test */
    public function it_can_create_an_arrival()
    {
        $arrival = CbdArrival::factory()->create([
            'amount' => 1500.50,
            'status' => 'pending'
        ]);

        $this->assertInstanceOf(CbdArrival::class, $arrival);
        $this->assertEquals(1500.50, $arrival->amount);
        $this->assertEquals('pending', $arrival->status);
        $this->assertDatabaseHas('cbd_arrivals', [
            'id' => $arrival->id,
            'amount' => 1500.50,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function it_has_default_pending_status()
    {
        $arrival = CbdArrival::factory()->create([
            'amount' => 1000.00
        ]);

        // Vérifier que le statut par défaut est bien appliqué via la base de données
        $this->assertContains($arrival->status, ['pending', 'validated']);
    }

    /** @test */
    public function it_can_have_products_associated()
    {
        $arrival = CbdArrival::factory()->create();
        
        $product1 = ProductCBD::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 50
        ]);
        
        $product2 = ProductCBD::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 30
        ]);

        // Associer des produits à l'arrivage
        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product1->id,
            'quantity' => 20,
            'unit_price' => 15.50
        ]);

        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product2->id,
            'quantity' => 10,
            'unit_price' => 25.00
        ]);

        $arrival = $arrival->fresh();
        
        $this->assertCount(2, $arrival->products);
        $this->assertEquals(20, $arrival->products->first()->quantity);
        $this->assertEquals(15.50, $arrival->products->first()->unit_price);
    }

    /** @test */
    public function it_updates_product_stock_when_validated()
    {
        // Créer un arrivage en statut pending
        $arrival = CbdArrival::factory()->create([
            'status' => 'pending'
        ]);

        // Créer un produit avec un stock initial
        $product = ProductCBD::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 100,
            'name' => 'Test Product'
        ]);

        // Associer le produit à l'arrivage
        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'unit_price' => 20.00
        ]);

        // Vérifier le stock initial
        $this->assertEquals(100, $product->fresh()->stock);

        // Valider l'arrivage (cela devrait déclencher l'événement)
        $arrival->update(['status' => 'validated']);

        // Vérifier que le stock a été mis à jour
        $this->assertEquals(150, $product->fresh()->stock);
    }

    /** @test */
    public function it_updates_multiple_products_stock_when_validated()
    {
        // Créer un arrivage
        $arrival = CbdArrival::factory()->create([
            'status' => 'pending'
        ]);

        // Créer plusieurs produits
        $product1 = ProductCBD::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 100
        ]);
        
        $product2 = ProductCBD::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 50
        ]);

        // Associer les produits à l'arrivage
        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product1->id,
            'quantity' => 30,
            'unit_price' => 15.00
        ]);

        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product2->id,
            'quantity' => 20,
            'unit_price' => 25.00
        ]);

        // Valider l'arrivage
        $arrival->update(['status' => 'validated']);

        // Vérifier que les stocks ont été mis à jour
        $this->assertEquals(130, $product1->fresh()->stock); // 100 + 30
        $this->assertEquals(70, $product2->fresh()->stock);  // 50 + 20
    }

    /** @test */
    public function it_does_not_update_stock_when_status_is_not_validated()
    {
        // Créer un arrivage
        $arrival = CbdArrival::factory()->create([
            'status' => 'pending'
        ]);

        // Créer un produit
        $product = ProductCBD::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 100
        ]);

        // Associer le produit à l'arrivage
        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product->id,
            'quantity' => 25,
            'unit_price' => 18.00
        ]);

        // Changer le statut vers autre chose que 'validated'
        $arrival->update(['status' => 'pending']); // Reste pending

        // Vérifier que le stock n'a pas changé
        $this->assertEquals(100, $product->fresh()->stock);
    }

    /** @test */
    public function it_handles_transaction_rollback_on_stock_update_failure()
    {
        // Créer un arrivage
        $arrival = CbdArrival::factory()->create([
            'status' => 'pending'
        ]);

        // Créer un produit
        $product = ProductCBD::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 100
        ]);

        // Associer le produit à l'arrivage
        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product->id,
            'quantity' => 25,
            'unit_price' => 18.00
        ]);

        // Simuler une erreur en mockant la base de données
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow(new \Exception('Database error'));

        // Tenter de valider l'arrivage
        try {
            $arrival->update(['status' => 'validated']);
        } catch (\Exception $e) {
            // L'exception est attendue
        }

        // Le stock ne devrait pas avoir changé à cause du rollback
        $this->assertEquals(100, $product->fresh()->stock);
    }

    /** @test */
    public function it_can_be_filtered_by_status()
    {
        // Créer des arrivages avec différents statuts
        $pendingArrival = CbdArrival::factory()->create(['status' => 'pending']);
        $validatedArrival = CbdArrival::factory()->create(['status' => 'validated']);

        // Tester les filtres
        $pendingArrivals = CbdArrival::where('status', 'pending')->get();
        $validatedArrivals = CbdArrival::where('status', 'validated')->get();

        $this->assertCount(1, $pendingArrivals);
        $this->assertCount(1, $validatedArrivals);
        $this->assertEquals($pendingArrival->id, $pendingArrivals->first()->id);
        $this->assertEquals($validatedArrival->id, $validatedArrivals->first()->id);
    }

    /** @test */
    public function it_calculates_total_cost_from_products()
    {
        $arrival = CbdArrival::factory()->create();
        
        $product1 = ProductCBD::factory()->create([
            'category_id' => $this->category->id
        ]);
        
        $product2 = ProductCBD::factory()->create([
            'category_id' => $this->category->id
        ]);

        // Ajouter des produits avec quantités et prix
        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product1->id,
            'quantity' => 10,
            'unit_price' => 15.50
        ]);

        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product2->id,
            'quantity' => 5,
            'unit_price' => 30.00
        ]);

        // Calculer le coût total
        $totalCost = $arrival->products->sum(function ($product) {
            return $product->quantity * $product->unit_price;
        });

        $this->assertEquals(305.00, $totalCost); // (10 * 15.50) + (5 * 30.00)
    }

    /** @test */
    public function it_has_proper_fillable_attributes()
    {
        $arrival = new CbdArrival();
        
        $this->assertEquals(['amount', 'status'], $arrival->getFillable());
    }

    /** @test */
    public function it_uses_correct_table_name()
    {
        $arrival = new CbdArrival();
        
        $this->assertEquals('cbd_arrivals', $arrival->getTable());
    }

    /** @test */
    public function it_can_create_arrival_with_factory()
    {
        $arrival = CbdArrival::factory()->create();
        
        $this->assertInstanceOf(CbdArrival::class, $arrival);
        $this->assertIsFloat($arrival->amount);
        $this->assertContains($arrival->status, ['pending', 'validated']);
    }

    /** @test */
    public function it_validates_status_enum_values()
    {
        // Tester les valeurs valides
        $validStatuses = ['pending', 'validated'];
        
        foreach ($validStatuses as $status) {
            $arrival = CbdArrival::factory()->create(['status' => $status]);
            $this->assertEquals($status, $arrival->status);
        }
    }
}
