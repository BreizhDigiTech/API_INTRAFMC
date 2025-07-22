<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index pour la table users
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email'], 'idx_users_email');
            $table->index(['role'], 'idx_users_role');
            $table->index(['created_at'], 'idx_users_created_at');
            $table->index(['email', 'role'], 'idx_users_email_role');
        });

        // Index pour la table categories
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['name'], 'idx_categories_name');
            $table->index(['created_at'], 'idx_categories_created_at');
        });

        // Index pour la table products
        Schema::table('products', function (Blueprint $table) {
            $table->index(['name'], 'idx_products_name');
            $table->index(['type'], 'idx_products_type');
            $table->index(['price'], 'idx_products_price');
            $table->index(['stock'], 'idx_products_stock');
            $table->index(['created_at'], 'idx_products_created_at');
            $table->index(['type', 'stock'], 'idx_products_type_stock');
            $table->index(['price', 'stock'], 'idx_products_price_stock');
        });

        // Index pour la table suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            $table->index(['name'], 'idx_suppliers_name');
            $table->index(['email'], 'idx_suppliers_email');
            $table->index(['created_at'], 'idx_suppliers_created_at');
        });

        // Index pour la table category_product (pivot)
        Schema::table('category_product', function (Blueprint $table) {
            $table->index(['category_id'], 'idx_category_product_category');
            $table->index(['product_id'], 'idx_category_product_product');
            $table->index(['category_id', 'product_id'], 'idx_category_product_both');
        });

        // Index pour la table carts
        Schema::table('carts', function (Blueprint $table) {
            $table->index(['user_id'], 'idx_carts_user');
            $table->index(['product_id'], 'idx_carts_product');
            $table->index(['created_at'], 'idx_carts_created_at');
            $table->index(['user_id', 'product_id'], 'idx_carts_user_product');
        });

        // Index pour la table orders
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id'], 'idx_orders_user');
            $table->index(['status'], 'idx_orders_status');
            $table->index(['created_at'], 'idx_orders_created_at');
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
            $table->index(['status', 'created_at'], 'idx_orders_status_date');
        });

        // Index pour la table cbd_arrivals
        Schema::table('cbd_arrivals', function (Blueprint $table) {
            $table->index(['supplier_id'], 'idx_cbd_arrivals_supplier');
            $table->index(['arrival_date'], 'idx_cbd_arrivals_date');
            $table->index(['created_at'], 'idx_cbd_arrivals_created_at');
        });

        // Index pour la table arrival_product_cbd (pivot)
        Schema::table('arrival_product_cbd', function (Blueprint $table) {
            $table->index(['cbd_arrival_id'], 'idx_arrival_product_arrival');
            $table->index(['product_id'], 'idx_arrival_product_product');
            $table->index(['cbd_arrival_id', 'product_id'], 'idx_arrival_product_both');
        });
    }

    public function down(): void
    {
        // Suppression des index pour users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_created_at');
            $table->dropIndex('idx_users_email_role');
        });

        // Suppression des index pour categories
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_name');
            $table->dropIndex('idx_categories_created_at');
        });

        // Suppression des index pour products
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_name');
            $table->dropIndex('idx_products_type');
            $table->dropIndex('idx_products_price');
            $table->dropIndex('idx_products_stock');
            $table->dropIndex('idx_products_created_at');
            $table->dropIndex('idx_products_type_stock');
            $table->dropIndex('idx_products_price_stock');
        });

        // Suppression des index pour suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex('idx_suppliers_name');
            $table->dropIndex('idx_suppliers_email');
            $table->dropIndex('idx_suppliers_created_at');
        });

        // Suppression des index pour category_product
        Schema::table('category_product', function (Blueprint $table) {
            $table->dropIndex('idx_category_product_category');
            $table->dropIndex('idx_category_product_product');
            $table->dropIndex('idx_category_product_both');
        });

        // Suppression des index pour carts
        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex('idx_carts_user');
            $table->dropIndex('idx_carts_product');
            $table->dropIndex('idx_carts_created_at');
            $table->dropIndex('idx_carts_user_product');
        });

        // Suppression des index pour orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_user');
            $table->dropIndex('idx_orders_status');
            $table->dropIndex('idx_orders_created_at');
            $table->dropIndex('idx_orders_user_status');
            $table->dropIndex('idx_orders_status_date');
        });

        // Suppression des index pour cbd_arrivals
        Schema::table('cbd_arrivals', function (Blueprint $table) {
            $table->dropIndex('idx_cbd_arrivals_supplier');
            $table->dropIndex('idx_cbd_arrivals_date');
            $table->dropIndex('idx_cbd_arrivals_created_at');
        });

        // Suppression des index pour arrival_product_cbd
        Schema::table('arrival_product_cbd', function (Blueprint $table) {
            $table->dropIndex('idx_arrival_product_arrival');
            $table->dropIndex('idx_arrival_product_product');
            $table->dropIndex('idx_arrival_product_both');
        });
    }
};
