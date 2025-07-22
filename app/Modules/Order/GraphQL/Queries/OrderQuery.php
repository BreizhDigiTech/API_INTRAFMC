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
     * Récupère la liste des commandes de l'utilisateur connecté.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function orders($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        if (!Gate::allows('viewAny', Order::class)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour voir la liste des commandes.');
        }

        try {
            $orders = Order::with('products')->where('user_id', $user->id)->get();
            // Retourne directement le tableau de commandes tel qu'attendu par le schéma GraphQL
            return $orders;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer la liste des commandes.');
        }
    }

    /**
     * Récupère une commande spécifique de l'utilisateur connecté.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function order($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        $order = $this->findOrderOrFail($args['id']);

        if ($order->user_id !== $user->id) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour voir cette commande.');
        }

        try {
            // Retourne directement la commande telle qu'attendue par le schéma GraphQL
            return $order;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer la commande.');
        }
    }
}