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
        // Vérifier et corriger la structure de la table orders
        Schema::table('orders', function (Blueprint $table) {
            // Changer le type de total en decimal pour plus de précision
            $table->decimal('total', 10, 2)->change();
            
            // Ajouter des statuts supplémentaires si nécessaire
            // Note: MySQL ne supporte pas ALTER COLUMN pour les ENUM, donc on garde le statut actuel
        });

        // Vérifier et corriger la structure de la table order_product
        Schema::table('order_product', function (Blueprint $table) {
            // Changer unit_price en decimal pour plus de précision
            $table->decimal('unit_price', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('total')->change();
        });

        Schema::table('order_product', function (Blueprint $table) {
            $table->float('unit_price')->change();
        });
    }
};
