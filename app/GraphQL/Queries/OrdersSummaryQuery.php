<?php

namespace App\GraphQL\Queries;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrdersSummaryQuery
{
    public function __invoke()
    {
        $summary = Order::select([
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_orders'),
            DB::raw('SUM(CASE WHEN status = "validated" OR status = "shipped" OR status = "delivered" THEN 1 ELSE 0 END) as validated_orders'),
            DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders'),
            DB::raw('SUM(CASE WHEN status != "cancelled" THEN total ELSE 0 END) as total_revenue')
        ])->first();

        return [
            'totalOrders' => (int) $summary->total_orders,
            'pendingOrders' => (int) $summary->pending_orders,
            'validatedOrders' => (int) $summary->validated_orders,
            'cancelledOrders' => (int) $summary->cancelled_orders,
            'totalRevenue' => (float) ($summary->total_revenue ?: 0),
        ];
    }
}
