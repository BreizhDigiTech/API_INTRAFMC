<?php

namespace App\Modules\User\GraphQL\Mutations;

use App\Modules\User\Services\UserService;
use App\Exceptions\CustomException;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Helpers\AuthHelper;

class UserMutator
{
    public function updateUser($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $targetUser = User::findOrFail($args['id']);

        if (!Gate::allows('update', $targetUser)) {
            throw new CustomException('Acces refuse', 'Permissions insuffisantes pour modifier cet utilisateur.');
        }

        try {
            $updatedUser = app(UserService::class)->updateUser($args);
            return $updatedUser;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de mettre a jour l utilisateur.');
        }
    }

    public function deleteUser($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $targetUser = User::findOrFail($args['id']);

        if (!Gate::allows('delete', $targetUser)) {
            throw new CustomException('Acces refuse', 'Permissions insuffisantes pour supprimer cet utilisateur.');
        }

        try {
            $result = app(UserService::class)->deleteUser($args);
            return $result;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de supprimer l utilisateur.');
        }
    }

    public function updateProfile($root, array $args)
    {
        $authUser = AuthHelper::ensureAuthenticated();

        if ($authUser->id != $args['id']) {
            throw new CustomException('Acces refuse', 'Vous ne pouvez modifier que votre propre profil.');
        }

        try {
            $updatedProfile = app(UserService::class)->updateProfile($args);
            return $updatedProfile;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de mettre a jour le profil.');
        }
    }

    public function changePassword($root, array $args)
    {
        $authUser = AuthHelper::ensureAuthenticated();

        try {
            $result = app(UserService::class)->changePassword($args);
            return $result;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de changer le mot de passe.');
        }
    }
}
