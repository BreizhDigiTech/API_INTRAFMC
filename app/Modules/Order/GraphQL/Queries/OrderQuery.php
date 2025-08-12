<?php

namespace App\Modules\Order\GraphQL\Queries;

use App\Exceptions\CustomException;
use App\Helpers\AuthHelper;
use App\Models\Order;
use Illuminate\Support\Facades\Gate;

class OrderQuery
{
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
