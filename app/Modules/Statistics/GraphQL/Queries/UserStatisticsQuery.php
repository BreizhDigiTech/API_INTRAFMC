<?php

namespace App\Modules\Statistics\GraphQL\Queries;

use App\Modules\Statistics\Services\StatisticsService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserStatisticsQuery
{
    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService = null)
    {
        $this->statisticsService = $statisticsService ?: new StatisticsService();
    }

    /**
     * Récupère les statistiques des commandes d'un utilisateur
     */
    public function getUserOrderStatistics($root, array $args)
    {
        $userId = $args["userId"] ?? Auth::id() ?? 1;
        $startDate = isset($args["startDate"]) ? Carbon::parse($args["startDate"]) : Carbon::now()->subYear();
        $endDate = isset($args["endDate"]) ? Carbon::parse($args["endDate"]) : Carbon::now();
        
        return $this->statisticsService->getUserStatistics($userId, $startDate, $endDate);
    }
}
