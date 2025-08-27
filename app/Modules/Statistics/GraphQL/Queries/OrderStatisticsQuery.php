<?php

namespace App\Modules\Statistics\GraphQL\Queries;

use App\Modules\Statistics\Services\StatisticsService;
use Carbon\Carbon;

class OrderStatisticsQuery
{
    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService = null)
    {
        $this->statisticsService = $statisticsService ?: new StatisticsService();
    }

    /**
     * Statistiques générales des commandes sur une période
     */
    public function getOrderStatistics($_, array $args)
    {
        $startDate = Carbon::parse($args['startDate']);
        $endDate = Carbon::parse($args['endDate']);
        
        $stats = $this->statisticsService->getOrderStatistics($startDate, $endDate);
        $topProducts = $this->statisticsService->getTopProducts($startDate, $endDate, 10);
        $topCustomers = $this->statisticsService->getTopCustomers($startDate, $endDate, 10);
        
        return array_merge($stats, [
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers
        ]);
    }
    
    /**
     * Timeline des revenus
     */
    public function getRevenueTimeline($_, array $args)
    {
        $startDate = Carbon::parse($args['startDate']);
        $endDate = Carbon::parse($args['endDate']);
        
        return $this->statisticsService->getRevenueTimeline($startDate, $endDate);
    }
    
    /**
     * Statistiques basiques pour utilisateurs connectés
     */
    public function getBasicOrderStats($_, array $args)
    {
        $startDate = Carbon::parse($args['startDate']);
        $endDate = Carbon::parse($args['endDate']);
        
        $stats = $this->statisticsService->getOrderStatistics($startDate, $endDate);
        $popularProducts = $this->statisticsService->getTopProducts($startDate, $endDate, 5);
        
        return [
            'totalOrders' => $stats['totalOrders'],
            'totalRevenue' => $stats['totalRevenue'],
            'averageOrderValue' => $stats['averageOrderValue'],
            'popularProducts' => $popularProducts
        ];
    }
    
    /**
     * Revenus mensuels
     */
    public function getMonthlyRevenue($_, array $args)
    {
        $months = $args['months'] ?? 12;
        return $this->statisticsService->getMonthlyRevenue($months);
    }
    
    /**
     * Timeline de croissance client
     */
    public function getCustomerGrowthTimeline($_, array $args)
    {
        $startDate = isset($args['startDate']) ? Carbon::parse($args['startDate']) : Carbon::now()->subYear();
        $endDate = isset($args['endDate']) ? Carbon::parse($args['endDate']) : Carbon::now();
        
        return $this->statisticsService->getCustomerGrowthTimeline($startDate, $endDate);
    }
}
