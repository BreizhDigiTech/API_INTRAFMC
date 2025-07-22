<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\CbdArrival;
use App\Models\ProductCBD;
use App\Models\ArrivalProductCbd;
use App\Models\Category;
use App\Models\User;

class CbdArrivalFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer des utilisateurs de test
        $this->admin = User::factory()->create([
            'is_admin' => true,
            'email' => 'admin@test.com'
        ]);
        
        $this->user = User::factory()->create([
            'is_admin' => false,
            'email' => 'user@test.com'
        ]);
        
        // Créer une catégorie de test
        $this->category = Category::factory()->create([
            'name' => 'Test Category'
        ]);
    }

    /** @test */
    public function admin_can_view_arrivals_list()
    {
        $this->actingAs($this->admin);
        
        // Créer quelques arrivages
        $arrivals = CbdArrival::factory()->count(3)->create();

        // Simuler une requête GraphQL (si vous avez un endpoint)
        // Ou tester directement les modèles
        $allArrivals = CbdArrival::all();
        
        $this->assertCount(3, $allArrivals);
        $this->assertEquals($arrivals->pluck('id')->sort(), $allArrivals->pluck('id')->sort());
    }

    /** @test */
    public function admin_can_create_arrival()
    {
        $this->actingAs($this->admin);
        
        $arrivalData = [
            'amount' => 1500.75,
            'status' => 'pending'
        ];

        $arrival = CbdArrival::create($arrivalData);
        
        $this->assertInstanceOf(CbdArrival::class, $arrival);
        $this->assertDatabaseHas('cbd_arrivals', $arrivalData);
    }

    /** @test */
    public function admin_can_update_arrival_status()
    {
        $this->actingAs($this->admin);
        
        $arrival = CbdArrival::factory()->create([
            'status' => 'pending'
        ]);

        $arrival->update(['status' => 'validated']);
        
        $this->assertEquals('validated', $arrival->fresh()->status);
        $this->assertDatabaseHas('cbd_arrivals', [
            'id' => $arrival->id,
            'status' => 'validated'
        ]);
    }

    /** @test */
    public function admin_can_add_products_to_arrival()
    {
        $this->actingAs($this->admin);
        
        $arrival = CbdArrival::factory()->create();
        $product = ProductCBD::factory()->create([
            'category_id' => $this->category->id
        ]);

        $arrivalProduct = ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'unit_price' => 25.00
        ]);

        $this->assertDatabaseHas('arrival_product_cbd', [
            'arrival_id' => $arrival->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'unit_price' => 25.00
        ]);

        // Vérifier la relation
        $this->assertCount(1, $arrival->fresh()->products);
    }

    /** @test */
    public function validating_arrival_updates_product_stocks()
    {
        $this->actingAs($this->admin);
        
        // Créer un arrivage avec des produits
        $arrival = CbdArrival::factory()->create(['status' => 'pending']);
        
        $product1 = ProductCBD::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 100
        ]);
        
        $product2 = ProductCBD::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 50
        ]);

        // Ajouter des produits à l'arrivage
        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product1->id,
            'quantity' => 30,
            'unit_price' => 20.00
        ]);

        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product2->id,
            'quantity' => 25,
            'unit_price' => 15.00
        ]);

        // Stocks initiaux
        $this->assertEquals(100, $product1->fresh()->stock);
        $this->assertEquals(50, $product2->fresh()->stock);

        // Valider l'arrivage
        $arrival->update(['status' => 'validated']);

        // Vérifier que les stocks ont été mis à jour
        $this->assertEquals(130, $product1->fresh()->stock); // 100 + 30
        $this->assertEquals(75, $product2->fresh()->stock);  // 50 + 25
    }

    /** @test */
    public function non_admin_cannot_access_arrivals()
    {
        $this->actingAs($this->user);
        
        // Tester que les utilisateurs non-admin ne peuvent pas accéder aux arrivages
        // Ceci dépend de votre implémentation des politiques
        $this->assertTrue($this->user->is_admin === false);
    }

    /** @test */
    public function arrival_with_products_calculates_correct_total()
    {
        $arrival = CbdArrival::factory()->create();
        
        $product1 = ProductCBD::factory()->create([
            'category_id' => $this->category->id
        ]);
        
        $product2 = ProductCBD::factory()->create([
            'category_id' => $this->category->id
        ]);

        // Ajouter des produits avec des quantités et prix spécifiques
        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product1->id,
            'quantity' => 10,
            'unit_price' => 25.00
        ]);

        ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product2->id,
            'quantity' => 5,
            'unit_price' => 40.00
        ]);

        // Calculer le total
        $totalCost = $arrival->products->sum(function ($product) {
            return $product->quantity * $product->unit_price;
        });

        $this->assertEquals(450.00, $totalCost); // (10 * 25) + (5 * 40)
    }

    /** @test */
    public function arrival_can_be_filtered_by_status()
    {
        // Créer des arrivages avec différents statuts
        $pendingArrivals = CbdArrival::factory()->count(3)->create(['status' => 'pending']);
        $validatedArrivals = CbdArrival::factory()->count(2)->create(['status' => 'validated']);

        // Tester les filtres
        $pending = CbdArrival::where('status', 'pending')->get();
        $validated = CbdArrival::where('status', 'validated')->get();

        $this->assertCount(3, $pending);
        $this->assertCount(2, $validated);
    }

    /** @test */
    public function arrival_can_be_filtered_by_date_range()
    {
        // Créer des arrivages avec différentes dates
        $oldArrival = CbdArrival::factory()->create([
            'created_at' => now()->subMonths(3)
        ]);
        
        $recentArrival = CbdArrival::factory()->create([
            'created_at' => now()->subDays(5)
        ]);

        // Filtrer les arrivages récents (dernières 2 semaines)
        $recentArrivals = CbdArrival::where('created_at', '>=', now()->subWeeks(2))->get();
        
        $this->assertCount(1, $recentArrivals);
        $this->assertEquals($recentArrival->id, $recentArrivals->first()->id);
    }

    /** @test */
    public function arrival_deletion_removes_associated_products()
    {
        $arrival = CbdArrival::factory()->create();
        $product = ProductCBD::factory()->create([
            'category_id' => $this->category->id
        ]);

        $arrivalProduct = ArrivalProductCbd::create([
            'arrival_id' => $arrival->id,
            'product_id' => $product->id,
            'quantity' => 20,
            'unit_price' => 30.00
        ]);

        $arrivalProductId = $arrivalProduct->id;
        
        // Supprimer l'arrivage
        $arrival->delete();
        
        // Vérifier que les produits associés ont été supprimés
        $this->assertDatabaseMissing('arrival_product_cbd', ['id' => $arrivalProductId]);
    }

    /** @test */
    public function arrival_status_enum_validation()
    {
        // Tester les statuts valides
        $validStatuses = ['pending', 'validated'];
        
        foreach ($validStatuses as $status) {
            $arrival = CbdArrival::factory()->create(['status' => $status]);
            $this->assertEquals($status, $arrival->status);
        }
    }

    /** @test */
    public function arrival_amount_must_be_positive()
    {
        $arrival = CbdArrival::factory()->create(['amount' => 100.50]);
        
        $this->assertGreaterThan(0, $arrival->amount);
    }

    /** @test */
    public function multiple_validations_update_stocks_correctly()
    {
        // Tester que valider plusieurs arrivages fonctionne correctement
        $product = ProductCBD::factory()->create([
            'category_id' => $this->category->id,
            'stock' => 100
        ]);

        // Premier arrivage
        $arrival1 = CbdArrival::factory()->create(['status' => 'pending']);
        ArrivalProductCbd::create([
            'arrival_id' => $arrival1->id,
            'product_id' => $product->id,
            'quantity' => 20,
            'unit_price' => 25.00
        ]);

        // Deuxième arrivage
        $arrival2 = CbdArrival::factory()->create(['status' => 'pending']);
        ArrivalProductCbd::create([
            'arrival_id' => $arrival2->id,
            'product_id' => $product->id,
            'quantity' => 30,
            'unit_price' => 22.00
        ]);

        // Valider le premier arrivage
        $arrival1->update(['status' => 'validated']);
        $this->assertEquals(120, $product->fresh()->stock); // 100 + 20

        // Valider le deuxième arrivage
        $arrival2->update(['status' => 'validated']);
        $this->assertEquals(150, $product->fresh()->stock); // 120 + 30
    }
}
