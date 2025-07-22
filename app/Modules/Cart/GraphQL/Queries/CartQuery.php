<?php

namespace App\Modules\Cart\GraphQL\Queries;

use App\Exceptions\CustomException;
use App\Modules\Cart\Services\CartService;
use Illuminate\Support\Facades\Gate;
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

        if (!Gate::allows('view', Cart::class)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour voir votre panier.');
        }

        try {
            $cart = (new CartService())->getOrCreateCart($user->id)->load('products');
            // Retourne directement le panier tel qu'attendu par le schéma GraphQL
            return $cart;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer le panier.');
        }
    }
}