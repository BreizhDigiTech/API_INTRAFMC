<?php
namespace App\Policies;

use App\Models\ProductCBD;
use App\Models\User;

class ProductCBDPolicy
{
    public function viewAny(User $authUser): bool
    {
        return true; // Tous les utilisateurs peuvent voir les produits
    }

    public function view(User $authUser, ProductCBD $product): bool
    {
        return true; // Tous les utilisateurs peuvent voir un produit
    }

    public function create(User $authUser): bool
    {
        return $authUser->is_admin;
    }

    public function update(User $authUser, ProductCBD $product): bool
    {
        return $authUser->is_admin;
    }

    public function delete(User $authUser, ProductCBD $product): bool
    {
        return $authUser->is_admin;
    }
}