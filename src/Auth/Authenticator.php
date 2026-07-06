<?php

namespace Narakode\FineAuth\Auth;

use Illuminate\Foundation\Auth\User;

interface Authenticator
{
    public function attempt(array $credentials): User|false;
}