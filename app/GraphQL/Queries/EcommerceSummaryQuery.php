<?php

namespace App\GraphQL\Queries;

use App\Models\ProductCBD;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class EcommerceSummaryQuery
{
    public function __invoke()
    {
        // Statistiques produits
        $productStats = ProductCBD::select([
            DB::raw('COUNT(*) as total_products'),
            DB::raw('SUM(CASE WHEN stock > 0 AND stock <= 10 THEN 1 ELSE 0 END) as low_stock_products'),
            DB::raw('SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock_products'),
            DB::raw('SUM(price * stock) as total_value'),
            DB::raw('AVG(price) as average_price')
        ])->first();

        $totalCategories = Category::count();

        return [
            'totalProducts' => (int) $productStats->total_products,
            'totalCategories' => (int) $totalCategories,
            'lowStockProducts' => (int) $productStats->low_stock_products,
            'outOfStockProducts' => (int) $productStats->out_of_stock_products,
            'totalValue' => (float) ($productStats->total_value ?: 0),
            'averagePrice' => round((float) ($productStats->average_price ?: 0), 2),
        ];
    }
}
