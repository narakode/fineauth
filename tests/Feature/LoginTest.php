<?php

use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\Cookie;
use Workbench\App\Models\User;

test('login returns 422 error when credentials empty', function () {
    $response = $this->withHeaders(['accept' => 'application/json'])
        ->post('/login');

    $response->assertStatus(422)
        ->assertJson(function (AssertableJson $json) {
            $json->hasAll('message', 'errors.email', 'errors.password');
        });
});

test('login returns 401 error when email not found', function () {
    $credentials = [
        'email' => 'random@email.com',
        'password' => '3r}!<-F71Gy|'
    ];

    $response = $this->withHeaders(['accept' => 'application/json'])
        ->post('/login', $credentials);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'The provided credentials do not match our records.'
        ]);
});

describe('when login attempt success', function () {
    test('returns access token', function () {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'dcG&494hj.6k'
        ];

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->post('/login', $credentials);

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->has('access_token')->etc();
            });
    });

    test('returns user object', function () {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'dcG&494hj.6k'
        ];

        $user = User::first();

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->post('/login', $credentials);

        $response->assertStatus(200)
            ->assertJson([
                'user' => $user->toArray()
            ])
            ->assertJsonMissingPaths(['user.password']);
    });

    test('creates refresh token', function () {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'dcG&494hj.6k'
        ];

        $user = User::first();

        $this->withHeaders(['accept' => 'application/json'])
            ->post('/login', $credentials)
            ->assertStatus(200);

        $this->assertEquals($user->refreshTokens()->count(), 1);
    });

    test('set cookies for refresh token', function () {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'dcG&494hj.6k'
        ];

        $user = User::first();

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->post('/login', $credentials)
            ->assertStatus(200);

        $refreshToken = $user->refreshTokens()->first()->token;

        $response->assertCookie('refresh_token', $refreshToken);
    });

    test('refresh token has expiration', function () {
        $this->freezeTime(function () {
            $credentials = [
                'email' => 'test@example.com',
                'password' => 'dcG&494hj.6k'
            ];

            $user = User::first();

            $response = $this->withHeaders(['accept' => 'application/json'])
                ->post('/login', $credentials)
                ->assertStatus(200);

            $refreshTokenExpire = now()->addHour();
            $refreshToken = $user->refreshTokens()->first();

            $this->assertEquals($refreshToken->expire_at->copy()->startOfSecond(), $refreshTokenExpire->copy()->startOfSecond());

            $refreshTokenCookie = collect($response->headers->getCookies())
                ->first(function ($cookie) {
                    return $cookie->getName() === 'refresh_token';
                });

            $this->assertEquals($refreshTokenCookie->getExpiresTime(), $refreshTokenExpire->timestamp);
        });
    });

    test('refresh token cookie has secure configuration', function () {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'dcG&494hj.6k'
        ];

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->post('/login', $credentials)
            ->assertStatus(200);

        $refreshTokenCookie = collect($response->headers->getCookies())
            ->first(function ($cookie) {
                return $cookie->getName() === 'refresh_token';
            });

        $this->assertTrue($refreshTokenCookie->isSecure());
        $this->assertTrue($refreshTokenCookie->isHttpOnly());
        $this->assertEquals($refreshTokenCookie->getPath(), '/');
        $this->assertNull($refreshTokenCookie->getDomain());
        $this->assertEquals($refreshTokenCookie->getSameSite(), Cookie::SAMESITE_STRICT);
    });
});