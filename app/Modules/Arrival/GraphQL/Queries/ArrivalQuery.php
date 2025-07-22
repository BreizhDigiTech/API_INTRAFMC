<?php

namespace App\Modules\Arrival\GraphQL\Queries;

use App\Models\CbdArrival;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Gate;
use App\Helpers\AuthHelper;

class ArrivalQuery
{
    /**
     * Verifie si un arrivage existe ou leve une exception.
     *
     * @param int $arrivalId
     * @return CbdArrival
     * @throws CustomException
     */
    private function findArrivalOrFail($arrivalId)
    {
        $arrival = CbdArrival::with('products')->find($arrivalId);
        if (!$arrival) {
            throw new CustomException('Arrivage introuvable', "Aucun arrivage n'a ete trouve avec cet identifiant.");
        }
        return $arrival;
    }

    /**
     * Recupere la liste des arrivages.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function arrivals($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        if (!Gate::allows('viewAny', CbdArrival::class)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour voir la liste des arrivages.');
        }

        try {
            $arrivals = CbdArrival::with('products')->get();
            // Retourne directement le tableau d'arrivages tel qu'attendu par le schema GraphQL
            return $arrivals;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de recuperer la liste des arrivages.');
        }
    }

    /**
     * Recupere un arrivage specifique.
     *
     * @param mixed $root
     * @param array $args
     * @return CbdArrival
     * @throws CustomException
     */
    public function arrival($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        $arrival = $this->findArrivalOrFail($args['arrival_id']);

        if (!Gate::allows('view', $arrival)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour voir cet arrivage.');
        }

        // Retourne directement l'arrivage tel qu'attendu par le schema GraphQL
        return $arrival;
    }
}