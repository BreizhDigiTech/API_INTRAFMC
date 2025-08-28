<?php

namespace App\GraphQL\Queries;

use Illuminate\Support\Facades\Auth;

class MyProfileCompleteQuery
{
    public function __invoke()
    {
        $user = Auth::user();
        
        if (!$user) {
            throw new \Exception('User not authenticated');
        }

        // Calculer statistiques utilisateur
        $ordersCount = $user->orders()->count();
        $totalSpent = $user->orders()->whereNotIn('status', ['cancelled'])->sum('total');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'birth_date' => $user->birth_date,
            'avatar' => $user->avatar,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'email_verified_at' => $user->email_verified_at,
            'last_login' => $user->last_login,
            'orders_count' => $ordersCount,
            'total_spent' => (float) $totalSpent,
            'preferences' => [
                'theme' => 'light', // Valeur par défaut
                'language' => 'fr',
                'notifications' => true,
            ],
        ];
    }
}
