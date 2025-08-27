<?php

namespace App\Modules\Statistics\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\ProductCBD;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsService
{
    /**
     * Obtenir les statistiques des commandes pour une période
     */
    public function getOrderStatistics(Carbon $startDate, Carbon $endDate): array
    {
        $totalRevenue = Order::whereBetween('created_at', [$startDate, $endDate])->sum('total');
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $uniqueCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')->count('user_id');

        return [
            'totalRevenue' => (float)$totalRevenue,
            'totalOrders' => $totalOrders,
            'averageOrderValue' => (float)$averageOrderValue,
            'uniqueCustomers' => $uniqueCustomers,
        ];
    }

    /**
     * Obtenir les top produits pour une période
     */
    public function getTopProducts(Carbon $startDate, Carbon $endDate, int $limit = 10): array
    {
        return DB::table('order_product')
            ->join('orders', 'order_product.order_id', '=', 'orders.id')
            ->join('cbd_products', 'order_product.product_id', '=', 'cbd_products.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select('cbd_products.name', DB::raw('SUM(order_product.quantity) as total_quantity'))
            ->groupBy('cbd_products.id', 'cbd_products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Obtenir les top clients pour une période
     */
    public function getTopCustomers(Carbon $startDate, Carbon $endDate, int $limit = 10): array
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('user_id', DB::raw('SUM(total) as total_spent'), DB::raw('COUNT(*) as order_count'))
            ->groupBy('user_id')
            ->orderBy('total_spent', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Obtenir la timeline des revenus par jour
     */
    public function getRevenueTimeline(Carbon $startDate, Carbon $endDate): array
    {
        $dailyRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as order_count')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        return [
            'periods' => $dailyRevenue->toArray(),
            'totalRevenue' => $dailyRevenue->sum('revenue'),
            'totalOrders' => $dailyRevenue->sum('order_count')
        ];
    }

    /**
     * Obtenir les revenus mensuels
     */
    public function getMonthlyRevenue(int $months = 12): array
    {
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            $revenue = Order::whereBetween('created_at', [$monthStart, $monthEnd])->sum('total');
            $orderCount = Order::whereBetween('created_at', [$monthStart, $monthEnd])->count();
                
            $data[] = [
                'month' => $monthStart->format('Y-m'),
                'revenue' => (float)$revenue,
                'orderCount' => $orderCount
            ];
        }
        
        return [
            'data' => $data,
            'total_revenue' => array_sum(array_column($data, 'revenue')),
            'total_orders' => array_sum(array_column($data, 'orderCount'))
        ];
    }

    /**
     * Obtenir la croissance des clients
     */
    public function getCustomerGrowthTimeline(Carbon $startDate, Carbon $endDate): array
    {
        $periods = [];
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();
            
            $newCustomers = User::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $totalCustomers = User::where('created_at', '<=', $monthEnd)->count();
            
            $periods[] = [
                'period' => $monthStart->format('Y-m'),
                'newCustomers' => $newCustomers,
                'returningCustomers' => 0,
                'totalCustomers' => $totalCustomers,
                'growthRate' => 0.0
            ];
            
            $current->addMonth();
        }
        
        return [
            'periods' => $periods,
            'summary' => [
                'totalNewCustomers' => array_sum(array_column($periods, 'newCustomers')),
                'averageGrowthRate' => 0.0,
                'peakGrowthPeriod' => '',
                'projectedNextPeriod' => 0
            ],
            'trends' => [
                'isGrowing' => true,
                'trend' => 'stable',
                'momentum' => 0.0,
                'seasonality' => 'none'
            ]
        ];
    }

    /**
     * Obtenir les statistiques d'un utilisateur
     */
    public function getUserStatistics(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception("Utilisateur non trouvé");
        }

        $orders = Order::where("user_id", $userId)
            ->whereBetween("created_at", [$startDate, $endDate])
            ->get();
            
        $totalOrders = $orders->count();
        $totalAmount = $orders->sum("total");
        $averageOrderValue = $totalOrders > 0 ? $totalAmount / $totalOrders : 0;
        
        $firstOrder = Order::where("user_id", $userId)->oldest()->first();
        $daysSinceFirstOrder = $firstOrder ? $firstOrder->created_at->diffInDays(Carbon::now()) : 0;
        $orderFrequency = $daysSinceFirstOrder > 0 ? $totalOrders / ($daysSinceFirstOrder / 30) : 0;
        
        // Déterminer le segment client
        $customerSegment = 'NEW';
        if ($totalAmount > 1000) $customerSegment = 'VIP';
        elseif ($totalAmount > 500) $customerSegment = 'PREMIUM';
        elseif ($totalOrders > 5) $customerSegment = 'STANDARD';
        
        return [
            "userId" => (string)$userId,
            "userName" => $user->name,
            "email" => $user->email,
            "totalOrders" => $totalOrders,
            "totalAmount" => (float)$totalAmount,
            "averageOrderValue" => (float)$averageOrderValue,
            "orderFrequency" => (float)$orderFrequency,
            "daysSinceFirstOrder" => $daysSinceFirstOrder,
            "customerSegment" => $customerSegment,
            "favoriteProducts" => [],
            "favoriteCategories" => [],
            "behaviorAnalysis" => [
                "preferredTimeOfDay" => "Afternoon",
                "preferredDayOfWeek" => "Friday",
                "seasonality" => "SPRING",
                "spendingPattern" => $totalOrders > 0 ? "CONSISTENT" : "NO_DATA",
                "loyaltyScore" => min(100, $totalOrders * 10)
            ],
            "recommendations" => [
                "products" => [],
                "actions" => []
            ],
            "lastOrderDate" => $orders->isNotEmpty() ? $orders->sortByDesc("created_at")->first()->created_at->format("Y-m-d") : null,
            "memberSince" => $user->created_at->format("Y-m-d")
        ];
    }

    /**
     * Obtenir les statistiques du dashboard
     */
    public function getDashboardStats(): array
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();
        
        $currentMonthOrders = Order::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();
            
        $currentMonthRevenue = Order::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('total');
            
        $totalUsers = User::count();
        $totalProducts = ProductCBD::count();
        
        return [
            'currentMonth' => [
                'orders' => $currentMonthOrders,
                'revenue' => (float)$currentMonthRevenue,
                'users' => $totalUsers,
                'products' => $totalProducts
            ],
            'summary' => [
                'totalOrders' => Order::count(),
                'totalRevenue' => (float)Order::sum('total'),
                'totalUsers' => $totalUsers,
                'totalProducts' => $totalProducts
            ]
        ];
    }
}
