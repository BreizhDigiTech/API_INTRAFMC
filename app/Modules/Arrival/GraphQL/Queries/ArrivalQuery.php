<?php

namespace App\Modules\Arrival\GraphQL\Queries;

use App\Models\CbdArrival;
use Illuminate\Support\Facades\Gate;
use App\Helpers\AuthHelper;

class ArrivalQuery
{
    /**
     * Retourne un builder d'arrivages pour être consommé par @paginate.
     * - Admin: renvoie tous les arrivages
     * - Non-admin: renvoie une requête vide (aucun résultat)
     */
    public function arrivals($root, array $args)
    {
        AuthHelper::ensureAuthenticated();

        if (Gate::allows('viewAny', CbdArrival::class)) {
            // Builder avec relations nécessaires, tri par dernières créations
            return CbdArrival::query()
                ->with('products')
                ->orderByDesc('created_at');
        }

        // Requête qui ne retourne rien pour les non-admins
        return CbdArrival::query()->whereRaw('1 = 0');
    }

    /**
     * Récupère un arrivage spécifique (utilisé par le champ arrival).
     */
    public function arrival($root, array $args)
    {
        AuthHelper::ensureAuthenticated();

        $arrival = CbdArrival::with('products')->find($args['arrival_id']);
        if (!$arrival) {
            return null;
        }

        if (!Gate::allows('view', $arrival)) {
            return null;
        }

        return $arrival;
    }
}