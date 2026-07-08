<?php

use Illuminate\Support\Facades\Cookie;
use Mockery\MockInterface;
use Narakode\FineAuth\Auth\Authenticator;
use Narakode\FineAuth\Auth\AuthResult;
use Narakode\FineAuth\Auth\AuthService;
use Narakode\FineAuth\Auth\Exceptions\LoginException;
use Narakode\FineAuth\Auth\Exceptions\RefreshTokenException;
use Narakode\FineAuth\RefreshToken\RefreshTokenService;
use Narakode\FineAuth\RefreshToken\RefreshToken;
use Symfony\Component\HttpFoundation\Cookie as HttpFoundationCookie;
use Workbench\App\Models\User;

describe('login', function () {
    test('throws LoginException when attempt fails', function () {
        $credentials = ['email' => 'test@example.com'];

        $this->partialMock(Authenticator::class, function (MockInterface $mock) use ($credentials) {
            $mock->shouldReceive('attempt')
                ->with($credentials)
                ->andReturnFalse(); 
        });

        $this->expectException(LoginException::class);
        $this->expectExceptionMessage('The provided credentials do not match our records.');

        (new AuthService)->login($credentials);
    });

    test('queues cookie when attempt success', function () {
        $this->freezeTime(function () {
            $refreshToken = new RefreshToken;

            $user = $this->partialMock(User::class, function (MockInterface $mock) use ($refreshToken) {
                $mock->shouldReceive('createRefreshToken')
                    ->andReturn($refreshToken);
            });

            $this->partialMock(Authenticator::class, function (MockInterface $mock) use ($user) {
                $mock->shouldReceive('attempt')
                    ->andReturn($user); 
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

            $this->mock(AuthResult::class, function (MockInterface $mock) {
                $mock->shouldReceive('generateAuthResult');
            });

            (new AuthService)->login([]);
        });
    });

    test('returns generated auth result when attempt success', function () {
        $refreshToken = new RefreshToken;

        $user = $this->partialMock(User::class, function (MockInterface $mock) use ($refreshToken) {
            $mock->shouldReceive('createRefreshToken')
                ->andReturn($refreshToken);
        });

        $this->partialMock(Authenticator::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('attempt')
                ->andReturn($user); 
        });

        $loginResult = ['user' => $user, 'access_token' => 'blah'];

        $this->mock(AuthResult::class, function (MockInterface $mock) use ($user, $loginResult) {
            $mock->shouldReceive('generateAuthResult')
                ->once()
                ->with($user)
                ->andReturn($loginResult);
        });

        $this->assertEquals((new AuthService)->login([]), $loginResult);
    });
});

describe('refreshToken', function () {
    test('throws RefreshTokenException when token not found', function () {
        $this->partialMock(RefreshTokenService::class, function (MockInterface $mock) {
            $mock->shouldReceive('findByToken')
                ->once()
                ->andReturnNull();
        });

        $this->expectException(RefreshTokenException::class);

        (new AuthService)->refreshToken('test');
    });

    test('throws RefreshTokenException when token expired', function () {
        $token = 'test';

        $this->partialMock(RefreshTokenService::class, function (MockInterface $mock) use ($token) {
            $value = new RefreshToken();

            $value->expire_at = now()->subHour();

            $mock->shouldReceive('findByToken')
                ->once()
                ->andReturn($value);
        });

        $this->expectException(RefreshTokenException::class);

        (new AuthService)->refreshToken($token);
    });

    test('returns generated auth result when token valid', function () {
        $token = 'test';

        $refreshToken = new RefreshToken();
        $refreshToken->expire_at = now()->addHour();

        $user = $this->partialMock(User::class, function (MockInterface $mock) use ($refreshToken) {
            $mock->shouldReceive('createRefreshToken')
                ->andReturn($refreshToken);
        });

        $refreshToken->user = $user;

        $this->partialMock(RefreshTokenService::class, function (MockInterface $mock) use ($refreshToken) {
            $mock->shouldReceive('findByToken')
                ->once()
                ->andReturn($refreshToken);
        });

        $loginResult = ['user' => $user, 'access_token' => 'blah'];

        $this->mock(AuthResult::class, function (MockInterface $mock) use ($user, $loginResult) {
            $mock->shouldReceive('generateAuthResult')
                ->once()
                ->with($user)
                ->andReturn($loginResult);
        });

        $this->assertEquals((new AuthService)->refreshToken($token), $loginResult);
    });
});