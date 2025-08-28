<?php

namespace App\GraphQL\Queries;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class ValidateTokenQuery
{
    public function __invoke()
    {
        try {
            $token = JWTAuth::getToken();
            
            if (!$token) {
                return [
                    'valid' => false,
                    'expires_at' => null,
                    'user' => null,
                ];
            }

            $user = JWTAuth::authenticate($token);
            $payload = JWTAuth::getPayload($token);
            $expiresAt = $payload->get('exp');

            return [
                'valid' => true,
                'expires_at' => date('Y-m-d H:i:s', $expiresAt),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'expires_at' => null,
                'user' => null,
            ];
        }
    }
}
