<?php

use Illuminate\Support\Facades\Cookie;
use Mockery\MockInterface;
use Narakode\FineAuth\RefreshToken\RefreshToken;
use Narakode\FineAuth\RefreshToken\RefreshTokenService;
use Symfony\Component\HttpFoundation\Cookie as HttpFoundationCookie;
use Workbench\App\Models\User;

describe('queueRefreshToken', function () {
    test('queues refresh token', function () {
        $this->freezeTime(function () {
            $refreshToken = new RefreshToken;

            $refreshToken->expire_at = now()->addHour();

            $user = $this->partialMock(User::class, function (MockInterface $mock) use ($refreshToken) {
                $mock->shouldReceive('createRefreshToken')
                    ->once()
                    ->andReturn($refreshToken); 
            });

            Cookie::expects('queue')
                ->once()
                ->with(
                    'refresh_token',
                    $refreshToken->token,
                    now()->diffInMinutes($refreshToken->expire_at),
                    '/',
                    null,
                    true,
                    true,
                    HttpFoundationCookie::SAMESITE_LAX
                );

            (new RefreshTokenService)->queueRefreshToken($user);
        });
    });
});