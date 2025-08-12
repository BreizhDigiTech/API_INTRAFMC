<?php

namespace App\Modules\User\GraphQL\Queries;

use App\Models\User;
use App\Modules\User\Services\UserService;
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
     */
    public function users($root, array $args)
    {
        AuthHelper::ensureAuthenticated();

        if (!Gate::allows('viewAny', User::class)) {
            // renvoyer une liste vide pour non-admin si nécessaire via builder (non modifié ici)
        }

        return app(UserService::class)->getUsers($args);
    }

    /**
     * Récupère un utilisateur spécifique.
     *
     * @param mixed $root
     * @param array $args
     * @return array|null
     */
    public function user($root, array $args)
    {
        AuthHelper::ensureAuthenticated();

        $user = app(UserService::class)->getUserById($args['id']);
        if (!$user) {
            return null;
        }

        if (!Gate::allows('view', $user)) {
            return null;
        }

        return $user;
    }
}