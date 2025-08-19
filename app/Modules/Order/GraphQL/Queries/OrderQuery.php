<?php

namespace App\Modules\Order\GraphQL\Queries;

use App\Exceptions\CustomException;
use App\Helpers\AuthHelper;
use App\Models\Order;
use Illuminate\Support\Facades\Gate;

class OrderQuery
{
    /**
     * Builder pour la liste admin des commandes (tri DESC)
     */
    public function orders($root, array $args)
    {
        AuthHelper::ensureAuthenticated();

        if (!Gate::allows('viewAny', Order::class)) {
            // liste vide si non autorisé
            return Order::query()->whereRaw('1=0');
        }

        return Order::query()->orderByDesc('created_at');
    }

    /**
     * Builder pour mes commandes (utilisateur courant), tri DESC
     */
    public function myOrders($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        return Order::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at');
    }

    /**
     * Retourne une commande avec verifications d'autorisation et tous ses détails.
     *
     * @param mixed $_
     * @param array $args
     * @return Order
     * @throws CustomException
     */
    public function order($_, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        // Récupérer la commande avec toutes ses relations
        $order = Order::withFullDetails()->find($args['id'] ?? null);
        
        if (!$order) {
            throw new CustomException('Commande introuvable', 'Aucune commande trouvee avec cet ID.');
        }

        if (!Gate::allows('view', $order)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour voir cette commande.');
        }

        return $order;
    }

    /**
     * Récupère les détails d'une commande avec toutes les informations nécessaires
     */
    public function orderDetails($_, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $orderId = $args['id'] ?? null;

        if (!$orderId) {
            throw new CustomException('ID manquant', 'L\'ID de la commande est requis.');
        }

        // Construire la requête avec eager loading optimisé
        $query = Order::withFullDetails()
            ->where('id', $orderId);

        // Si l'utilisateur n'est pas admin, limiter aux ses propres commandes
        if (!$user->is_admin) {
            $query->where('user_id', $user->id);
        }

        $order = $query->first();

        if (!$order) {
            throw new CustomException(
                'Commande introuvable', 
                'Aucune commande trouvee avec cet ID ou vous n\'avez pas les permissions pour la voir.'
            );
        }

        return $order;
    }

    /**
     * Récupère les statistiques d'une commande
     */
    public function orderStats($_, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $orderId = $args['id'] ?? null;

        $order = Order::withFullDetails()->find($orderId);
        
        if (!$order) {
            throw new CustomException('Commande introuvable', 'Aucune commande trouvee avec cet ID.');
        }

        if (!Gate::allows('view', $order)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour voir cette commande.');
        }

        return [
            'order_id' => $order->id,
            'total_items' => $order->total_items,
            'product_count' => $order->product_count,
            'total_amount' => $order->total,
            'average_item_price' => $order->products->count() > 0 ? $order->total / $order->total_items : 0,
            'created_at' => $order->created_at,
            'status' => $order->status,
            'formatted_status' => $order->formatted_status
        ];
    }
}
