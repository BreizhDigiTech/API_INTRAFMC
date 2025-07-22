<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryOptimizationService
{
    /**
     * Optimise les requêtes de produits avec eager loading
     */
    public function getOptimizedProducts(array $filters = []): Collection
    {
        $query = DB::table('products')
            ->select([
                'products.id',
                'products.name',
                'products.type',
                'products.price',
                'products.stock',
                'products.description',
                'products.created_at',
                'products.updated_at'
            ]);

        // Application des filtres avec index
        if (isset($filters['type'])) {
            $query->where('products.type', $filters['type']);
        }

        if (isset($filters['min_price'])) {
            $query->where('products.price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('products.price', '<=', $filters['max_price']);
        }

        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $query->where('products.stock', '>', 0);
        }

        if (isset($filters['category_id'])) {
            $query->join('category_product', 'products.id', '=', 'category_product.product_id')
                  ->where('category_product.category_id', $filters['category_id']);
        }

        // Optimisation de l'ordre avec index
        $query->orderBy('products.created_at', 'desc');

        // Limitation pour éviter les requêtes trop lourdes
        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        $startTime = microtime(true);
        $results = $query->get();
        $executionTime = microtime(true) - $startTime;

        Log::info('Optimized products query executed', [
            'execution_time' => $executionTime,
            'result_count' => $results->count(),
            'filters' => $filters
        ]);

        return $results;
    }

    /**
     * Optimise les requêtes de catégories avec leurs produits
     */
    public function getOptimizedCategoriesWithProducts(): Collection
    {
        $startTime = microtime(true);

        // Requête optimisée avec join unique
        $results = DB::table('categories')
            ->select([
                'categories.id as category_id',
                'categories.name as category_name',
                'categories.description as category_description',
                'products.id as product_id',
                'products.name as product_name',
                'products.type as product_type',
                'products.price as product_price',
                'products.stock as product_stock'
            ])
            ->leftJoin('category_product', 'categories.id', '=', 'category_product.category_id')
            ->leftJoin('products', 'category_product.product_id', '=', 'products.id')
            ->orderBy('categories.name')
            ->orderBy('products.name')
            ->get();

        $executionTime = microtime(true) - $startTime;

        Log::info('Optimized categories with products query executed', [
            'execution_time' => $executionTime,
            'result_count' => $results->count()
        ]);

        return $results;
    }

    /**
     * Optimise les requêtes de commandes avec informations utilisateur
     */
    public function getOptimizedOrders(array $filters = []): Collection
    {
        $query = DB::table('orders')
            ->select([
                'orders.id',
                'orders.user_id',
                'orders.total_amount',
                'orders.status',
                'orders.created_at',
                'users.name as user_name',
                'users.email as user_email'
            ])
            ->join('users', 'orders.user_id', '=', 'users.id');

        // Application des filtres avec index
        if (isset($filters['status'])) {
            $query->where('orders.status', $filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->where('orders.user_id', $filters['user_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('orders.created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('orders.created_at', '<=', $filters['date_to']);
        }

        // Optimisation de l'ordre avec index
        $query->orderBy('orders.created_at', 'desc');

        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        $startTime = microtime(true);
        $results = $query->get();
        $executionTime = microtime(true) - $startTime;

        Log::info('Optimized orders query executed', [
            'execution_time' => $executionTime,
            'result_count' => $results->count(),
            'filters' => $filters
        ]);

        return $results;
    }

    /**
     * Optimise les requêtes de panier utilisateur
     */
    public function getOptimizedUserCart(int $userId): Collection
    {
        $startTime = microtime(true);

        $results = DB::table('carts')
            ->select([
                'carts.id',
                'carts.quantity',
                'carts.created_at',
                'products.id as product_id',
                'products.name as product_name',
                'products.type as product_type',
                'products.price as product_price',
                'products.stock as product_stock'
            ])
            ->join('products', 'carts.product_id', '=', 'products.id')
            ->where('carts.user_id', $userId)
            ->orderBy('carts.created_at', 'desc')
            ->get();

        $executionTime = microtime(true) - $startTime;

        Log::info('Optimized user cart query executed', [
            'execution_time' => $executionTime,
            'user_id' => $userId,
            'cart_items' => $results->count()
        ]);

        return $results;
    }

    /**
     * Statistiques de performance des requêtes
     */
    public function getQueryPerformanceStats(): array
    {
        $startTime = microtime(true);

        $stats = [
            'total_products' => DB::table('products')->count(),
            'total_categories' => DB::table('categories')->count(),
            'total_users' => DB::table('users')->count(),
            'total_orders' => DB::table('orders')->count(),
            'total_suppliers' => DB::table('suppliers')->count(),
            'products_in_stock' => DB::table('products')->where('stock', '>', 0)->count(),
            'pending_orders' => DB::table('orders')->where('status', 'pending')->count(),
            'recent_arrivals' => DB::table('cbd_arrivals')
                ->where('arrival_date', '>=', now()->subDays(30))
                ->count(),
        ];

        $executionTime = microtime(true) - $startTime;

        Log::info('Performance stats query executed', [
            'execution_time' => $executionTime,
            'stats' => $stats
        ]);

        return array_merge($stats, ['query_time' => $executionTime]);
    }

    /**
     * Analyse des requêtes lentes
     */
    public function analyzeSlowQueries(): array
    {
        // Activation du log des requêtes lentes
        DB::enableQueryLog();

        $startTime = microtime(true);

        // Simulation de requêtes potentiellement lentes
        $slowQueries = [];

        // Test 1: Produits sans index sur description
        $query1Start = microtime(true);
        DB::table('products')->where('description', 'like', '%test%')->get();
        $query1Time = microtime(true) - $query1Start;
        
        if ($query1Time > 0.1) {
            $slowQueries[] = [
                'type' => 'products_description_search',
                'time' => $query1Time,
                'recommendation' => 'Add fulltext index on description field'
            ];
        }

        // Test 2: Jointures complexes
        $query2Start = microtime(true);
        DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('carts', 'users.id', '=', 'carts.user_id')
            ->join('products', 'carts.product_id', '=', 'products.id')
            ->get();
        $query2Time = microtime(true) - $query2Start;
        
        if ($query2Time > 0.2) {
            $slowQueries[] = [
                'type' => 'complex_joins',
                'time' => $query2Time,
                'recommendation' => 'Consider denormalization or caching'
            ];
        }

        $totalTime = microtime(true) - $startTime;

        return [
            'analysis_time' => $totalTime,
            'slow_queries' => $slowQueries,
            'total_queries_analyzed' => 2,
            'recommendations' => $this->getPerformanceRecommendations()
        ];
    }

    /**
     * Recommandations de performance
     */
    private function getPerformanceRecommendations(): array
    {
        return [
            'database' => [
                'Add Redis caching for frequently accessed data',
                'Implement database connection pooling',
                'Consider read replicas for heavy read workloads',
                'Optimize JOIN queries with proper indexing'
            ],
            'application' => [
                'Use eager loading to reduce N+1 queries',
                'Implement GraphQL DataLoader pattern',
                'Add query result caching',
                'Use pagination for large datasets'
            ],
            'monitoring' => [
                'Set up slow query logging',
                'Monitor database connection pool usage',
                'Track GraphQL query complexity',
                'Implement performance alerts'
            ]
        ];
    }
}
