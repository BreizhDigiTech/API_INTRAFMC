<?php

namespace App\Modules\Cart\GraphQL\Queries;

use App\Exceptions\CustomException;
use App\Models\Cart;
use App\Helpers\AuthHelper;

class CartQuery
{
    /**
     * Récupère le panier de l'utilisateur connecté.
     *
     * @return array
     * @throws CustomException
     */
    public function myCart()
    {
        $user = AuthHelper::ensureAuthenticated();

        try {
            $cartItems = Cart::where('user_id', $user->id)
                ->with(['product', 'user'])
                ->get();
                
            return $cartItems;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer le panier.');
        }
    }

    /**
     * Calcule le total du panier de l'utilisateur.
     *
     * @return array
     * @throws CustomException
     */
    public function cartTotal()
    {
        $user = AuthHelper::ensureAuthenticated();

        try {
            $cartItems = Cart::where('user_id', $user->id)
                ->with('product')
                ->get();
                
            $total = 0;
            $itemCount = 0;
            
            foreach ($cartItems as $item) {
                $total += $item->product->price * $item->quantity;
                $itemCount += $item->quantity;
            }
            
            return [
                'total' => $total,
                'itemCount' => $itemCount
            ];
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de calculer le total du panier.');
        }
    }
}