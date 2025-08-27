<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     * Seuls les admins peuvent voir toutes les commandes
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
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
        // Un utilisateur peut annuler ses commandes en attente, un admin peut tout annuler
        return ($user->id === $order->user_id && $order->status === 'pending') || $user->is_admin;
    }

    /**
     * Determine whether the user can view statistics.
     * Tous les utilisateurs connectés peuvent voir les statistiques
     */
    public function viewStatistics(User $user): bool
    {
        return true; // Autoriser tous les utilisateurs connectés
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
