<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArrivalProductCbdTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('arrival_product_cbd', function (Blueprint $table) {
            $table->id(); // Identifiant unique
            $table->foreignId('arrival_id')->constrained('cbd_arrivals')->onDelete('cascade'); // Référence à cbd_arrivals
            $table->foreignId('product_id')->constrained('cbd_products')->onDelete('cascade'); // Référence à cbd_products
            $table->integer('quantity'); // Quantité de produit dans l'arrivage
            $table->decimal('unit_price', 10, 2); // Prix unitaire du produit
            $table->timestamps(); // created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arrival_product_cbd');
    }
};
