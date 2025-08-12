<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Orders
        Schema::table('orders', function (Blueprint $table) {
            // Indexes to speed up filters and sorts
            $table->index('user_id', 'orders_user_id_index');
            $table->index('status', 'orders_status_index');
            $table->index('created_at', 'orders_created_at_index');
        });

        // Order pivot
        Schema::table('order_product', function (Blueprint $table) {
            $table->index('order_id', 'order_product_order_id_index');
            $table->index('product_id', 'order_product_product_id_index');
        });

        // Products
        Schema::table('cbd_products', function (Blueprint $table) {
            $table->index('category_id', 'cbd_products_category_id_index');
            $table->index('created_at', 'cbd_products_created_at_index');
        });

        // Suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            $table->index('name', 'suppliers_name_index');
            $table->index('email', 'suppliers_email_index');
            $table->index('created_at', 'suppliers_created_at_index');
        });

        // Arrivals
        Schema::table('cbd_arrivals', function (Blueprint $table) {
            $table->index('status', 'cbd_arrivals_status_index');
            $table->index('created_at', 'cbd_arrivals_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_id_index');
            $table->dropIndex('orders_status_index');
            $table->dropIndex('orders_created_at_index');
        });

        Schema::table('order_product', function (Blueprint $table) {
            $table->dropIndex('order_product_order_id_index');
            $table->dropIndex('order_product_product_id_index');
        });

        Schema::table('cbd_products', function (Blueprint $table) {
            $table->dropIndex('cbd_products_category_id_index');
            $table->dropIndex('cbd_products_created_at_index');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex('suppliers_name_index');
            $table->dropIndex('suppliers_email_index');
            $table->dropIndex('suppliers_created_at_index');
        });

        Schema::table('cbd_arrivals', function (Blueprint $table) {
            $table->dropIndex('cbd_arrivals_status_index');
            $table->dropIndex('cbd_arrivals_created_at_index');
        });
    }
};
