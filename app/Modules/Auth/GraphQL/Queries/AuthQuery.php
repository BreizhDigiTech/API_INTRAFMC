<?php

namespace App\Modules\Auth\GraphQL\Queries;

use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Auth;
use App\Helpers\AuthHelper;

class AuthQuery
{
    /**
     * Retourne l'utilisateur connecté.
     *
     * @return array
     * @throws CustomException
     */
    public function me()
    {
        try {
            // Vérifie si l'utilisateur est authentifié
            $user = AuthHelper::ensureAuthenticated();

            // Retourne directement l'utilisateur tel qu'attendu par le schéma GraphQL
            return $user;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer l’utilisateur connecté.');
        }
    }
}
