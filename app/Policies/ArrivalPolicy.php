<?php

namespace App\Policies;

use App\Models\CbdArrival;
use App\Models\User;

class ArrivalPolicy
{
    /**
     * Détermine si l'utilisateur peut voir la liste des arrivages.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Détermine si l'utilisateur peut voir un arrivage spécifique.
     */
    public function view(User $user, CbdArrival $arrival): bool
    {
        return $user->is_admin;
    }

    /**
     * Détermine si l'utilisateur peut créer un nouvel arrivage.
     */
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour un arrivage.
     */
    public function update(User $user, CbdArrival $arrival): bool
    {
        return $user->is_admin;
    }

    /**
     * Détermine si l'utilisateur peut supprimer un arrivage.
     */
    public function delete(User $user, CbdArrival $arrival): bool
    {
        return $user->is_admin;
    }
}
