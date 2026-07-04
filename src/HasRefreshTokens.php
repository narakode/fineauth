<?php

namespace Narakode\FineAuth;

use Illuminate\Support\Str;
use Narakode\FineAuth\Models\RefreshToken;

trait HasRefreshTokens
{
    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }

    public function createRefreshToken()
    {
        $refreshToken = Str::random();

        $expireAt = now()->addHour();

        $this->refreshTokens()->delete();
        
        return $this->refreshTokens()->create([
            'token' => $refreshToken,
            'expire_at' => $expireAt
        ]);
    }
}