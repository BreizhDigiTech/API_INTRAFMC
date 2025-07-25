<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Tous les utilisateurs peuvent voir leurs commandes
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        // Un utilisateur peut voir sa commande, un admin peut tout voir
        return $user->id === $order->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Tous les utilisateurs peuvent créer une commande
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return false; // Les commandes ne sont pas modifiables
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        // Seul un admin peut supprimer une commande
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return false;
    }
}
