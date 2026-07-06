<?php

namespace Narakode\FineAuth\Auth\Default;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Narakode\FineAuth\Auth\Authenticator as AuthenticatorContract;

class Authenticator implements AuthenticatorContract
{
    public function attempt(array $credentials): User|false
    {
        if (!Auth::attempt($credentials)) {
            return false;
        }

        return Auth::user();
    }
}