<?php

use Illuminate\Support\Facades\Cookie;
use Mockery\MockInterface;
use Narakode\FineAuth\Auth\Authenticator;
use Narakode\FineAuth\Auth\AuthResult;
use Narakode\FineAuth\Auth\AuthService;
use Narakode\FineAuth\Auth\Exceptions\LoginException;
use Narakode\FineAuth\RefreshToken\RefreshToken;
use Symfony\Component\HttpFoundation\Cookie as HttpFoundationCookie;
use Workbench\App\Models\User;

pest()->only();

describe('login', function () {
    test('throws LoginException on attempt fails', function () {
        $credentials = ['email' => 'test@example.com'];

        $this->partialMock(Authenticator::class, function (MockInterface $mock) use ($credentials) {
            $mock->shouldReceive('attempt')
                ->with($credentials)
                ->andReturnFalse(); 
        });

        $this->expectException(LoginException::class);
        $this->expectExceptionMessage('The provided credentials do not match our records.');

        (new AuthService(new AuthResult))->login($credentials);
    });

    test('should queue cookie on attempt success', function () {
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

            $authResult = $this->mock(AuthResult::class, function (MockInterface $mock) {
                $mock->shouldReceive('generateAuthResult');
            });

            (new AuthService($authResult))->login([]);
        });
    });

    test('should return generated auth result on attempt success', function () {
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

        $authResult = $this->mock(AuthResult::class, function (MockInterface $mock) use ($user, $loginResult) {
            $mock->shouldReceive('generateAuthResult')
                ->once()
                ->with($user)
                ->andReturn($loginResult);
        });

        $this->assertEquals((new AuthService($authResult))->login([]), $loginResult);
    });
});