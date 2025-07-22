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
            $cartItems = Cart::where('user_id', $userId)->with('product')->get();

            if ($cartItems->isEmpty()) {
                throw new \Exception("Le panier est vide.");
            }

            $total = 0;
            foreach ($cartItems as $cartItem) {
                $total += $cartItem->quantity * $cartItem->product->price;
            }

            $order = Order::create([
                'user_id' => $userId,
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($cartItems as $cartItem) {
                $order->products()->attach($cartItem->product->id, [
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->product->price,
                ]);
            }

            // Vide le panier
            Cart::where('user_id', $userId)->delete();

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