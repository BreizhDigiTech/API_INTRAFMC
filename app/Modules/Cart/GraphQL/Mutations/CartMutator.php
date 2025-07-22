<?php

namespace App\Modules\Cart\GraphQL\Mutations;

use App\Models\Cart;
use App\Models\ProductCBD;
use App\Helpers\AuthHelper;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartMutator
{
    /**
     * Ajouter un produit au panier
     */
    public function addToCart($rootValue, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $input = $args['input'];
        
        // Validation
        if ($input['quantity'] <= 0) {
            throw new CustomException('Quantité invalide', 'La quantité doit être positive.');
        }
        
        try {
            // Vérifier que le produit existe et a suffisamment de stock
            $product = ProductCBD::findOrFail($input['product_id']);
            
            if ($product->stock < $input['quantity']) {
                throw new CustomException('Stock insuffisant', 'La quantité demandée dépasse le stock disponible.');
            }
            
            // Vérifier s'il y a déjà une entrée pour ce produit dans le panier
            $existingCartItem = Cart::where('user_id', $user->id)
                ->where('product_id', $input['product_id'])
                ->first();
                
            if ($existingCartItem) {
                // Mettre à jour la quantité
                $existingCartItem->quantity += $input['quantity'];
                $existingCartItem->save();
                return $existingCartItem->load(['product', 'user']);
            } else {
                // Créer une nouvelle entrée
                $cartItem = Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $input['product_id'],
                    'quantity' => $input['quantity']
                ]);
                
                return $cartItem->load(['product', 'user']);
            }
        } catch (CustomException $e) {
            throw $e; // Relancer l'exception CustomException
        } catch (ModelNotFoundException $e) {
            throw new CustomException('Produit introuvable', 'Le produit spécifié n\'existe pas.');
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible d\'ajouter le produit au panier.');
        }
    }

    /**
     * Mettre à jour la quantité d'un produit dans le panier
     */
    public function updateCartItem($rootValue, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $input = $args['input'];
        
        try {
            $cartItem = Cart::where('user_id', $user->id)
                ->where('id', $args['id'])
                ->firstOrFail();
                
            // Vérifier le stock
            $product = ProductCBD::findOrFail($cartItem->product_id);
            if ($product->stock < $input['quantity']) {
                throw new CustomException('Stock insuffisant', 'La quantité demandée dépasse le stock disponible.');
            }
            
            $cartItem->quantity = $input['quantity'];
            $cartItem->save();
            
            return $cartItem->load(['product', 'user']);
        } catch (ModelNotFoundException $e) {
            throw new CustomException('Produit introuvable', 'Le produit n\'est pas dans le panier.');
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de mettre à jour le panier.');
        }
    }

    /**
     * Supprimer un produit du panier
     */
    public function removeFromCart($rootValue, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        
        try {
            $cartItem = Cart::where('user_id', $user->id)
                ->where('id', $args['id'])
                ->firstOrFail();
                
            $cartItem->delete();
            
            return [
                'message' => 'Item removed from cart successfully'
            ];
        } catch (ModelNotFoundException $e) {
            throw new CustomException('Produit introuvable', 'Le produit n\'est pas dans le panier.');
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de supprimer le produit du panier.');
        }
    }

    /**
     * Vider complètement le panier
     */
    public function clearCart($rootValue, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        
        try {
            Cart::where('user_id', $user->id)->delete();
            
            return [
                'message' => 'Cart cleared successfully'
            ];
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de vider le panier.');
        }
    }
}
