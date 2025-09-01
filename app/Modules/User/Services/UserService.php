<?php

namespace App\Modules\User\Services;

use App\Models\User;

class UserService
{
    public function getUsers(array $args)
    {
        $query = User::query();

        $pagination = $query->paginate(
            $perPage = $args['per_page'] ?? 5,
            $columns = ['*'],
            $pageName = 'page',
            $page = $args['page'] ?? 1
        );

        return [
            'data' => $pagination->items(),
            'pagination' => [
                'total' => $pagination->total(),
                'per_page' => $pagination->perPage(),
                'current_page' => $pagination->currentPage(),
                'last_page' => $pagination->lastPage(),
            ],
        ];
    }

    public function getUserById($id)
    {
        return User::find($id);
    }

    public function updateUser(array $data)
    {
        $user = User::find($data['id']);
        if (!$user) {
            throw new \App\Exceptions\CustomException('Utilisateur introuvable', 'Aucun utilisateur avec cet identifiant.');
        }

        $validatedData = $this->validateUpdateData($data);

        $user->update([
            'name' => $validatedData['name'] ?? $user->name,
            'email' => $validatedData['email'] ?? $user->email,
            'phone' => $validatedData['phone'] ?? $user->phone,
            'address' => $validatedData['address'] ?? $user->address,
            'birth_date' => $validatedData['birth_date'] ?? $user->birth_date,
            'is_active' => $validatedData['is_active'] ?? $user->is_active,
            'is_admin' => $validatedData['is_admin'] ?? $user->is_admin,
        ]);

        if (!empty($validatedData['password'])) {
            $user->update(['password' => bcrypt($validatedData['password'])]);
        }

        return $user;
    }

    private function validateUpdateData(array $data): array
    {
        $validator = \Illuminate\Support\Facades\Validator::make($data, [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . ($data['id'] ?? 'NULL'),
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/[A-Z]/', $value) || !preg_match('/[a-z]/', $value) || !preg_match('/[0-9]/', $value) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $value)) {
                        $fail('Le mot de passe doit contenir une majuscule, une minuscule, un chiffre et un caractère spécial.');
                    }
                },
            ],
            'is_active' => 'nullable|boolean',
            'is_admin' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }

    public function deleteUser(array $args)
    {
        $user = User::find($args['id']);
        if (!$user) {
            throw new \App\Exceptions\CustomException('Utilisateur introuvable', 'Aucun utilisateur avec cet identifiant.');
        }
        $user->delete();
        return [
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès.',
        ];
    }

    public function updateProfile(array $data)
    {
        $user = auth()->user();
        
        if (!$user) {
            throw new \App\Exceptions\CustomException('Non authentifié', 'Utilisateur non connecté.');
        }
        
        // Préparer les données à mettre à jour (uniquement les champs fournis)
        $updateData = [];
        
        // Champs de profil modifiables par l'utilisateur
        $allowedFields = ['name', 'email', 'phone', 'address', 'birth_date', 'avatar'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $updateData[$field] = $data[$field];
            }
        }
        
        // Validation spécifique pour l'email
        if (isset($updateData['email']) && $updateData['email'] !== $user->email) {
            // Validation format email
            if (!filter_var($updateData['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \App\Exceptions\CustomException('Email invalide', 'Le format de l\'email est incorrect.');
            }
            
            // Vérifier que l'email n'est pas déjà utilisé
            $existingUser = \App\Models\User::where('email', $updateData['email'])
                                          ->where('id', '!=', $user->id)
                                          ->first();
            if ($existingUser) {
                throw new \App\Exceptions\CustomException('Email déjà utilisé', 'Cette adresse email est déjà utilisée par un autre utilisateur.');
            }
        }
        
        // Validation pour le téléphone
        if (isset($updateData['phone']) && !empty($updateData['phone'])) {
            if (!preg_match('/^[0-9+\-\s\(\)]+$/', $updateData['phone'])) {
                throw new \App\Exceptions\CustomException('Téléphone invalide', 'Le format du numéro de téléphone est incorrect.');
            }
        }
        
        // Traitement spécial pour l'avatar - stocker uniquement le chemin relatif
        if (isset($updateData['avatar'])) {
            // Si c'est une URL complète, extraire le chemin relatif
            $avatar = $updateData['avatar'];
            if (strpos($avatar, 'avatars/') !== false) {
                // Extraire juste le nom de fichier à partir de l'URL
                $pathParts = explode('avatars/', $avatar);
                $updateData['avatar'] = 'avatars/' . end($pathParts);
            }
        }
        
        // Mettre à jour uniquement les champs fournis
        if (!empty($updateData)) {
            try {
                $user->update($updateData);
            } catch (\Exception $e) {
                throw new \App\Exceptions\CustomException('Erreur de mise à jour', 'Impossible de mettre à jour le profil: ' . $e->getMessage());
            }
        }
        
        return $user->fresh();
    }

    public function changePassword(array $data)
    {
        $user = auth()->user();
        
        if (!$user) {
            throw new \App\Exceptions\CustomException('Non authentifié', 'Utilisateur non connecté.');
        }
        
        // Validation du mot de passe actuel
        if (!\Hash::check($data['current_password'], $user->password)) {
            throw new \App\Exceptions\CustomException('Mot de passe incorrect', 'Le mot de passe actuel est incorrect.');
        }
        
        // Validation du nouveau mot de passe
        if (strlen($data['new_password']) < 8) {
            throw new \App\Exceptions\CustomException('Mot de passe trop court', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
        }
        
        // Validation complexité mot de passe
        if (!preg_match('/[A-Z]/', $data['new_password']) || 
            !preg_match('/[a-z]/', $data['new_password']) || 
            !preg_match('/[0-9]/', $data['new_password']) || 
            !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $data['new_password'])) {
            throw new \App\Exceptions\CustomException('Mot de passe faible', 'Le mot de passe doit contenir une majuscule, une minuscule, un chiffre et un caractère spécial.');
        }
        
        try {
            $user->update(['password' => bcrypt($data['new_password'])]);
            return ['success' => true, 'message' => 'Mot de passe changé avec succès.'];
        } catch (\Exception $e) {
            throw new \App\Exceptions\CustomException('Erreur de mise à jour', 'Impossible de changer le mot de passe: ' . $e->getMessage());
        }
    }
}