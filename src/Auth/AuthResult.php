<?php

namespace Narakode\FineAuth\Auth;

use Illuminate\Foundation\Auth\User;

class AuthResult
{
    public function generateAuthResult(User $user): array
    {
        $accessToken = $user->createToken('api')->plainTextToken;

        return [
            'access_token' => $accessToken,
            ...$this->generateCurrentUserResult($user)
        ];
    }

    public function generateCurrentUserResult(User $user): array
    {
        return [
            'user' => $user,
            'meta' => app(AuthMeta::class)->toArray(new AuthContext($user))
        ];
    }
}