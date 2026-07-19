<?php

namespace Narakode\FineAuth\Auth;

use Illuminate\Foundation\Auth\User;

class AuthResult
{
    public function generateAuthResult(User $user): array
    {
        $expiresAt = now()->addMinutes(config('fineauth.access_token_expiration'));
        $accessToken = $user->createToken('api', ['*'], $expiresAt)->plainTextToken;

        return [
            'access_token' => $accessToken,
            'expires_at' => $expiresAt,
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