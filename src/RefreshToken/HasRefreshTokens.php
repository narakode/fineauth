<?php

namespace Narakode\FineAuth\RefreshToken;

trait HasRefreshTokens
{
    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }

    public function createRefreshToken(): RefreshToken
    {
        return app(RefreshTokenService::class)->storeRefreshToken($this);
    }
}