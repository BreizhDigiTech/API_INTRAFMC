<?php

namespace App\Modules\FinancialRecap\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;
use App\Models\CbdArrival;
use App\Models\User;

class FinancialRecapService
{
    /**
     * Génère un rapport financier complet pour une période donnée
     */
    public function generateFinancialReport(array $filters)
    {
        $cacheKey = $this->generateCacheKey($filters);
        
        return Cache::remember($cacheKey, 300, function () use ($filters) {
            $startDate = Carbon::parse($filters['startDate']);
            $endDate = Carbon::parse($filters['endDate']);

            return [
                'summary' => $this->generateSummary($startDate, $endDate, $filters),
                'timeline' => $this->generateTimeline($startDate, $endDate, $filters),
                'topCustomers' => $this->getTopCustomers($startDate, $endDate, $filters),
                'insights' => $this->generateInsights($startDate, $endDate, $filters)
            ];
        });
    }

    /**
     * Génère des insights financiers
     */
    public function generateInsights($startDate, $endDate, $filters)
    {
        // Comparaison avec la période précédente
        $previousPeriod = $this->getPreviousPeriod($startDate, $endDate);
        $currentData = $this->getFinancialData($startDate, $endDate, $filters);
        $previousData = $this->getFinancialData($previousPeriod['start'], $previousPeriod['end'], $filters);

        $insights = [
            'performance' => $this->comparePerformance($currentData, $previousData),
            'trends' => $this->analyzeTrends($startDate, $endDate, $filters),
            'recommendations' => $this->generateRecommendations($currentData, $previousData)
        ];

        return $insights;
    }

    /**
     * Analyse des tendances
     */
    public function analyzeTrends($startDate, $endDate, $filters)
    {
        // Analyse des tendances hebdomadaires
        $weeklyData = $this->getWeeklyData($startDate, $endDate, $filters);
        
        // Calcul des moyennes mobiles
        $movingAverages = $this->calculateMovingAverages($weeklyData);
        
        // Détection des patterns saisonniers
        $seasonalPatterns = $this->detectSeasonalPatterns($startDate, $endDate);

        return [
            'weeklyTrends' => $weeklyData,
            'movingAverages' => $movingAverages,
            'seasonalPatterns' => $seasonalPatterns,
            'growthRate' => $this->calculateGrowthRate($weeklyData)
        ];
    }

    /**
     * Génération de recommandations
     */
    public function generateRecommendations($currentData, $previousData)
    {
        $recommendations = [];

        // Recommandations basées sur les revenus
        if ($currentData['revenue'] < $previousData['revenue']) {
            $recommendations[] = [
                'type' => 'revenue',
                'priority' => 'high',
                'title' => 'Baisse des revenus détectée',
                'description' => 'Les revenus ont diminué par rapport à la période précédente.',
                'actions' => [
                    'Analyser les causes de la baisse',
                    'Lancer une campagne promotionnelle',
                    'Contacter les clients inactifs'
                ]
            ];
        }

        // Recommandations basées sur les coûts
        if ($currentData['expenses'] > $previousData['expenses'] * 1.2) {
            $recommendations[] = [
                'type' => 'expenses',
                'priority' => 'medium',
                'title' => 'Augmentation des coûts',
                'description' => 'Les dépenses ont augmenté de plus de 20%.',
                'actions' => [
                    'Réviser les contrats fournisseurs',
                    'Optimiser les processus d\'achat',
                    'Négocier de meilleurs tarifs'
                ]
            ];
        }

        // Recommandations basées sur la marge
        $currentMargin = $currentData['revenue'] > 0 ? 
            ($currentData['revenue'] - $currentData['expenses']) / $currentData['revenue'] * 100 : 0;
        $previousMargin = $previousData['revenue'] > 0 ? 
            ($previousData['revenue'] - $previousData['expenses']) / $previousData['revenue'] * 100 : 0;

        if ($currentMargin < $previousMargin - 5) {
            $recommendations[] = [
                'type' => 'margin',
                'priority' => 'high',
                'title' => 'Détérioration de la marge',
                'description' => 'La marge bénéficiaire a diminué de plus de 5%.',
                'actions' => [
                    'Revoir la stratégie de prix',
                    'Optimiser les coûts operationnels',
                    'Diversifier l\'offre produits'
                ]
            ];
        }

        return $recommendations;
    }

