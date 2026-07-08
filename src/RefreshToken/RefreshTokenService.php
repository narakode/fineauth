<?php

namespace Narakode\FineAuth\RefreshToken;

class RefreshTokenService
{
    public function findByToken(string $token): ?RefreshToken
    {
        return RefreshToken::with('user')
            ->firstWhere('token', $token);
    }
}