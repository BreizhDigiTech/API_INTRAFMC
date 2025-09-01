<?php

namespace App\Modules\FinancialRecap\GraphQL\Queries;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;
use App\Models\ProductCBD;

class UserFinancialRecapQuery
{
    /**
     * Récapitulatif financier d'un utilisateur spécifique avec récupération complète
     */
    public function getUserRecap($root, array $args)
    {
        $userId = $args['userId'];
        $startDate = Carbon::parse($args['startDate']);
        $endDate = Carbon::parse($args['endDate']);

        $user = User::findOrFail($userId);
        
        // Récupération des commandes détaillées (TOUTES les commandes)
        $orders = $this->getUserOrders($userId, $startDate, $endDate, true);
        
        // Calcul du résumé
        $summary = $this->calculateUserSummary($orders, $user);
        
        // Timeline des commandes
        $timeline = $this->getUserTimeline($userId, $startDate, $endDate, true);

        \Log::info("UserFinancialRecap: Récupération complète pour utilisateur {$userId} - {$orders->count()} commandes");

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'createdAt' => $user->created_at,
                'isActive' => $user->is_active ?? true
            ],
            'orders' => $orders,
            'summary' => $summary,
            'timeline' => $timeline
        ];
    }

    /**
     * Récapitulatifs financiers de plusieurs utilisateurs avec récupération complète
     */
    public function getUsersRecaps($root, array $args)
    {
        $filters = $args['filters'];
        $startDate = Carbon::parse($filters['startDate']);
        $endDate = Carbon::parse($filters['endDate']);
        
        // Vérifier si on doit récupérer toutes les données
        $includeAll = isset($filters['pagination']['includeAll']) && $filters['pagination']['includeAll'];
        $limit = $includeAll ? null : ($args['first'] ?? 20);
        
        // Récupération des utilisateurs ayant passé des commandes
        $usersQuery = User::select('users.*')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->distinct();

        if (isset($filters['customerIds'])) {
            $usersQuery->whereIn('users.id', $filters['customerIds']);
        }

        // Appliquer la limitation si nécessaire
        if (!$includeAll && $limit) {
            $usersQuery->limit($limit);
        }

        $users = $usersQuery->get();
        
        \Log::info("UserFinancialRecaps: Récupération " . ($includeAll ? "complète" : "limitée à {$limit}") . " - {$users->count()} utilisateurs");

        return $users->map(function ($user) use ($startDate, $endDate, $filters, $includeAll) {
            // Récupération des commandes pour cet utilisateur (complète si demandé)
            $orders = $this->getUserOrders($user->id, $startDate, $endDate, $includeAll);
            
            // Calcul du résumé
            $summary = $this->calculateUserSummary($orders, $user);
            
            // Timeline simplifiée
            $timeline = $this->getUserTimelineSimple($user->id, $startDate, $endDate, $includeAll);

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'createdAt' => $user->created_at,
                    'isActive' => $user->is_active ?? true
                ],
                'totalSpent' => $summary['totalAmount'],
                'orderCount' => $summary['totalOrders'],
                'averageOrderAmount' => $summary['averageAmount'],
                'lastOrderDate' => $summary['lastOrderDate'],
                'favoriteProducts' => $summary['favoriteProducts']
            ];
        });
    }

    /**
     * Récupération des commandes détaillées d'un utilisateur avec support récupération complète
     */
    private function getUserOrders($userId, $startDate, $endDate, $includeAll = false)
    {
        $query = Order::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['products' => function ($productQuery) {
                $productQuery->select('cbd_products.id', 'cbd_products.name', 'cbd_products.price')
                    ->withPivot(['quantity', 'unit_price']);
            }])
            ->orderBy('created_at', 'desc');

        // Gestion de la récupération complète ou limitée
        if (!$includeAll) {
            // Limitation par défaut pour éviter les surcharges
            $query->limit(50);
            \Log::info("UserOrders: Limitation à 50 commandes pour utilisateur {$userId}");
        } else {
            \Log::info("UserOrders: Récupération complète pour utilisateur {$userId}");
        }

        $orders = $query->get();

        return $orders->map(function ($order) {
            $products = $order->products->map(function ($product) {
                return [
                    'productId' => $product->id,
                    'productName' => $product->name,
                    'quantity' => $product->pivot->quantity,
                    'unitPrice' => $product->pivot->unit_price,
                    'totalPrice' => $product->pivot->quantity * $product->pivot->unit_price
                ];
            });

            return [
                'id' => $order->id,
                'total' => $order->total,
                'status' => $order->status,
                'productCount' => $order->products->count(),
                'createdAt' => $order->created_at,
                'products' => $products
            ];
        });
    }

    /**
     * Calcul du résumé financier pour un utilisateur
     */
    private function calculateUserSummary($orders, $user)
    {
        $totalOrders = $orders->count();
        $totalAmount = $orders->sum('total');
        $averageAmount = $totalOrders > 0 ? $totalAmount / $totalOrders : 0;

        // Dates de première et dernière commande
        $firstOrderDate = $orders->min('createdAt');
        $lastOrderDate = $orders->max('createdAt');

        // Calcul de la fréquence (commandes par mois)
        $orderFrequency = 0;
        if ($firstOrderDate && $lastOrderDate) {
            $monthsDiff = Carbon::parse($firstOrderDate)->diffInMonths(Carbon::parse($lastOrderDate)) + 1;
            $orderFrequency = $totalOrders / $monthsDiff;
        }

        // Produits favoris
        $favoriteProducts = $this->calculateFavoriteProducts($orders);

        return [
            'totalOrders' => $totalOrders,
            'totalAmount' => $totalAmount,
            'averageAmount' => $averageAmount,
            'firstOrderDate' => $firstOrderDate,
            'lastOrderDate' => $lastOrderDate,
            'favoriteProducts' => $favoriteProducts,
            'orderFrequency' => round($orderFrequency, 2)
        ];
    }

    /**
     * Calcul des produits favoris
     */
    private function calculateFavoriteProducts($orders)
    {
        $productStats = [];

        foreach ($orders as $order) {
            foreach ($order['products'] as $product) {
                $productId = $product['productId'];
                
                if (!isset($productStats[$productId])) {
                    $productStats[$productId] = [
                        'productId' => $product['productId'],
                        'productName' => $product['productName'],
                        'orderCount' => 0,
                        'totalQuantity' => 0,
                        'totalAmount' => 0
                    ];
                }

                $productStats[$productId]['orderCount']++;
                $productStats[$productId]['totalQuantity'] += $product['quantity'];
                $productStats[$productId]['totalAmount'] += $product['totalPrice'];
            }
        }

        // Tri par nombre de commandes puis par montant total
        $favoriteProducts = collect($productStats)
            ->sortByDesc('orderCount')
            ->sortByDesc('totalAmount')
            ->take(5)
            ->values();

        return $favoriteProducts;
    }

    /**
     * Timeline détaillée des commandes d'un utilisateur avec support récupération complète
     */
    private function getUserTimeline($userId, $startDate, $endDate, $includeAll = false)
    {
        $query = Order::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['products' => function ($query) {
                $query->select('cbd_products.id', 'cbd_products.name')
                    ->withPivot(['quantity', 'unit_price']);
            }]);

        // Gestion de la récupération complète
        if (!$includeAll) {
            $query->limit(50);
        }

        $orders = $query->get();

        // Groupement par date
        $timeline = $orders->groupBy(function ($order) {
            return Carbon::parse($order->created_at)->toDateString();
        })->map(function ($dayOrders, $date) {
            $orderCount = $dayOrders->count();
            $totalAmount = $dayOrders->sum('total');

            $orderDetails = $dayOrders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'total' => $order->total,
                    'status' => $order->status,
                    'productCount' => $order->products->count(),
                    'createdAt' => $order->created_at,
                    'products' => $order->products->map(function ($product) {
                        return [
                            'productId' => $product->id,
                            'productName' => $product->name,
                            'quantity' => $product->pivot->quantity,
                            'unitPrice' => $product->pivot->unit_price,
                            'totalPrice' => $product->pivot->quantity * $product->pivot->unit_price
                        ];
                    })
                ];
            });

            return [
                'date' => $date,
                'orderCount' => $orderCount,
                'totalAmount' => $totalAmount,
                'orders' => $orderDetails
            ];
        })->values();

        return $timeline;
    }

    /**
     * Timeline simplifiée pour les listes avec support récupération complète
     */
    private function getUserTimelineSimple($userId, $startDate, $endDate, $includeAll = false)
    {
        $query = Order::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orderCount'),
                DB::raw('SUM(total) as totalAmount')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date');

        // Limitation si pas de récupération complète
        if (!$includeAll) {
            $query->limit(30); // Limiter à 30 jours par défaut
        }

        $ordersGrouped = $query->get();

        return $ordersGrouped->map(function ($group) {
            return [
                'date' => $group->date,
                'orderCount' => $group->orderCount,
                'totalAmount' => $group->totalAmount,
                'orders' => [] // Vide pour la version simplifiée
            ];
        });
    }

    /**
     * Tableau simple des utilisateurs avec total commandes et montants par période
     * Optimisé pour affichage de données générales
     */
    public function getUsersOrdersSummary($root, array $args)
    {
        $filters = $args['filters'];
        $startDate = Carbon::parse($filters['startDate']);
        $endDate = Carbon::parse($filters['endDate']);

        \Log::info("getUsersOrdersSummary appelé - Période: {$startDate->toDateString()} à {$endDate->toDateString()}");

        // Requête optimisée pour récupérer directement les statistiques par utilisateur
        $usersStats = DB::table('users')
            ->leftJoin('orders', function($join) use ($startDate, $endDate, $filters) {
                $join->on('users.id', '=', 'orders.user_id')
                     ->whereBetween('orders.created_at', [$startDate, $endDate]);
                
                // Appliquer les filtres de statut si fournis
                if (isset($filters['orderStatuses']) && !empty($filters['orderStatuses'])) {
                    $join->whereIn('orders.status', $filters['orderStatuses']);
                }
            })
            ->select([
                'users.id as user_id',
                'users.name as user_name',
                'users.email as user_email',
                'users.phone as user_phone',
                'users.created_at as user_created_at',
                DB::raw('COALESCE(COUNT(orders.id), 0) as total_orders'),
                DB::raw('COALESCE(SUM(orders.total), 0) as total_amount'),
                DB::raw('COALESCE(AVG(orders.total), 0) as average_order_amount'),
                DB::raw('MAX(orders.created_at) as last_order_date'),
                DB::raw('MIN(orders.created_at) as first_order_date')
            ])
            ->groupBy([
                'users.id', 
                'users.name', 
                'users.email', 
                'users.phone', 
                'users.created_at'
            ]);

        // Filtrer par utilisateurs spécifiques si demandé
        if (isset($filters['customerIds']) && !empty($filters['customerIds'])) {
            $usersStats->whereIn('users.id', $filters['customerIds']);
        }

        // Filtrer pour ne récupérer que les utilisateurs avec des commandes si demandé
        if (isset($filters['onlyActiveCustomers']) && $filters['onlyActiveCustomers']) {
            $usersStats->having('total_orders', '>', 0);
        }

        // Tri par défaut : par montant total décroissant
        $orderBy = $filters['orderBy'] ?? 'total_amount';
        $orderDirection = $filters['orderDirection'] ?? 'desc';
        $usersStats->orderBy($orderBy, $orderDirection);

        // TOUJOURS récupérer tous les utilisateurs (pas de limitation pour les stats)
        $results = $usersStats->get();

        \Log::info("getUsersOrdersSummary: {$results->count()} utilisateurs récupérés");

        // Formatage des résultats
        return $results->map(function ($userStat) use ($startDate, $endDate) {
            return [
                'userId' => (int) $userStat->user_id,
                'userName' => $userStat->user_name,
                'userEmail' => $userStat->user_email,
                'userPhone' => $userStat->user_phone,
                'userCreatedAt' => $userStat->user_created_at,
                
                // Statistiques de commandes pour la période
                'periodStats' => [
                    'startDate' => $startDate->toDateString(),
                    'endDate' => $endDate->toDateString(),
                    'totalOrders' => (int) $userStat->total_orders,
                    'totalAmount' => (float) $userStat->total_amount,
                    'averageOrderAmount' => (float) $userStat->average_order_amount,
                    'firstOrderDate' => $userStat->first_order_date,
                    'lastOrderDate' => $userStat->last_order_date
                ],
                
                // Indicateurs de performance
                'performance' => [
                    'isActiveCustomer' => $userStat->total_orders > 0,
                    'orderFrequency' => $this->calculateOrderFrequency($userStat->total_orders, $startDate, $endDate),
                    'customerSegment' => $this->determineCustomerSegment($userStat->total_amount, $userStat->total_orders)
                ]
            ];
        })->toArray();
    }

    /**
     * Calcul de la fréquence de commande (commandes par mois)
     */
    private function calculateOrderFrequency($totalOrders, $startDate, $endDate)
    {
        $diffInMonths = $startDate->diffInMonths($endDate);
        if ($diffInMonths === 0) $diffInMonths = 1; // Éviter division par zéro
        
        return round($totalOrders / $diffInMonths, 2);
    }

    /**
     * Détermination du segment client
     */
    private function determineCustomerSegment($totalAmount, $totalOrders)
    {
        if ($totalOrders === 0) return 'Inactif';
        if ($totalAmount > 1000) return 'Premium';
        if ($totalAmount > 500) return 'Régulier';
        if ($totalOrders > 5) return 'Fidèle';
        return 'Occasionnel';
    }
}