    /**
     * Calcul des données financières pour une période
     * Supporte maintenant la récupération complète des données
     */
    private function getFinancialData($startDate, $endDate, $filters = [])
    {
        $ordersQuery = Order::whereBetween('created_at', [$startDate, $endDate]);
        $arrivalsQuery = CbdArrival::whereBetween('created_at', [$startDate, $endDate]);

        if (isset($filters['orderStatuses'])) {
            $ordersQuery->whereIn('status', $filters['orderStatuses']);
        }

        if (isset($filters['arrivalStatuses'])) {
            $arrivalsQuery->whereIn('status', $filters['arrivalStatuses']);
        }

        // Vérifier si on doit récupérer toutes les données ou limiter
        $includeAll = isset($filters['pagination']['includeAll']) && $filters['pagination']['includeAll'];
        
        if ($includeAll) {
            // Récupérer TOUTES les données sans limitation
            $revenue = $ordersQuery->sum('total');
            $expenses = $arrivalsQuery->sum('amount');
            $orderCount = $ordersQuery->count();
            $arrivalCount = $arrivalsQuery->count();
            
            // Log pour debugging
            \Log::info("FinancialRecap: Récupération complète - {$orderCount} commandes, {$arrivalCount} arrivages");
        } else {
            // Limitation par défaut (pour compatibilité)
            $revenue = $ordersQuery->limit(50)->sum('total');
            $expenses = $arrivalsQuery->limit(50)->sum('amount');
            $orderCount = $ordersQuery->limit(50)->count();
            $arrivalCount = $arrivalsQuery->limit(50)->count();
            
            \Log::warning("FinancialRecap: Données limitées à 50 enregistrements");
        }

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'balance' => $revenue - $expenses,
            'orderCount' => $orderCount,
            'arrivalCount' => $arrivalCount,
            'isComplete' => $includeAll
        ];
    }

    /**
     * Calcul de la période précédente
     */
    private function getPreviousPeriod($startDate, $endDate)
    {
        $duration = $startDate->diffInDays($endDate);
        
        return [
            'start' => $startDate->copy()->subDays($duration + 1),
            'end' => $startDate->copy()->subDay()
        ];
    }

    /**
     * Comparaison des performances
     */
    private function comparePerformance($current, $previous)
    {
        $revenueChange = $this->calculatePercentageChange($previous['revenue'], $current['revenue']);
        $expenseChange = $this->calculatePercentageChange($previous['expenses'], $current['expenses']);
        $orderCountChange = $this->calculatePercentageChange($previous['orderCount'], $current['orderCount']);

        return [
            'revenue' => [
                'current' => $current['revenue'],
                'previous' => $previous['revenue'],
                'change' => $revenueChange,
                'trend' => $revenueChange > 0 ? 'up' : ($revenueChange < 0 ? 'down' : 'stable')
            ],
            'expenses' => [
                'current' => $current['expenses'],
                'previous' => $previous['expenses'],
                'change' => $expenseChange,
                'trend' => $expenseChange > 0 ? 'up' : ($expenseChange < 0 ? 'down' : 'stable')
            ],
            'orders' => [
                'current' => $current['orderCount'],
                'previous' => $previous['orderCount'],
                'change' => $orderCountChange,
                'trend' => $orderCountChange > 0 ? 'up' : ($orderCountChange < 0 ? 'down' : 'stable')
            ]
        ];
    }

    /**
     * Calcul du pourcentage de changement
     */
    private function calculatePercentageChange($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        
        return round((($newValue - $oldValue) / $oldValue) * 100, 2);
    }

    /**
     * Données hebdomadaires
     */
    private function getWeeklyData($startDate, $endDate, $filters)
    {
        $weeks = [];
        $current = $startDate->copy()->startOfWeek();

        while ($current <= $endDate) {
            $weekEnd = $current->copy()->endOfWeek();
            if ($weekEnd > $endDate) {
                $weekEnd = $endDate->copy();
            }

            $weekData = $this->getFinancialData($current, $weekEnd, $filters);
            $weekData['startDate'] = $current->toDateString();
            $weekData['endDate'] = $weekEnd->toDateString();
            $weekData['weekNumber'] = $current->weekOfYear;

            $weeks[] = $weekData;
            $current->addWeek();
        }

        return $weeks;
    }

    /**
     * Calcul des moyennes mobiles
     */
    private function calculateMovingAverages($weeklyData, $period = 4)
    {
        $movingAverages = [];
        
        for ($i = $period - 1; $i < count($weeklyData); $i++) {
            $sum = 0;
            for ($j = $i - $period + 1; $j <= $i; $j++) {
                $sum += $weeklyData[$j]['revenue'];
            }
            
            $movingAverages[] = [
                'week' => $weeklyData[$i]['weekNumber'],
                'average' => $sum / $period,
                'date' => $weeklyData[$i]['startDate']
            ];
        }

        return $movingAverages;
    }

    /**
     * Détection des patterns saisonniers
     */
    private function detectSeasonalPatterns($startDate, $endDate)
    {
        // Analyse simple basée sur les mois
        $monthlyData = Order::whereBetween('created_at', [$startDate->copy()->subYear(), $endDate])
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get();

        // Calcul des indices saisonniers
        $averageRevenue = $monthlyData->avg('revenue');
        
        return $monthlyData->map(function ($month) use ($averageRevenue) {
            return [
                'month' => $month->month,
                'revenue' => $month->revenue,
                'seasonalIndex' => $averageRevenue > 0 ? $month->revenue / $averageRevenue : 1,
                'trend' => $month->revenue > $averageRevenue ? 'above' : 'below'
            ];
        });
    }

    /**
     * Calcul du taux de croissance
     */
    private function calculateGrowthRate($weeklyData)
    {
        if (count($weeklyData) < 2) {
            return 0;
        }

        $firstWeek = $weeklyData[0]['revenue'];
        $lastWeek = end($weeklyData)['revenue'];

        return $this->calculatePercentageChange($firstWeek, $lastWeek);
    }

    /**
     * Génération de la clé de cache
     */
    private function generateCacheKey($filters)
    {
        return 'financial_recap_' . md5(json_encode($filters));
    }

    /**
     * Export des données en CSV
     */
    public function exportToCSV($filters)
    {
        $data = $this->generateFinancialReport($filters);
        
        $csv = "Date,Type,Montant,Statut,Description\n";
        
        // Export des commandes
        foreach ($data['summary']['orders'] as $order) {
            $csv .= sprintf(
                "%s,Commande,%.2f,%s,Commande #%s\n",
                $order['date'],
                $order['amount'],
                $order['status'],
                $order['id']
            );
        }
        
        // Export des arrivages
        foreach ($data['summary']['arrivals'] as $arrival) {
            $csv .= sprintf(
                "%s,Arrivage,%.2f,%s,Arrivage #%s\n",
                $arrival['date'],
                $arrival['amount'],
                $arrival['status'],
                $arrival['id']
            );
        }
        
        return $csv;
    }
}
