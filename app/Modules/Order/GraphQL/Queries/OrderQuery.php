<?php

namespace App\Modules\Order\GraphQL\Queries;

use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Order;
use App\Helpers\AuthHelper;

class OrderQuery
{
    /**
     * Vérifie si une commande existe ou lève une exception.
     *
     * @param int $orderId
     * @return Order
     * @throws CustomException
     */
    private function findOrderOrFail($orderId)
    {
        $order = Order::with('products')->find($orderId);
        if (!$order) {
            throw new CustomException('Commande introuvable', "Aucune commande n'a été trouvée avec cet identifiant.");
        }
        return $order;
    }

    /**
     * Récupère toutes les commandes - Admin uniquement.
     *
     * @param mixed $root
     * @param array $args
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws CustomException
     */
    public function orders($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        if (!$user->is_admin) {
            throw new CustomException('Accès refusé', 'Seuls les administrateurs peuvent voir toutes les commandes.');
        }

        try {
            // La pagination est gérée automatiquement par @paginate dans le schéma
            return Order::with('products', 'user')->paginate($args['first'] ?? 10);
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer la liste des commandes.');
        }
    }

    /**
     * Récupère les commandes de l'utilisateur connecté uniquement.
     *
     * @param mixed $root
     * @param array $args
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws CustomException
     */
    public function myOrders($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        try {
            $query = Order::with('products')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Pagination manuelle
            $perPage = min($args['first'] ?? 10, 50); // Max 50 par page
            $page = $args['page'] ?? 1;
            
            return $query->paginate($perPage, ['*'], 'page', $page);
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer vos commandes.');
        }
    }

    /**
     * Récupère une commande spécifique.
     * Admin : peut voir toutes les commandes
     * Utilisateur : peut voir seulement ses commandes
     *
     * @param mixed $root
     * @param array $args
     * @return Order
     * @throws CustomException
     */
    public function order($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        $order = $this->findOrderOrFail($args['id']);

        // Vérification des permissions
        if (!$user->is_admin && $order->user_id !== $user->id) {
            throw new CustomException('Accès refusé', 'Vous n\'avez pas les permissions nécessaires pour voir cette commande.');
        }

        try {
            // Retourne directement la commande telle qu'attendue par le schéma GraphQL
            return $order;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer la commande.');
        }
    }
}