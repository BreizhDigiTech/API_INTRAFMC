<?php

namespace App\GraphQL\Queries;

use App\Models\Order;
use App\Models\User;
use App\Models\ProductCBD;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardStatsQuery
{
    public function __invoke()
    {
        $now = Carbon::now();
        $thisMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Statistiques commandes
        $orders = $this->getOrdersStats($thisMonth, $lastMonth, $lastMonthEnd);
        
        // Statistiques revenus
        $revenue = $this->getRevenueStats($thisMonth, $lastMonth, $lastMonthEnd);
        
        // Statistiques utilisateurs
        $users = $this->getUsersStats($thisMonth);
        
        // Statistiques produits
        $products = $this->getProductsStats();

        return [
            'orders' => $orders,
            'revenue' => $revenue,
            'users' => $users,
            'products' => $products,
        ];
    }

    private function getOrdersStats($thisMonth, $lastMonth, $lastMonthEnd)
    {
        $totalOrders = Order::count();
        $thisMonthOrders = Order::where('created_at', '>=', $thisMonth)->count();
        $lastMonthOrders = Order::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        
        $growth = $lastMonthOrders > 0 
            ? round((($thisMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1)
            : 0;

        return [
            'total' => $totalOrders,
            'thisMonth' => $thisMonthOrders,
            'lastMonth' => $lastMonthOrders,
            'growth' => $growth,
        ];
    }

    private function getRevenueStats($thisMonth, $lastMonth, $lastMonthEnd)
    {
        $totalRevenue = Order::whereNotIn('status', ['cancelled'])->sum('total');
        $thisMonthRevenue = Order::where('created_at', '>=', $thisMonth)
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');
        $lastMonthRevenue = Order::whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');
        
        $growth = $lastMonthRevenue > 0 
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        return [
            'total' => (float) $totalRevenue,
            'thisMonth' => (float) $thisMonthRevenue,
            'lastMonth' => (float) $lastMonthRevenue,
            'growth' => $growth,
        ];
    }

    private function getUsersStats($thisMonth)
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $newThisMonth = User::where('created_at', '>=', $thisMonth)->count();

        return [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'newThisMonth' => $newThisMonth,
        ];
    }

    private function getProductsStats()
    {
        $totalProducts = ProductCBD::count();
        $lowStock = ProductCBD::where('stock', '>', 0)->where('stock', '<=', 10)->count();
        $outOfStock = ProductCBD::where('stock', 0)->count();

        return [
            'total' => $totalProducts,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
        ];
    }
}
