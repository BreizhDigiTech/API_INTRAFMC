<?php

namespace App\Modules\Register\Services;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class RegisterService
{
    /**
     * Inscrit un nouvel utilisateur.
     *
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function register(array $data): array
    {
        // Valider les données d'inscription
        $validatedData = $this->validateRegistrationData($data);

        // Définir un avatar par défaut si non fourni
        if (empty($validatedData['avatar'])) {
            $validatedData['avatar'] = 'avatars/avatar-default-symbolic.svg';
        }

        // Créer l'utilisateur
        $user = User::create($validatedData);

        // Assigner un rôle par défaut
        $this->assignDefaultRole($user);

        // Retourner les informations de l'utilisateur et le token JWT
        return [
            'user' => $user,
            'token' => JWTAuth::fromUser($user),
        ];
    }

    /**
     * Valide les données d'inscription.
     *
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    private function validateRegistrationData(array $data): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/[A-Z]/', $value) || !preg_match('/[a-z]/', $value) || !preg_match('/[0-9]/', $value) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $value)) {
                        $fail('Le mot de passe doit contenir une majuscule, une minuscule, un chiffre et un caractère spécial.');
                    }
                },
            ],
            'avatar' => 'nullable|string',
            'is_admin' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'email_verified_at' => 'nullable|date',
            'remember_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Assigne un rôle par défaut à l'utilisateur.
     *
     * @param User $user
     */
    private function assignDefaultRole(User $user): void
    {
        $user->is_admin = User::USER;
        $user->is_active = User::ACTIVE;
        $user->save();
    }
}