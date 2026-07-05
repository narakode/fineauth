<?php

namespace Narakode\FineAuth\Auth;

use Illuminate\Foundation\Auth\User;

class AuthService
{
    public function authenticate(User $user): AuthResult
    {
        $accessToken = $user->createToken('api')->plainTextToken;

        return new AuthResult($user, $accessToken);
    }
}