<?php

namespace App\Modules\Order\Services;

use App\Models\Order;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function checkout($userId)
    {
        return DB::transaction(function () use ($userId) {
            $cart = Cart::where('user_id', $userId)->with('products')->firstOrFail();

            if ($cart->products->isEmpty()) {
                throw new \Exception("Le panier est vide.");
            }

            $total = 0;
            foreach ($cart->products as $product) {
                $total += $product->pivot->quantity * $product->price;
            }

            $order = Order::create([
                'user_id' => $userId,
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($cart->products as $product) {
                $order->products()->attach($product->id, [
                    'quantity' => $product->pivot->quantity,
                    'unit_price' => $product->price,
                ]);
            }

            // Vide le panier
            $cart->products()->detach();

            return $order->load('products');
        });
    }

    public function cancelOrder($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->status === 'completed') {
            throw new \Exception("Impossible d'annuler une commande terminée.");
        }

        $order->update(['status' => 'cancelled']);
    }
}