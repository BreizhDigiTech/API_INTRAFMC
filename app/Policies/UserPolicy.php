<?php
namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->is_admin;
    }

    public function view(User $authUser, User $targetUser): bool
    {
        return $authUser->id === $targetUser->id || $authUser->is_admin;
    }

    public function create(?User $authUser): bool
    {
        // Autorise la création du premier utilisateur si la base de données est vide
        if (User::count() === 0) {
            return true;
        }

        // Sinon, seul un administrateur peut créer un utilisateur
        return $authUser && $authUser->is_admin;
    }

    public function update(User $authUser, User $targetUser): bool
    {
        return $authUser->is_admin;
    }

    public function delete(User $authUser, User $targetUser): bool
    {
        return $authUser->is_admin;
    }
}