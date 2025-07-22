<?php

namespace App\Modules\Auth\GraphQL\Mutations;

use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthMutator
{
    /**
     * Connexion utilisateur, retourne token et user.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function login($root, array $args)
    {
        // Validation des arguments
        $this->validateLoginInput($args);

        // Récupération de l'utilisateur
        $user = \App\Models\User::where('email', $args['email'])->first();

        if (!$user) {
            throw new CustomException('Invalid credentials', "The provided credentials are incorrect.");
        }

        if (!$user->is_active) {
            throw new CustomException('Compte désactivé', "Votre compte est désactivé. Veuillez contacter l'administrateur.");
        }

        // Vérification des identifiants
        if (!Auth::attempt(['email' => $args['email'], 'password' => $args['password']])) {
            throw new CustomException('Invalid credentials', "The provided credentials are incorrect.");
        }

        try {
            // Génération du token
            $token = JWTAuth::fromUser($user);

            if (!$token) {
                throw new CustomException('Erreur interne', 'Impossible de générer le token.');
            }

            return [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60, // Convert minutes to seconds
                'user' => $user,
            ];
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de générer le token.');
        }
    }

    /**
     * Déconnexion utilisateur.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function logout($root, array $args)
    {
        try {
            // Invalidation du token
            JWTAuth::invalidate(JWTAuth::getToken());
            return [
                'message' => 'Successfully logged out',
            ];
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            throw new CustomException('Token invalide', 'Le token fourni est invalide.');
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de révoquer le token.');
        }
    }

    /**
     * Valide les données d'entrée pour la connexion.
     *
     * @param array $input
     * @throws CustomException
     */
    private function validateLoginInput(array $input)
    {
        $validator = validator($input, [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            throw new CustomException('Données invalides', implode(' ', $validator->errors()->all()));
        }
    }
}
