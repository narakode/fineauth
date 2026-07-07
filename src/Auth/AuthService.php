<?php

namespace Narakode\FineAuth\Auth;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Cookie;
use Narakode\FineAuth\Auth\Exceptions\LoginException;
use Narakode\FineAuth\Auth\Exceptions\RefreshTokenException;
use Narakode\FineAuth\RefreshToken\RefreshToken;
use Symfony\Component\HttpFoundation\Cookie as HttpFoundationCookie;

class AuthService
{
    public function __construct(private AuthResult $authResult) {}

    public function login(array $credentials): array
    {
        $user = app(Authenticator::class)->attempt($credentials);

        if (!$user) {
            throw new LoginException('The provided credentials do not match our records.');
        }

        $this->queueRefreshToken($user);

        return $this->authResult->generateAuthResult($user);
    }

    public function refreshToken(string $rawToken): array
    {
        $refreshToken = RefreshToken::with('user')
            ->firstWhere('token', $rawToken);

        if (!$refreshToken) {
            throw new RefreshTokenException;
        }

        if ($refreshToken->expire_at->lessThan(now())) {
            throw new RefreshTokenException;
        }

        return $this->authResult->generateAuthResult($refreshToken->user);
    }

    private function queueRefreshToken(User $user): void
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