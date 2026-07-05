<?php

namespace Narakode\FineAuth\Auth;

use Illuminate\Foundation\Auth\User;

class AuthResult
{
    public function __construct(private User $user, private string $accessToken) {}

    public function getUser(): User
    {
        return $this->user;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}