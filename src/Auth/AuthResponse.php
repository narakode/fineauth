<?php

namespace Narakode\FineAuth\Auth;

interface AuthResponse
{
    public function toArray(AuthResult $auth): array;
}