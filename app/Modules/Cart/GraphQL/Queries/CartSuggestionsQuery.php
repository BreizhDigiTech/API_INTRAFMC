<?php

namespace App\Modules\Cart\GraphQL\Queries;

use App\Models\User;
use App\Models\Cart;
use App\Models\ProductCBD;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CartSuggestionsQuery
{
    /**
     * Obtenir des suggestions de produits basées sur le panier actuel
     */
    public function getCartSuggestions($root, array $args)
    {
        $user = Auth::user();
        $limit = $args['limit'] ?? 5;
        $type = $args['type'] ?? 'ALL';
        
        $currentCart = $user->cart()->with('product')->get();
        
        if ($currentCart->isEmpty()) {
            return $this->getGeneralSuggestions($user, $limit);
        }
        
        $suggestions = [];
        
        // Cross-sell : produits complémentaires
        if (in_array($type, ['ALL', 'CROSS_SELL'])) {
            $crossSell = $this->getCrossSellSuggestions($currentCart, $limit);
            $suggestions = array_merge($suggestions, $crossSell);
        }
        
        // Up-sell : produits de gamme supérieure
        if (in_array($type, ['ALL', 'UP_SELL'])) {
            $upSell = $this->getUpSellSuggestions($currentCart, $limit);
            $suggestions = array_merge($suggestions, $upSell);
        }
        
        // Produits fréquemment achetés ensemble
        if (in_array($type, ['ALL', 'FREQUENTLY_BOUGHT'])) {
            $frequentlyBought = $this->getFrequentlyBoughtTogetherSuggestions($currentCart, $limit);
            $suggestions = array_merge($suggestions, $frequentlyBought);
        }
        
        // Limiter le nombre de suggestions et éviter les doublons
        $uniqueSuggestions = collect($suggestions)
            ->unique('productId')
            ->take($limit)
            ->values()
            ->toArray();
            
        return $uniqueSuggestions;
    }
    
    /**
     * Obtenir des paniers sauvegardés d'un utilisateur
     */
    public function getSavedCarts($root, array $args)
    {
        $user = Auth::user();
        
        // Pour cette implémentation, nous utilisons une table séparée pour les paniers sauvegardés
        // En attendant, nous simulons avec des commandes "draft" ou "saved"
        return $this->getUserSavedCarts($user);
    }
    
    /**
     * Sauvegarder le panier actuel
     */
    public function saveCurrentCart($root, array $args)
    {
        $user = Auth::user();
        $name = $args['name'] ?? 'Panier du ' . now()->format('d/m/Y H:i');
        
        $currentCart = $user->cart()->with('product')->get();
        
        if ($currentCart->isEmpty()) {
            throw new \Exception('Aucun article dans le panier à sauvegarder');
        }
        
        // Créer une commande "draft" pour simuler la sauvegarde
        $savedCart = Order::create([
            'user_id' => $user->id,
            'total' => $currentCart->sum(function ($item) {
                return $item->quantity * $item->product->price;
            }),
            'status' => 'saved', // Statut spécial pour les paniers sauvegardés
            'notes' => $name,
        ]);
        
        // Ajouter les produits du panier
        foreach ($currentCart as $cartItem) {
            $savedCart->products()->attach($cartItem->product_id, [
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->product->price,
            ]);
        }
        
        return [
            'success' => true,
            'message' => 'Panier sauvegardé avec succès',
            'savedCartId' => $savedCart->id,
            'name' => $name,
        ];
    }
    
    /**
     * Récupérer les paniers abandonnés
     */
    public function getAbandonedCarts($root, array $args)
    {
        $user = Auth::user();
        $daysSinceAbandoned = $args['daysSinceAbandoned'] ?? 7;
        
        // Un panier est considéré comme abandonné si :
        // - Il y a des articles dans le panier
        // - Aucune commande n'a été passée depuis X jours
        // - L'utilisateur ne s'est pas connecté récemment
        
        $cartItems = $user->cart()->with('product')->get();
        
        if ($cartItems->isEmpty()) {
            return null;
        }
        
        $lastOrder = $user->orders()->latest()->first();
        $lastActivity = $user->updated_at; // Approximation de la dernière activité
        
        $isAbandoned = $lastActivity->lt(now()->subDays($daysSinceAbandoned));
        
        if (!$isAbandoned) {
            return null;
        }
        
        return [
            'cartId' => 'current',
            'abandonedDate' => $lastActivity->toDateString(),
            'daysSinceAbandoned' => $lastActivity->diffInDays(now()),
            'itemCount' => $cartItems->count(),
            'totalValue' => $cartItems->sum(function ($item) {
                return $item->quantity * $item->product->price;
            }),
            'items' => $cartItems->map(function ($item) {
                return [
                    'productId' => $item->product_id,
                    'productName' => $item->product->name,
                    'quantity' => $item->quantity,
                    'unitPrice' => $item->product->price,
                    'totalPrice' => $item->quantity * $item->product->price,
                ];
            })->toArray(),
        ];
    }
    
    /**
     * Obtenir des suggestions cross-sell
     */
    private function getCrossSellSuggestions($currentCart, int $limit): array
    {
        $currentProductIds = $currentCart->pluck('product_id')->toArray();
        $currentCategoryIds = $this->getCategoryIdsFromProducts($currentProductIds);
        
        // Trouver des produits dans des catégories complémentaires
        $suggestions = DB::table('cbd_products')
            ->join('category_product', 'cbd_products.id', '=', 'category_product.product_id')
            ->whereIn('category_product.category_id', $currentCategoryIds)
            ->whereNotIn('cbd_products.id', $currentProductIds)
            ->where('cbd_products.stock', '>', 0)
            ->select('cbd_products.id', 'cbd_products.name', 'cbd_products.price')
            ->limit($limit)
            ->get();
            
        return $suggestions->map(function ($product) {
            return [
                'productId' => $product->id,
                'productName' => $product->name,
                'price' => $product->price,
                'suggestionType' => 'CROSS_SELL',
                'reason' => 'Produit complémentaire à votre sélection',
                'score' => rand(70, 90) / 100, // Score simulé
            ];
        })->toArray();
    }
    
    /**
     * Obtenir des suggestions up-sell
     */
    private function getUpSellSuggestions($currentCart, int $limit): array
    {
        $suggestions = [];
        
        foreach ($currentCart as $cartItem) {
            $currentPrice = $cartItem->product->price;
            
            // Trouver des produits similaires mais plus chers (up-sell)
            $betterProducts = DB::table('cbd_products')
                ->join('category_product', 'cbd_products.id', '=', 'category_product.product_id')
                ->whereIn('category_product.category_id', function ($query) use ($cartItem) {
                    $query->select('category_id')
                        ->from('category_product')
                        ->where('product_id', $cartItem->product_id);
                })
                ->where('cbd_products.price', '>', $currentPrice)
                ->where('cbd_products.price', '<=', $currentPrice * 1.5) // Max 50% plus cher
                ->where('cbd_products.stock', '>', 0)
                ->where('cbd_products.id', '!=', $cartItem->product_id)
                ->select('cbd_products.id', 'cbd_products.name', 'cbd_products.price')
                ->limit(2)
                ->get();
                
            foreach ($betterProducts as $product) {
                $suggestions[] = [
                    'productId' => $product->id,
                    'productName' => $product->name,
                    'price' => $product->price,
                    'suggestionType' => 'UP_SELL',
                    'reason' => 'Version premium de ' . $cartItem->product->name,
                    'score' => rand(60, 85) / 100,
                    'replaces' => $cartItem->product_id,
                ];
            }
        }
        
        return array_slice($suggestions, 0, $limit);
    }
    
    /**
     * Obtenir des suggestions "fréquemment achetés ensemble"
     */
    private function getFrequentlyBoughtTogetherSuggestions($currentCart, int $limit): array
    {
        $currentProductIds = $currentCart->pluck('product_id')->toArray();
        
        // Trouver les produits souvent achetés avec les produits actuels
        $frequentlyBought = DB::table('order_product as op1')
            ->join('order_product as op2', 'op1.order_id', '=', 'op2.order_id')
            ->join('cbd_products', 'op2.product_id', '=', 'cbd_products.id')
            ->whereIn('op1.product_id', $currentProductIds)
            ->whereNotIn('op2.product_id', $currentProductIds)
            ->where('cbd_products.stock', '>', 0)
            ->select(
                'op2.product_id',
                'cbd_products.name',
                'cbd_products.price',
                DB::raw('COUNT(*) as frequency')
            )
            ->groupBy('op2.product_id', 'cbd_products.name', 'cbd_products.price')
            ->orderByDesc('frequency')
            ->limit($limit)
            ->get();
            
        return $frequentlyBought->map(function ($product) {
            return [
                'productId' => $product->product_id,
                'productName' => $product->name,
                'price' => $product->price,
                'suggestionType' => 'FREQUENTLY_BOUGHT',
                'reason' => 'Souvent acheté avec vos produits',
                'score' => min(0.95, $product->frequency / 10), // Score basé sur la fréquence
                'frequency' => $product->frequency,
            ];
        })->toArray();
    }
    
    /**
     * Obtenir des suggestions générales pour un panier vide
     */
    private function getGeneralSuggestions(User $user, int $limit): array
    {
        // Basé sur l'historique de l'utilisateur
        $userHistory = $user->orders()
            ->join('order_product', 'orders.id', '=', 'order_product.order_id')
            ->join('cbd_products', 'order_product.product_id', '=', 'cbd_products.id')
            ->where('orders.status', '!=', 'cancelled')
            ->select('cbd_products.id', 'cbd_products.name', 'cbd_products.price')
            ->distinct()
            ->limit($limit)
            ->get();
            
        if ($userHistory->isNotEmpty()) {
            return $userHistory->map(function ($product) {
                return [
                    'productId' => $product->id,
                    'productName' => $product->name,
                    'price' => $product->price,
                    'suggestionType' => 'REPURCHASE',
                    'reason' => 'Basé sur vos achats précédents',
                    'score' => rand(50, 80) / 100,
                ];
            })->toArray();
        }
        
        // Produits populaires pour les nouveaux utilisateurs
        return $this->getPopularProducts($limit);
    }
    
    /**
     * Obtenir les produits populaires
     */
    private function getPopularProducts(int $limit): array
    {
        $popular = DB::table('order_product')
            ->join('cbd_products', 'order_product.product_id', '=', 'cbd_products.id')
            ->join('orders', 'order_product.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.created_at', '>=', now()->subMonth())
            ->where('cbd_products.stock', '>', 0)
            ->select(
                'cbd_products.id',
                'cbd_products.name',
                'cbd_products.price',
                DB::raw('COUNT(*) as popularity')
            )
            ->groupBy('cbd_products.id', 'cbd_products.name', 'cbd_products.price')
            ->orderByDesc('popularity')
            ->limit($limit)
            ->get();
            
        return $popular->map(function ($product) {
            return [
                'productId' => $product->id,
                'productName' => $product->name,
                'price' => $product->price,
                'suggestionType' => 'POPULAR',
                'reason' => 'Produit populaire ce mois-ci',
                'score' => rand(60, 90) / 100,
                'popularity' => $product->popularity,
            ];
        })->toArray();
    }
    
    /**
     * Obtenir les IDs de catégories depuis des produits
     */
    private function getCategoryIdsFromProducts(array $productIds): array
    {
        return DB::table('category_product')
            ->whereIn('product_id', $productIds)
            ->pluck('category_id')
            ->unique()
            ->toArray();
    }
    
    /**
     * Obtenir les paniers sauvegardés d'un utilisateur
     */
    private function getUserSavedCarts(User $user): array
    {
        $savedCarts = Order::where('user_id', $user->id)
            ->where('status', 'saved')
            ->with('products')
            ->orderByDesc('created_at')
            ->get();
            
        return $savedCarts->map(function ($cart) {
            return [
                'savedCartId' => $cart->id,
                'name' => $cart->notes ?: 'Panier sauvegardé',
                'createdAt' => $cart->created_at->toDateString(),
                'itemCount' => $cart->products->count(),
                'totalValue' => $cart->total,
                'items' => $cart->products->map(function ($product) {
                    return [
                        'productId' => $product->id,
                        'productName' => $product->name,
                        'quantity' => $product->pivot->quantity,
                        'unitPrice' => $product->pivot->unit_price,
                        'totalPrice' => $product->pivot->quantity * $product->pivot->unit_price,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }
}
