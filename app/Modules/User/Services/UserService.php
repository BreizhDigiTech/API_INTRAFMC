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
        $user->update([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'avatar' => $data['avatar'] ?? $user->avatar,
        ]);
        return $user;
    }

    public function changePassword(array $data)
    {
        $user = auth()->user();
        if (!\Hash::check($data['current_password'], $user->password)) {
            throw new \Exception('Le mot de passe actuel est incorrect.');
        }
        $user->update(['password' => bcrypt($data['new_password'])]);
        return ['success' => true, 'message' => 'Mot de passe changé avec succès.'];
    }
}