<?php

namespace App\GraphQL\Queries;

use App\Models\User;

class UsersSearchQuery
{
    public function __invoke($root, array $args)
    {
        $query = User::query();

        // Recherche par nom ou email
        if (!empty($args['search'])) {
            $search = $args['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Filtre par rôle
        if (!empty($args['role'])) {
            if ($args['role'] === 'admin') {
                $query->where('is_admin', true);
            } elseif ($args['role'] === 'user') {
                $query->where('is_admin', false);
            }
        }

        // Filtre par statut
        if (!empty($args['status'])) {
            if ($args['status'] === 'active') {
                $query->where('is_active', true);
            } elseif ($args['status'] === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return $query->orderBy('created_at', 'desc');
    }
}
