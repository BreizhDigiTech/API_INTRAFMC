<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Index pour SQLite - création directe avec IF NOT EXISTS
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)');
            
            DB::statement('CREATE INDEX IF NOT EXISTS idx_categories_name ON categories(name)');
            
            DB::statement('CREATE INDEX IF NOT EXISTS idx_products_name ON products(name)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_products_type ON products(type)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_products_price ON products(price)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_products_stock ON products(stock)');
            
            DB::statement('CREATE INDEX IF NOT EXISTS idx_suppliers_name ON suppliers(name)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_suppliers_email ON suppliers(email)');
            
            DB::statement('CREATE INDEX IF NOT EXISTS idx_carts_user_id ON carts(user_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_carts_product_id ON carts(product_id)');
            
            DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders(user_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status)');
        } catch (\Exception $e) {
            // Ignorer les erreurs si les tables n'existent pas encore
        }
    }

    public function down(): void
    {
        // SQLite supporte DROP INDEX IF EXISTS
        DB::statement('DROP INDEX IF EXISTS idx_users_email');
        DB::statement('DROP INDEX IF EXISTS idx_users_role');
        DB::statement('DROP INDEX IF EXISTS idx_categories_name');
        DB::statement('DROP INDEX IF EXISTS idx_products_name');
        DB::statement('DROP INDEX IF EXISTS idx_products_type');
        DB::statement('DROP INDEX IF EXISTS idx_products_price');
        DB::statement('DROP INDEX IF EXISTS idx_products_stock');
        DB::statement('DROP INDEX IF EXISTS idx_suppliers_name');
        DB::statement('DROP INDEX IF EXISTS idx_suppliers_email');
        DB::statement('DROP INDEX IF EXISTS idx_carts_user_id');
        DB::statement('DROP INDEX IF EXISTS idx_carts_product_id');
        DB::statement('DROP INDEX IF EXISTS idx_orders_user_id');
        DB::statement('DROP INDEX IF EXISTS idx_orders_status');
    }
};
