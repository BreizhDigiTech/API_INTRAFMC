<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GraphQLCacheService
{
    const TTL_CATEGORIES = 3600;      // 1 heure
    const TTL_PRODUCTS = 1800;       // 30 minutes
    const TTL_SUPPLIERS = 7200;      // 2 heures
    const TTL_USER_PROFILE = 900;    // 15 minutes
    const TTL_SCHEMA = 86400;        // 24 heures
    const TTL_PERMISSIONS = 1800;    // 30 minutes

    /**
     * Clés de cache standardisées
     */
    private function getCacheKey(string $type, string|int $identifier = null): string
    {
        $key = "graphql.{$type}";
        if ($identifier) {
            $key .= ".{$identifier}";
        }
        return $key;
    }

    /**
     * Cache des catégories
     */
    public function getCachedCategories(callable $dataLoader = null): array
    {
        return Cache::store('graphql')->remember(
            $this->getCacheKey('categories', 'all'),
            self::TTL_CATEGORIES,
            $dataLoader ?: fn() => []
        );
    }

    public function getCachedCategory(int $id, callable $dataLoader = null): ?array
    {
        return Cache::store('graphql')->remember(
            $this->getCacheKey('categories', $id),
            self::TTL_CATEGORIES,
            $dataLoader ?: fn() => null
        );
    }

    public function invalidateCategoriesCache(): void
    {
        $patterns = [
            $this->getCacheKey('categories', 'all'),
            $this->getCacheKey('categories', '*'),
        ];

        foreach ($patterns as $pattern) {
            Cache::store('graphql')->forget($pattern);
        }

        Log::info('Categories cache invalidated');
    }

    /**
     * Cache des produits
     */
    public function getCachedProducts(array $filters = [], callable $dataLoader = null): array
    {
        $cacheKey = $this->getCacheKey('products', md5(serialize($filters)));
        
        return Cache::store('graphql')->remember(
            $cacheKey,
            self::TTL_PRODUCTS,
            $dataLoader ?: fn() => []
        );
    }

    public function getCachedProduct(int $id, callable $dataLoader = null): ?array
    {
        return Cache::store('graphql')->remember(
            $this->getCacheKey('products', $id),
            self::TTL_PRODUCTS,
            $dataLoader ?: fn() => null
        );
    }

    public function invalidateProductsCache(): void
    {
        $patterns = [
            $this->getCacheKey('products', '*'),
        ];

        foreach ($patterns as $pattern) {
            Cache::store('graphql')->forget($pattern);
        }

        Log::info('Products cache invalidated');
    }

    /**
     * Cache des fournisseurs
     */
    public function getCachedSuppliers(callable $dataLoader = null): array
    {
        return Cache::store('graphql')->remember(
            $this->getCacheKey('suppliers', 'all'),
            self::TTL_SUPPLIERS,
            $dataLoader ?: fn() => []
        );
    }

    public function getCachedSupplier(int $id, callable $dataLoader = null): ?array
    {
        return Cache::store('graphql')->remember(
            $this->getCacheKey('suppliers', $id),
            self::TTL_SUPPLIERS,
            $dataLoader ?: fn() => null
        );
    }

    public function invalidateSuppliersCache(): void
    {
        Cache::store('graphql')->forget($this->getCacheKey('suppliers', 'all'));
        Log::info('Suppliers cache invalidated');
    }

    /**
     * Cache des utilisateurs
     */
    public function getCachedUserProfile(int $userId, callable $dataLoader = null): ?array
    {
        return Cache::store('graphql')->remember(
            $this->getCacheKey('user_profile', $userId),
            self::TTL_USER_PROFILE,
            $dataLoader ?: fn() => null
        );
    }

    public function invalidateUserCache(int $userId): void
    {
        Cache::store('graphql')->forget($this->getCacheKey('user_profile', $userId));
        Log::info("User cache invalidated for user: {$userId}");
    }

    /**
     * Cache des permissions
     */
    public function getCachedUserPermissions(int $userId, callable $dataLoader = null): array
    {
        return Cache::store('graphql')->remember(
            $this->getCacheKey('permissions', $userId),
            self::TTL_PERMISSIONS,
            $dataLoader ?: fn() => []
        );
    }

    public function invalidatePermissionsCache(int $userId): void
    {
        Cache::store('graphql')->forget($this->getCacheKey('permissions', $userId));
        Log::info("Permissions cache invalidated for user: {$userId}");
    }

    /**
     * Cache du schéma GraphQL
     */
    public function getCachedSchema(callable $dataLoader = null): ?string
    {
        return Cache::store('graphql')->remember(
            $this->getCacheKey('schema', 'current'),
            self::TTL_SCHEMA,
            $dataLoader ?: fn() => null
        );
    }

    public function invalidateSchemaCache(): void
    {
        Cache::store('graphql')->forget($this->getCacheKey('schema', 'current'));
        Log::info('GraphQL schema cache invalidated');
    }

    /**
     * Invalidation globale
     */
    public function clearAllCache(): void
    {
        Cache::store('graphql')->flush();
        Log::info('All GraphQL cache cleared');
    }

    /**
     * Méthodes utilitaires
     */
    public function warmUpCache(): void
    {
        Log::info('Starting cache warm-up...');
        
        // Préchauffer les données principales
        $this->getCachedCategories(function() {
            return \App\Models\Category::with('products')->get()->toArray();
        });

        $this->getCachedSuppliers(function() {
            return \App\Models\Supplier::all()->toArray();
        });

        Log::info('Cache warm-up completed');
    }

    public function getCacheStats(): array
    {
        return [
            'store' => 'graphql',
            'prefix' => Cache::store('graphql')->getPrefix(),
            'status' => Cache::store('graphql')->get('health_check', 'unknown'),
        ];
    }
}
