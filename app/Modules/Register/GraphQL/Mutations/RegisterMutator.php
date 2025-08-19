<?php

namespace App\Modules\Register\GraphQL\Mutations;

use App\Exceptions\CustomException;
use App\Modules\Register\Services\RegisterService;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class RegisterMutator
{
    protected $service;

    public function __construct()
    {
        $this->service = app(RegisterService::class);
    }

    /**
     * Inscrit un nouvel utilisateur.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function register($root, array $args)
    {
        try {
            // Appelle le service d'inscription
            $result = $this->service->register($args);
            
            // Retourne directement la structure attendue par le schéma GraphQL
            return [
                'access_token' => $result['token'],
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'user' => $result['user'],
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Gestion des erreurs de validation
            $messages = $e->validator->errors()->all();
            throw new CustomException('Erreur de validation', implode(' ', $messages));
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de créer l\'utilisateur.');
        }
    }

    /**
     * Vérifie l'email d'un utilisateur avec un token.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function verifyEmail($root, array $args)
    {
        try {
            $result = $this->service->verifyEmail($args['token']);
            
            return [
                'success' => $result,
                'message' => $result ? 'Email vérifié avec succès' : 'Token invalide ou expiré'
            ];
        } catch (\Exception $e) {
            throw new CustomException('Erreur de vérification', 'Token invalide ou expiré.');
        }
    }

    /**
     * Renvoie l'email de vérification.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function resendVerificationEmail($root, array $args)
    {
        try {
            $result = $this->service->resendVerificationEmail($args['email']);
            
            return [
                'success' => $result,
                'message' => $result ? 'Email de vérification envoyé' : 'Utilisateur introuvable ou déjà vérifié'
            ];
        } catch (\Exception $e) {
            throw new CustomException('Erreur d\'envoi', 'Impossible d\'envoyer l\'email de vérification.');
        }
    }
}
