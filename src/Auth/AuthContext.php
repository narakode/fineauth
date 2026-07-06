<?php

namespace Narakode\FineAuth\Auth;

use Illuminate\Foundation\Auth\User;

class AuthContext
{
    public function __construct(private User $user) {}

    public function getUser(): User
    {
        return $this->user;
    }
}