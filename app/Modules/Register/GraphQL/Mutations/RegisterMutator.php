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
        // Vérifie si l'utilisateur a les permissions nécessaires pour créer un utilisateur
        if (!Gate::allows('create', User::class)) {
            throw new CustomException('Accès refusé', 'Vous n\'avez pas les permissions nécessaires pour créer un utilisateur.');
        }

        try {
            // Appelle le service d'inscription
            $result = $this->service->register($args);
            
            // Retourne directement la structure attendue par le schéma GraphQL
            return [
                'user' => $result['user'],
                'token' => $result['token'],
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Gestion des erreurs de validation
            $messages = $e->validator->errors()->all();
            throw new CustomException('Erreur de validation', implode(' ', $messages));
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de créer l\'utilisateur.');
        }
    }
}
