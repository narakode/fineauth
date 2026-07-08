<?php

namespace Narakode\FineAuth\RefreshToken;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Cookie as HttpFoundationCookie;

class RefreshTokenService
{
    public function findByToken(string $token): ?RefreshToken
    {
        return RefreshToken::with('user')
            ->firstWhere('token', $token);
    }

    public function queueRefreshToken(User $user): void
    {
        $refreshToken = $user->createRefreshToken();

        Cookie::queue(
            'refresh_token',
            $refreshToken->token,
            now()->diffInMinutes($refreshToken->expire_at),
            '/',
            null,
            true,
            true,
            HttpFoundationCookie::SAMESITE_LAX
        );
    }
}