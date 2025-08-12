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
     * Retourne une commande avec verifications d'autorisation.
     *
     * @param mixed $_
     * @param array $args
     * @return Order
     * @throws CustomException
     */
    public function order($_, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        $order = Order::find($args['id'] ?? null);
        if (!$order) {
            throw new CustomException('Commande introuvable', 'Aucune commande trouvee avec cet ID.');
        }

        if (!Gate::allows('view', $order)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour voir cette commande.');
        }

        return $order;
    }
}
