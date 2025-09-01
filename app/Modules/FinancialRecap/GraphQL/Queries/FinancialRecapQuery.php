<?php

namespace App\Modules\FinancialRecap\GraphQL\Queries;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\CbdArrival;
use App\Models\User;

class FinancialRecapQuery
{
    /**
     * Récapitulatif financier général avec support de récupération complète
     */
    public function getSummary($root, array $args)
    {
        try {
            $filters = $args['filters'];
            
            // Validation des filtres obligatoires
            if (!isset($filters['startDate']) || !isset($filters['endDate'])) {
                throw new \InvalidArgumentException('Les dates de début et fin sont obligatoires');
            }
            
            // Validation et parsing des dates
            $startDate = Carbon::parse($filters['startDate']);
            $endDate = Carbon::parse($filters['endDate']);
            
            if ($startDate > $endDate) {
                throw new \InvalidArgumentException('La date de début doit être antérieure à la date de fin');
            }
            
            // FORCE TOUJOURS la récupération complète pour les calculs généraux
            // Les données financières nécessitent TOUTES les commandes pour être exactes
            $filters['pagination'] = [
                'includeAll' => true,  // TOUJOURS récupérer toutes les données
                'limit' => null        // JAMAIS de limitation
            ];
            
            // Log pour debug
            \Log::info('FinancialRecap getSummary - RÉCUPÉRATION COMPLÈTE FORCÉE', [
                'startDate' => $startDate->toDateString(),
                'endDate' => $endDate->toDateString(),
                'includeAll' => $filters['pagination']['includeAll'] ?? false,
                'orderStatuses' => $filters['orderStatuses'] ?? [],
                'arrivalStatuses' => $filters['arrivalStatuses'] ?? []
            ]);

            // Récupération des données de commandes avec support complète
            $ordersData = $this->getOrdersRecap($startDate, $endDate, $filters);
            
            // Récupération des données d'arrivages avec support complète
            $arrivalsData = $this->getArrivalsRecap($startDate, $endDate, $filters);
            
            // Calcul du bilan
            $balance = $this->calculateBalance($ordersData, $arrivalsData);
            
            // Informations sur la période
            $period = $this->getPeriodInfo($startDate, $endDate);
            
            // Informations de complétude
            $dataCompleteness = $this->calculateDataCompleteness($ordersData, $arrivalsData, $filters);

            \Log::info('FinancialRecap getSummary success', [
                'orderCount' => $ordersData['totalCount'],
                'arrivalCount' => $arrivalsData['totalCount'],
                'isComplete' => $dataCompleteness['isComplete']
            ]);

            return [
                'orders' => $ordersData,
                'arrivals' => $arrivalsData,
                'balance' => $balance,
                'period' => $period,
                'dataCompleteness' => $dataCompleteness
            ];
            
        } catch (\Exception $e) {
            \Log::error('Erreur dans FinancialRecapQuery::getSummary', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $args['filters'] ?? 'Non défini'
            ]);
            
            throw new \GraphQL\Error\Error(
                'Erreur lors de la récupération des données financières: ' . $e->getMessage()
            );
        }
    }

    /**
     * Timeline financière
     */
    public function getTimeline($root, array $args)
    {
        $filters = $args['filters'];
        $startDate = Carbon::parse($filters['startDate']);
        $endDate = Carbon::parse($filters['endDate']);
        $groupBy = $filters['groupBy'] ?? 'MONTH';

        $periods = $this->generateTimePeriods($startDate, $endDate, $groupBy);
        $periodsData = [];

        foreach ($periods as $period) {
            $periodStart = Carbon::parse($period['start']);
            $periodEnd = Carbon::parse($period['end']);
            
            $periodOrdersData = $this->getOrdersForPeriod($periodStart, $periodEnd, $filters);
            $periodArrivalsData = $this->getArrivalsForPeriod($periodStart, $periodEnd, $filters);
            
            $periodsData[] = [
                'date' => $period['start'],
                'label' => $period['label'],
                'orders' => $periodOrdersData,
                'arrivals' => $periodArrivalsData,
                'balance' => $periodOrdersData['amount'] - $periodArrivalsData['amount']
            ];
        }

        $totals = $this->calculateTotals($periodsData);
        $trends = $this->calculateTrends($periodsData);

        return [
            'periods' => $periodsData,
            'totals' => $totals,
            'trends' => $trends
        ];
    }

    /**
     * Options pour les filtres
     */
    public function getFilterOptions()
    {
        return [
            'orderStatuses' => $this->getOrderStatusOptions(),
            'arrivalStatuses' => $this->getArrivalStatusOptions(),
            'customers' => $this->getCustomerOptions(),
            'suppliers' => $this->getSupplierOptions(),
            'datePresets' => $this->getDatePresetOptions()
        ];
    }

    /**
     * Récapitulatif des commandes avec support de récupération complète
     */
    private function getOrdersRecap($startDate, $endDate, $filters)
    {
        $query = Order::whereBetween('created_at', [$startDate, $endDate]);

        // Appliquer les filtres de statut
        if (isset($filters['orderStatuses']) && !empty($filters['orderStatuses'])) {
            $query->whereIn('status', $filters['orderStatuses']);
        }

        // Appliquer les filtres de clients
        if (isset($filters['customerIds']) && !empty($filters['customerIds'])) {
            $query->whereIn('user_id', $filters['customerIds']);
        }

        // RÉCUPÉRATION COMPLÈTE TOUJOURS - Pas de limitation pour les calculs financiers
        $orders = $query->get();  // TOUTES les commandes récupérées
        $totalAmount = $orders->sum('total');
        $totalCount = $orders->count();
        $averageAmount = $totalCount > 0 ? $totalAmount / $totalCount : 0;
        
        \Log::info("FinancialRecap Orders: Récupération complète FORCÉE - {$totalCount} commandes récupérées pour calculs exacts");

        // Calcul des statistiques par statut
        $byStatus = $this->calculateOrdersByStatus($orders);
        
        // Top clients - toujours 20 car récupération complète
        $topCustomers = $this->getTopCustomers($orders, 20);

        return [
            'totalCount' => $totalCount,
            'totalAmount' => $totalAmount,
            'averageAmount' => $averageAmount,
            'byStatus' => $byStatus,
            'topCustomers' => $topCustomers,
            'isComplete' => true  // TOUJOURS true car récupération complète forcée
        ];
    }

    /**
     * Récapitulatif des arrivages avec support de récupération complète
     */
    private function getArrivalsRecap($startDate, $endDate, $filters)
    {
        $query = CbdArrival::whereBetween('created_at', [$startDate, $endDate]);

        // Appliquer les filtres de statut
        if (isset($filters['arrivalStatuses']) && !empty($filters['arrivalStatuses'])) {
            $query->whereIn('status', $filters['arrivalStatuses']);
        }

        // Appliquer les filtres de fournisseurs
        if (isset($filters['supplierIds']) && !empty($filters['supplierIds'])) {
            $query->whereIn('supplier_id', $filters['supplierIds']);
        }

        // RÉCUPÉRATION COMPLÈTE TOUJOURS - Pas de limitation pour les calculs financiers
        $arrivals = $query->get();  // TOUS les arrivages récupérés
        $totalAmount = $arrivals->sum('amount');
        $totalCount = $arrivals->count();
        $averageAmount = $totalCount > 0 ? $totalAmount / $totalCount : 0;
        
        \Log::info("FinancialRecap Arrivals: Récupération complète FORCÉE - {$totalCount} arrivages récupérés pour calculs exacts");

        // Calcul des statistiques par statut
        $byStatus = $this->calculateArrivalsByStatus($arrivals);
        
        // Top fournisseurs - toujours 20 car récupération complète
        $topSuppliers = $this->getTopSuppliers($arrivals, 20);

        return [
            'totalCount' => $totalCount,
            'totalAmount' => $totalAmount,
            'averageAmount' => $averageAmount,
            'byStatus' => $byStatus,
            'topSuppliers' => $topSuppliers,
            'isComplete' => true  // TOUJOURS true car récupération complète forcée
        ];
    }

    /**
     * Calcul des statistiques des commandes par statut
     */
    private function calculateOrdersByStatus($orders)
    {
        $totalCount = $orders->count();
        
        return $orders->groupBy('status')->map(function ($statusOrders, $status) use ($totalCount) {
            $count = $statusOrders->count();
            $amount = $statusOrders->sum('total');
            
            return [
                'status' => $status,
                'count' => $count,
                'amount' => $amount,
                'percentage' => $totalCount > 0 ? ($count / $totalCount) * 100 : 0
            ];
        })->values()->toArray();
    }

    /**
     * Calcul des statistiques des arrivages par statut
     */
    private function calculateArrivalsByStatus($arrivals)
    {
        $totalCount = $arrivals->count();
        
        return $arrivals->groupBy('status')->map(function ($statusArrivals, $status) use ($totalCount) {
            $count = $statusArrivals->count();
            $amount = $statusArrivals->sum('amount');
            
            return [
                'status' => $status,
                'count' => $count,
                'amount' => $amount,
                'percentage' => $totalCount > 0 ? ($count / $totalCount) * 100 : 0
            ];
        })->values()->toArray();
    }

    /**
     * Top clients avec support de récupération complète
     */
    private function getTopCustomers($orders, $limit = 10)
    {
        // Grouper les commandes par utilisateur
        $customerStats = $orders->groupBy('user_id')->map(function ($userOrders, $userId) {
            $user = User::find($userId);
            if (!$user) return null;
            
            $totalAmount = $userOrders->sum('total');
            $orderCount = $userOrders->count();
            $averageAmount = $orderCount > 0 ? $totalAmount / $orderCount : 0;
            $lastOrderDate = $userOrders->max('created_at');
            
            return [
                'userId' => $userId,
                'userName' => $user->name,
                'userEmail' => $user->email,
                'orderCount' => $orderCount,
                'totalAmount' => $totalAmount,
                'averageAmount' => $averageAmount,
                'lastOrderDate' => $lastOrderDate
            ];
        })->filter()->sortByDesc('totalAmount')->take($limit);

        return $customerStats->values()->toArray();
    }

    /**
     * Top fournisseurs avec support de récupération complète
     */
    private function getTopSuppliers($arrivals, $limit = 10)
    {
        // Grouper les arrivages par fournisseur si disponible
        // Pour l'instant, retournons un tableau vide car le modèle n'a pas de relation fournisseur
        return [];
    }

    /**
     * Calcul du bilan financier
     */
    private function calculateBalance($ordersData, $arrivalsData)
    {
        $revenue = $ordersData['totalAmount'];
        $expenses = $arrivalsData['totalAmount'];
        $balance = $revenue - $expenses;
        $profitMargin = $revenue > 0 ? ($balance / $revenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'balance' => $balance,
            'profitMargin' => round($profitMargin, 2)
        ];
    }

    /**
     * Calcul des informations de complétude des données
     * TOUJOURS complètes car récupération forcée
     */
    private function calculateDataCompleteness($ordersData, $arrivalsData, $filters)
    {
        $ordersCount = $ordersData['totalCount'];
        $arrivalsCount = $arrivalsData['totalCount'];
        
        // Données TOUJOURS complètes car récupération forcée
        $isComplete = true;
        $possibleLimitation = false;
        $recommendation = "Toutes les données ont été récupérées automatiquement pour des calculs précis.";

        return [
            'isComplete' => $isComplete,
            'ordersRetrieved' => $ordersCount,
            'arrivalsRetrieved' => $arrivalsCount,
            'possibleLimitation' => $possibleLimitation,
            'recommendation' => $recommendation
        ];
    }

    /**
     * Calcul du bilan financier (méthode existante)
     */
    private function calculateBalanceOld($ordersData, $arrivalsData)
    {
        $revenue = $ordersData['totalAmount'];
        $expenses = $arrivalsData['totalAmount'];
        $balance = $revenue - $expenses;
        $profitMargin = $revenue > 0 ? ($balance / $revenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'balance' => $balance,
            'profitMargin' => $profitMargin
        ];
    }

    /**
     * Informations sur la période
     */
    private function getPeriodInfo($startDate, $endDate)
    {
        $duration = $startDate->diffInDays($endDate) + 1;
        
        // Génération du label selon la durée
        if ($duration <= 31) {
            $label = $startDate->format('M Y');
        } elseif ($duration <= 93) {
            $label = 'Q' . $startDate->quarter . ' ' . $startDate->year;
        } else {
            $label = $startDate->year . ' - ' . $endDate->year;
        }

        return [
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'duration' => $duration,
            'label' => $label
        ];
    }

    /**
     * Génération des périodes pour la timeline
     */
    private function generateTimePeriods($startDate, $endDate, $groupBy)
    {
        $periods = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $periodEnd = match($groupBy) {
                'DAY' => $current->copy(),
                'WEEK' => $current->copy()->endOfWeek(),
                'MONTH' => $current->copy()->endOfMonth(),
                'QUARTER' => $current->copy()->endOfQuarter(),
                'YEAR' => $current->copy()->endOfYear(),
                default => $current->copy()->endOfMonth()
            };

            $periods[] = [
                'start' => $current->toDateString(),
                'end' => min($periodEnd, $endDate)->toDateString(),
                'label' => $this->getPeriodLabel($current, $groupBy)
            ];

            $current = match($groupBy) {
                'DAY' => $current->addDay(),
                'WEEK' => $current->addWeek(),
                'MONTH' => $current->addMonth(),
                'QUARTER' => $current->addQuarter(),
                'YEAR' => $current->addYear(),
                default => $current->addMonth()
            };
        }

        return $periods;
    }

    /**
     * Label pour les périodes
     */
    private function getPeriodLabel($date, $groupBy)
    {
        return match($groupBy) {
            'DAY' => $date->format('d/m'),
            'WEEK' => 'S' . $date->weekOfYear,
            'MONTH' => $date->format('M Y'),
            'QUARTER' => 'Q' . $date->quarter . ' ' . $date->year,
            'YEAR' => $date->year,
            default => $date->format('M Y')
        };
    }

    /**
     * Données de commandes pour une période
     */
    private function getOrdersForPeriod($start, $end, $filters)
    {
        $query = Order::whereBetween('created_at', [$start, $end]);
        
        if (isset($filters['orderStatuses'])) {
            $query->whereIn('status', $filters['orderStatuses']);
        }

        $orders = $query->get();
        $count = $orders->count();
        $amount = $orders->sum('total');

        return [
            'count' => $count,
            'amount' => $amount,
            'averageAmount' => $count > 0 ? $amount / $count : 0
        ];
    }

    /**
     * Données d'arrivages pour une période
     */
    private function getArrivalsForPeriod($start, $end, $filters)
    {
        $query = CbdArrival::whereBetween('created_at', [$start, $end]);
        
        if (isset($filters['arrivalStatuses'])) {
            $query->whereIn('status', $filters['arrivalStatuses']);
        }

        $arrivals = $query->get();
        $count = $arrivals->count();
        $amount = $arrivals->sum('amount');

        return [
            'count' => $count,
            'amount' => $amount,
            'averageAmount' => $count > 0 ? $amount / $count : 0
        ];
    }

    /**
     * Calcul des totaux
     */
    private function calculateTotals($periodsData)
    {
        $totalRevenue = 0;
        $totalExpenses = 0;
        $totalOrders = 0;
        $totalArrivals = 0;
        
        foreach ($periodsData as $period) {
            $totalRevenue += $period['orders']['amount'] ?? 0;
            $totalExpenses += $period['arrivals']['amount'] ?? 0;
            $totalOrders += $period['orders']['count'] ?? 0;
            $totalArrivals += $period['arrivals']['count'] ?? 0;
        }

        return [
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'totalBalance' => $totalRevenue - $totalExpenses,
            'totalOrders' => $totalOrders,
            'totalArrivals' => $totalArrivals
        ];
    }

    /**
     * Calcul des tendances
     */
    private function calculateTrends($periodsData)
    {
        if (count($periodsData) < 2) {
            return [
                'revenueGrowth' => 0,
                'expenseGrowth' => 0,
                'orderGrowth' => 0,
                'arrivalGrowth' => 0,
                'profitTrend' => 'STABLE'
            ];
        }

        $firstPeriod = $periodsData[0];
        $lastPeriod = end($periodsData);

        $revenueGrowth = $this->calculateGrowthRate(
            $firstPeriod['orders']['amount'], 
            $lastPeriod['orders']['amount']
        );

        $expenseGrowth = $this->calculateGrowthRate(
            $firstPeriod['arrivals']['amount'], 
            $lastPeriod['arrivals']['amount']
        );

        $orderGrowth = $this->calculateGrowthRate(
            $firstPeriod['orders']['count'], 
            $lastPeriod['orders']['count']
        );

        $arrivalGrowth = $this->calculateGrowthRate(
            $firstPeriod['arrivals']['count'], 
            $lastPeriod['arrivals']['count']
        );

        $profitTrend = $firstPeriod['balance'] < $lastPeriod['balance'] ? 'UP' : 
                      ($firstPeriod['balance'] > $lastPeriod['balance'] ? 'DOWN' : 'STABLE');

        return [
            'revenueGrowth' => $revenueGrowth,
            'expenseGrowth' => $expenseGrowth,
            'orderGrowth' => $orderGrowth,
            'arrivalGrowth' => $arrivalGrowth,
            'profitTrend' => $profitTrend
        ];
    }

    /**
     * Calcul du taux de croissance
     */
    private function calculateGrowthRate($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        
        return (($newValue - $oldValue) / $oldValue) * 100;
    }

    /**
     * Options de statuts de commandes
     */
    private function getOrderStatusOptions()
    {
        $statuses = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return $statuses->map(function ($status) {
            return [
                'value' => $status->status,
                'label' => ucfirst($status->status),
                'count' => $status->count
            ];
        });
    }

    /**
     * Options de statuts d'arrivages
     */
    private function getArrivalStatusOptions()
    {
        $statuses = CbdArrival::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return $statuses->map(function ($status) {
            return [
                'value' => $status->status,
                'label' => ucfirst($status->status),
                'count' => $status->count
            ];
        });
    }

    /**
     * Options de clients
     */
    private function getCustomerOptions()
    {
        $customers = User::select('users.id', 'users.name', 'users.email', DB::raw('COUNT(orders.id) as orderCount'))
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('orderCount', 'DESC')
            ->limit(50)
            ->get();

        return $customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'orderCount' => $customer->orderCount
            ];
        });
    }

    /**
     * Options de fournisseurs
     */
    private function getSupplierOptions()
    {
        // À implémenter selon votre modèle de fournisseurs
        return [];
    }

    /**
     * Présets de dates
     */
    private function getDatePresetOptions()
    {
        $now = Carbon::now();
        
        return [
            [
                'value' => 'today',
                'label' => "Aujourd'hui",
                'startDate' => $now->toDateString(),
                'endDate' => $now->toDateString()
            ],
            [
                'value' => 'week',
                'label' => 'Cette semaine',
                'startDate' => $now->startOfWeek()->toDateString(),
                'endDate' => $now->endOfWeek()->toDateString()
            ],
            [
                'value' => 'month',
                'label' => 'Ce mois',
                'startDate' => $now->startOfMonth()->toDateString(),
                'endDate' => $now->endOfMonth()->toDateString()
            ],
            [
                'value' => 'quarter',
                'label' => 'Ce trimestre',
                'startDate' => $now->startOfQuarter()->toDateString(),
                'endDate' => $now->endOfQuarter()->toDateString()
            ],
            [
                'value' => 'year',
                'label' => 'Cette année',
                'startDate' => $now->startOfYear()->toDateString(),
                'endDate' => $now->endOfYear()->toDateString()
            ],
            [
                'value' => 'last30',
                'label' => '30 derniers jours',
                'startDate' => $now->subDays(30)->toDateString(),
                'endDate' => Carbon::now()->toDateString()
            ]
        ];
    }
}
