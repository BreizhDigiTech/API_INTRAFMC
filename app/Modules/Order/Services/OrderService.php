<?php

namespace App\Modules\Order\Services;

use App\Models\Order;
use App\Models\Cart;
use App\Models\ProductCBD;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Créer une commande à partir du panier de l'utilisateur
     */
    public function checkout($userId)
    {
        return DB::transaction(function () use ($userId) {
            $cartItems = Cart::where('user_id', $userId)->with('product')->get();

            if ($cartItems->isEmpty()) {
                throw new CustomException(
                    'Panier vide',
                    'Le panier est vide. Impossible de créer une commande.'
                );
            }

            // Vérifier le stock et calculer le total
            $total = 0;
            $orderItems = [];

            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                
                // Vérifier le stock
                if ($product->stock < $cartItem->quantity) {
                    throw new CustomException(
                        'Stock insuffisant',
                        "Stock insuffisant pour le produit '{$product->name}'. Stock disponible: {$product->stock}, demandé: {$cartItem->quantity}"
                    );
                }

                $lineTotal = $cartItem->quantity * $product->price;
                $total += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $product->price,
                    'line_total' => $lineTotal
                ];
            }

            // Créer la commande
            $order = Order::create([
                'user_id' => $userId,
                'total' => $total,
                'status' => 'pending',
            ]);

            // Ajouter les produits à la commande et décrémenter le stock
            foreach ($orderItems as $item) {
                $order->products()->attach($item['product_id'], [
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);

                // Décrémenter le stock
                ProductCBD::where('id', $item['product_id'])
                    ->decrement('stock', $item['quantity']);
            }

            // Vider le panier
            Cart::where('user_id', $userId)->delete();

            Log::info('Commande créée avec succès', [
                'order_id' => $order->id,
                'user_id' => $userId,
                'total' => $total,
                'items_count' => count($orderItems)
            ]);

            return $order->load(['user', 'products']);
        });
    }

    /**
     * Annuler une commande
     */
    public function cancelOrder($orderId, $userId = null)
    {
        return DB::transaction(function () use ($orderId, $userId) {
            $query = Order::query();
            
            if ($userId) {
                $query->where('user_id', $userId);
            }
            
            $order = $query->findOrFail($orderId);

            if (in_array($order->status, ['delivered', 'shipped'])) {
                throw new CustomException(
                    'Annulation impossible',
                    'Impossible d\'annuler une commande déjà expédiée ou livrée.'
                );
            }

            if ($order->status === 'cancelled') {
                throw new CustomException(
                    'Commande déjà annulée',
                    'Cette commande est déjà annulée.'
                );
            }

            // Remettre le stock
            foreach ($order->products as $product) {
                ProductCBD::where('id', $product->id)
                    ->increment('stock', $product->pivot->quantity);
            }

            $order->update(['status' => 'cancelled']);

            Log::info('Commande annulée', [
                'order_id' => $orderId,
                'user_id' => $order->user_id
            ]);

            return $order;
        });
    }

    /**
     * Mettre à jour le statut d'une commande
     */
    public function updateOrderStatus($orderId, $newStatus)
    {
    // Statuts alignés avec la BDD (ENUM: pending, validated, cancelled)
    $validStatuses = ['pending', 'validated', 'cancelled'];
        
        if (!in_array($newStatus, $validStatuses)) {
            throw new CustomException(
                'Statut invalide',
                'Le statut fourni n\'est pas valide.'
            );
        }

        $order = Order::findOrFail($orderId);
        
        // Logique de transition de statut
        // Transitions simples compatibles avec l'ENUM
        $validTransitions = [
            'pending' => ['validated', 'cancelled'],
            'validated' => [],
            'cancelled' => [],
        ];

        if (!in_array($newStatus, $validTransitions[$order->status] ?? [])) {
            throw new CustomException(
                'Transition invalide',
                "Impossible de passer du statut '{$order->status}' à '{$newStatus}'."
            );
        }

        $oldStatus = $order->status;
        $order->update(['status' => $newStatus]);

        Log::info('Statut de commande mis à jour', [
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);

        return $order;
    }

    /**
     * Obtenir les commandes d'un utilisateur avec pagination
     */
    public function getUserOrders($userId, $perPage = 10)
    {
        return Order::where('user_id', $userId)
            ->with(['products' => function ($query) {
                $query->select(['cbd_products.id', 'cbd_products.name', 'cbd_products.price', 'cbd_products.images']);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtenir les détails complets d'une commande
     */
    public function getOrderDetails($orderId, $userId = null)
    {
        $query = Order::with([
            'user:id,name,email',
            'products' => function ($query) {
                $query->with('categories:id,name');
            }
        ]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $order = $query->findOrFail($orderId);

        return $order;
    }

    /**
     * Obtenir les statistiques de commandes
     */
    public function getOrderStats($filters = [])
    {
        $query = Order::query();

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return [
            'total_orders' => $query->count(),
            'total_amount' => $query->sum('total'),
            'average_order_value' => $query->avg('total'),
            'status_breakdown' => $query->groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->pluck('count', 'status')
                ->toArray()
        ];
    }
}
