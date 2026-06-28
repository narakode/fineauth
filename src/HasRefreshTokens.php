<?php

namespace Narakode\FineAuth;

use Narakode\FineAuth\Models\RefreshToken;

trait HasRefreshTokens
{
    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }
}