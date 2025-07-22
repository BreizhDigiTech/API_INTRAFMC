<?php
namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $authUser): bool
    {
        return true; // Tous les utilisateurs peuvent voir les catégories
    }

    public function view(User $authUser, Category $category): bool
    {
        return true; // Tous les utilisateurs peuvent voir une catégorie
    }

    public function create(User $authUser): bool
    {
        return $authUser->is_admin;
    }

    public function update(User $authUser, Category $category): bool
    {
        return $authUser->is_admin;
    }

    public function delete(User $authUser, Category $category): bool
    {
        return $authUser->is_admin;
    }
}