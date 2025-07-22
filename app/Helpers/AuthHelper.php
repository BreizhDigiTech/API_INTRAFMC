<?php
namespace App\Helpers;

use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Auth;

class AuthHelper
{
    /**
     * Vérifie si l'utilisateur est authentifié.
     * @throws CustomException
     */
    public static function ensureAuthenticated()
    {
        $user = Auth::user();
        if (!$user) {
            throw new CustomException('Authentification requise', 'Vous devez être connecté pour effectuer cette action.');
        }
        return $user;
    }
}