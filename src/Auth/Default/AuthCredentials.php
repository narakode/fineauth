<?php

namespace Narakode\FineAuth\Auth\Default;

use Illuminate\Validation\Rules\Password;
use Narakode\FineAuth\Auth\AuthCredentials as AuthCredentialsContract;

class AuthCredentials implements AuthCredentialsContract
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => [
                'required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ];
    }
}