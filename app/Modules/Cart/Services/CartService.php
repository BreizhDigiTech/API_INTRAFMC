<?php


namespace App\Modules\Cart\Services;

use App\Models\Cart;
use App\Models\ProductCBD;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;

class CartService
{
    public function getOrCreateCart($userId)
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function addProduct($userId, $productId, $quantity)
    {
        return DB::transaction(function () use ($userId, $productId, $quantity) {
            $cart = $this->getOrCreateCart($userId);
            $product = ProductCBD::findOrFail($productId);

            if ($product->stock < $quantity) {
                throw new CustomException("Stock insuffisant pour ce produit.");
            }

            // Décrémente le stock
            $product->decrement('stock', $quantity);

            // Ajoute ou met à jour la quantité dans le panier
            $cart->products()->syncWithoutDetaching([
                $productId => [
                    'quantity' => DB::raw('quantity + ' . $quantity)
                ]
            ]);

            // Correction si le produit n'était pas déjà dans le panier
            $pivot = $cart->products()->where('product_id', $productId)->first();
            if ($pivot->pivot->quantity < $quantity) {
                $cart->products()->updateExistingPivot($productId, ['quantity' => $quantity]);
            }

            return $cart->load('products');
        });
    }

    public function removeProduct($userId, $productId)
    {
        return DB::transaction(function () use ($userId, $productId) {
            $cart = $this->getOrCreateCart($userId);
            $pivot = $cart->products()->where('product_id', $productId)->first();

            if ($pivot) {
                // Rendre le stock
                $pivot->increment('stock', $pivot->pivot->quantity);
                $cart->products()->detach($productId);
            }

            return $cart->load('products');
        });
    }

    public function clearCart($userId)
    {
        $cart = $this->getOrCreateCart($userId);
        foreach ($cart->products as $product) {
            $product->increment('stock', $product->pivot->quantity);
        }
        $cart->products()->detach();
        return $cart;
    }

    public function updateProductQuantity($userId, $productId, $quantity)
    {
        return \DB::transaction(function () use ($userId, $productId, $quantity) {
            $cart = $this->getOrCreateCart($userId);
            $product = \App\Models\ProductCBD::findOrFail($productId);

            $pivot = $cart->products()->where('product_id', $productId)->first();
            if (!$pivot) {
                throw new \Exception("Produit non présent dans le panier.");
            }

            $currentQuantity = $pivot->pivot->quantity;
            $diff = $quantity - $currentQuantity;

            if ($diff > 0 && $product->stock < $diff) {
                throw new \Exception("Stock insuffisant pour ce produit.");
            }

            // Ajuste le stock
            $product->decrement('stock', $diff);

            // Met à jour la quantité dans le panier
            $cart->products()->updateExistingPivot($productId, ['quantity' => $quantity]);

            // Si quantité = 0, retire le produit du panier
            if ($quantity <= 0) {
                $cart->products()->detach($productId);
            }

            return $cart->load('products');
        });
    }
}