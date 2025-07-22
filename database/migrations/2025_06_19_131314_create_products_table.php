<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cbd_products', function (Blueprint $table) {
            $table->id(); // Identifiant unique
            $table->string('name'); // Nom du produit
            $table->text('description')->nullable(); // Description détaillée
            $table->decimal('price', 10, 2); // Prix avec 2 décimales
            $table->json('images')->nullable(); // Tableau JSON pour les URLs des images
            $table->integer('stock')->default(0); // Quantité en stock
            $table->string('analysis_file')->nullable(); // Fichier d'analyse (optionnel)
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null'); // Référence à la catégorie
            $table->timestamps(); // created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbd_products');
    }
}
