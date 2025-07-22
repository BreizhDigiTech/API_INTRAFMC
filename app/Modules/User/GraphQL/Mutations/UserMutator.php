<?php

namespace App\Modules\User\GraphQL\Mutations;

use App\Modules\User\Services\UserService;
use App\Exceptions\CustomException;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Helpers\AuthHelper;

class UserMutator
{
    /**
     * Met à jour un utilisateur.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function updateUser($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $targetUser = User::findOrFail($args['id']);

        if (!Gate::allows('update', $targetUser)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour modifier cet utilisateur.');
        }

        try {
            $updatedUser = app(UserService::class)->updateUser($args);
            // Retourne directement l'utilisateur tel qu'attendu par le schéma GraphQL
            return $updatedUser;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de mettre à jour l’utilisateur.');
        }
    }

    /**
     * Supprime un utilisateur.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function deleteUser($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $targetUser = User::findOrFail($args['id']);

        if (!Gate::allows('delete', $targetUser)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour supprimer cet utilisateur.');
        }

        try {
            app(UserService::class)->deleteUser($args);
            return [
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès.',
            ];
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de supprimer l’utilisateur.');
        }
    }

    /**
     * Met à jour le profil de l'utilisateur connecté.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function updateProfile($root, array $args)
    {
        $authUser = AuthHelper::ensureAuthenticated();

        if ($authUser->id !== $args['id']) {
            throw new CustomException('Accès refusé', 'Vous ne pouvez modifier que votre propre profil.');
        }

        try {
            $updatedProfile = app(UserService::class)->updateProfile($args);
            // Retourne directement l'utilisateur tel qu'attendu par le schéma GraphQL
            return $updatedProfile;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de mettre à jour le profil.');
        }
    }

    /**
     * Change le mot de passe de l'utilisateur connecté.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function changePassword($root, array $args)
    {
        $authUser = AuthHelper::ensureAuthenticated();

        try {
            app(UserService::class)->changePassword($args);
            return [
                'success' => true,
                'message' => 'Mot de passe changé avec succès.',
            ];
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de changer le mot de passe.');
        }
    }
}