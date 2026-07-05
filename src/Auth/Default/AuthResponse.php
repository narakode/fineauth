<?php

namespace Narakode\FineAuth\Auth\Default;

use Narakode\FineAuth\Auth\AuthResponse as AuthResponseContract;
use Narakode\FineAuth\Auth\AuthResult;

class AuthResponse implements AuthResponseContract
{
    public function toArray(AuthResult $auth): array
    {
        return [
            'user' => $auth->getUser(),
            'access_token' => $auth->getAccessToken()
        ];
    }
}