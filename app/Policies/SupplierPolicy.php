<?php
namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->is_admin;
    }

    public function view(User $authUser, Supplier $supplier): bool
    {
        return $authUser->is_admin;
    }

    public function create(User $authUser): bool
    {
        return $authUser->is_admin;
    }

    public function update(User $authUser, Supplier $supplier): bool
    {
        return $authUser->is_admin;
    }

    public function delete(User $authUser, Supplier $supplier): bool
    {
        return $authUser->is_admin;
    }
}