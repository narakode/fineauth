<?php

namespace Narakode\FineAuth\Auth;

use Narakode\FineAuth\Auth\Exceptions\LoginException;
use Narakode\FineAuth\Auth\Exceptions\RefreshTokenException;
use Narakode\FineAuth\RefreshToken\RefreshTokenService;

class AuthService
{
    public function __construct() {}

    public function login(array $credentials): array
    {
        $user = app(Authenticator::class)->attempt($credentials);

        if (!$user) {
            throw new LoginException('The provided credentials do not match our records.');
        }

        app(RefreshTokenService::class)->queueRefreshToken($user);

        return app(AuthResult::class)->generateAuthResult($user);
    }

    public function refreshToken(string $rawToken): array
    {
        $refreshToken = app(RefreshTokenService::class)->findByToken($rawToken);

        if (!$refreshToken) {
            throw new RefreshTokenException;
        }

        if ($refreshToken->expire_at->lessThan(now())) {
            throw new RefreshTokenException;
        }

        return app(AuthResult::class)->generateAuthResult($refreshToken->user);
    }
}