<?php

namespace App\Modules\Statistics\GraphQL\Queries;

use App\Modules\Statistics\Services\StatisticsService;

class DashboardStatisticsQuery
{
    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService = null)
    {
        $this->statisticsService = $statisticsService ?: new StatisticsService();
    }

    /**
     * Récupère les statistiques pour le dashboard
     */
    public function getDashboardStats($_, array $args)
    {
        return $this->statisticsService->getDashboardStats();
    }
}
