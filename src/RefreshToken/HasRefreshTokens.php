<?php

namespace Narakode\FineAuth\RefreshToken;

use Illuminate\Support\Str;

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