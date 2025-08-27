<?php

namespace App\Modules\Category\GraphQL\Queries;

use App\Models\Category;
use App\Models\ProductCBD;
use Illuminate\Support\Facades\DB;

class CategoryEnrichedQuery
{
    /**
     * Catégories avec compteurs de produits et informations enrichies
     */
    public function getCategoriesWithCounts($_, array $args)
    {
        return Category::with(['products'])
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug ?? str_replace(' ', '-', strtolower($category->name)),
                    'description' => $category->description,
                    'productCount' => $category->products->count(),
                    'parentId' => null, // À implémenter si hiérarchie needed
                    'level' => 0, // À implémenter si hiérarchie needed
                    'children' => [], // À implémenter si hiérarchie needed
                    'isActive' => true, // À ajouter en BDD si nécessaire
                    'displayOrder' => $category->display_order ?? 0,
                    'imageUrl' => $category->image_url ?? null
                ];
            });
    }
    
    /**
     * Produits populaires par catégorie
     */
    public function getPopularProductsByCategory($_, array $args)
    {
        $categoryId = $args['categoryId'];
        $limit = $args['limit'] ?? 5;
        $period = $args['period'] ?? 'MONTH';
        
        // Calculer la date de début selon la période
        $startDate = $this->getStartDateForPeriod($period);
        
        return DB::table('orders')
            ->join('order_product', 'orders.id', '=', 'order_product.order_id')
            ->join('cbd_products', 'order_product.product_id', '=', 'cbd_products.id')
            ->join('category_product', 'cbd_products.id', '=', 'category_product.product_id')
            ->where('category_product.category_id', $categoryId)
            ->where('orders.created_at', '>=', $startDate)
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'cbd_products.id',
                'cbd_products.name',
                'cbd_products.price',
                'cbd_products.stock',
                DB::raw('COUNT(DISTINCT orders.id) as orderCount'),
                DB::raw('SUM(order_product.quantity * order_product.unit_price) as revenue')
            )
            ->groupBy('cbd_products.id', 'cbd_products.name', 'cbd_products.price', 'cbd_products.stock')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => (float) $item->price,
                    'stock' => (int) $item->stock,
                    'orderCount' => (int) $item->orderCount,
                    'revenue' => (float) $item->revenue
                ];
            });
    }
    
    /**
     * Calcule la date de début selon la période
     */
    private function getStartDateForPeriod($period)
    {
        switch ($period) {
            case 'WEEK':
                return now()->subWeek();
            case 'MONTH':
                return now()->subMonth();
            case 'QUARTER':
                return now()->subQuarter();
            case 'YEAR':
                return now()->subYear();
            default:
                return now()->subMonth();
        }
    }
}
