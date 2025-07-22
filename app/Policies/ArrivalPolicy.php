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
        // La Policy ne vérifie pas les rôles globaux ici
        return true; // Tous les utilisateurs connectés peuvent voir les arrivages
    }

    /**
     * Détermine si l'utilisateur peut voir un arrivage spécifique.
     */
    public function view(User $user, CbdArrival $arrival): bool
    {
        // Vérifie uniquement si l'utilisateur est lié à l'arrivage
        return $user->id === $arrival->created_by;
    }

    /**
     * Détermine si l'utilisateur peut créer un nouvel arrivage.
     */
    public function create(User $user): bool
    {
        // La Policy ne vérifie pas les rôles globaux ici
        return true; // Tous les utilisateurs connectés peuvent créer un arrivage
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour un arrivage.
     */
    public function update(User $user, CbdArrival $arrival): bool
    {
        // Vérifie uniquement si l'utilisateur est lié à l'arrivage
        return $user->id === $arrival->created_by;
    }

    /**
     * Détermine si l'utilisateur peut supprimer un arrivage.
     */
    public function delete(User $user, CbdArrival $arrival): bool
    {
        // La policy ne verifie pas les rôles globaux ici
        return true;
    }
}
