<?php

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthService
{
    /**
     * Connexion utilisateur.
     *
     * @param string $email
     * @param string $password
     * @return User|null
     */
    public function login(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        Auth::login($user);
        return $user;
    }

    /**
     * Déconnexion utilisateur.
     */
    public function logout(): void
    {
        Auth::logout();
    }

    /**
     * Retourne l'utilisateur connecté.
     *
     * @return User|null
     */
    public function me(): ?User
    {
        return Auth::user();
    }
}
