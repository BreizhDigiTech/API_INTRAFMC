<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCbdArrivalsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cbd_arrivals', function (Blueprint $table) {
            $table->id(); // Identifiant unique
            $table->decimal('amount', 10, 2); // Montant total de l'arrivage
            $table->enum('status', ['pending', 'validated'])->default('pending'); // Statut de l'arrivage
            $table->timestamps(); // created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbd_arrivals');
    }
};
