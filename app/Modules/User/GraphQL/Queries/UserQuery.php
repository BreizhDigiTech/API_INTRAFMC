<?php

namespace App\Modules\User\GraphQL\Queries;

use App\Models\User;
use App\Modules\User\Services\UserService;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Gate;
use App\Helpers\AuthHelper;

class UserQuery
{
    /**
     * Récupère la liste des utilisateurs.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function users($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        if (!Gate::allows('viewAny', User::class)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour voir la liste des utilisateurs.');
        }

        try {
            // Retourne directement UserPagination tel qu'attendu par le schéma GraphQL
            return app(UserService::class)->getUsers($args);
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer la liste des utilisateurs.');
        }
    }

    /**
     * Récupère un utilisateur spécifique.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function user($root, array $args)
    {
        $authUser = AuthHelper::ensureAuthenticated();

        try {
            $user = app(UserService::class)->getUserById($args['id']);

            if (!$user) {
                throw new CustomException('Utilisateur introuvable', "Aucun utilisateur n'a été trouvé avec cet identifiant.");
            }

            if (!Gate::allows('view', $user)) {
                throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour voir cet utilisateur.');
            }

            // Retourne directement l'utilisateur tel qu'attendu par le schéma GraphQL
            return $user;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer l’utilisateur.');
        }
    }
}