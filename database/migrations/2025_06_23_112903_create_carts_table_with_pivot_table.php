<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('cbd_products')->onDelete('cascade');
            $table->integer('quantity');
            $table->timestamps();
            
            // Index unique pour éviter les doublons user_id + product_id
            $table->unique(['user_id', 'product_id']);
        });

        // Supprimé la table pivot car on utilise directement la table carts
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
