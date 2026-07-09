<?php

use Illuminate\Testing\Fluent\AssertableJson;
use Narakode\FineAuth\Auth\AuthContext;
use Narakode\FineAuth\Auth\AuthMeta;
use Workbench\App\Models\User;

test('returns unauthorized when refresh token cookie is empty', function () {
    $response = $this->withHeaders(['accept' => 'application/json'])
        ->post('/refresh-token');

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.'
        ]); 
});

test('returns unauthorized when refresh token cookie doesn\'t exists', function () {
    $response = $this->withHeaders(['accept' => 'application/json'])
        ->withCookie('refresh_token', 'test')
        ->post('/refresh-token');

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.'
        ]); 
});

test('returns unauthorized when refresh token cookie is expired', function () {
    $user = User::first();

    config()->set('fineauth.refresh_token_expiration', 60);

    $refreshToken = $user->createRefreshToken();

    $this->travel(3)->hours();

    $response = $this->withHeaders(['accept' => 'application/json'])
        ->withMiddleware()
        ->withCookie('refresh_token', $refreshToken->token)
        ->post('/refresh-token');

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.'
        ]); 
});

describe('when refresh token valid', function () {
    test('returns new access token and user ', function () {
        $user = User::first();

        $refreshToken = $user->createRefreshToken();

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->withMiddleware()
            ->withCookie('refresh_token', $refreshToken->token)
            ->post('/refresh-token');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->has('access_token')
                    ->has('user')
                    ->etc();
            });
    });  

    test('access token has expiration from config', function () {
        $this->freezeTime(function () {
            config()->set('fineauth.access_token_expiration', 60);

            $user = User::first();

            $refreshToken = $user->createRefreshToken();

            $this->withHeaders(['accept' => 'application/json'])
                ->withCookie('refresh_token', $refreshToken->token)
                ->post('/refresh-token')
                ->assertStatus(200);

            $token = $user->tokens()
                ->first();

            $this->assertNotNull($token->expires_at);
            $this->assertEquals($token->expires_at, now()->addMinutes(config('fineauth.access_token_expiration'))->copy()->startOfSecond());
        });
    });

    test('returns custom meta', function () {
        class CustomRefreshTokenMeta implements AuthMeta
        {
            public function toArray(AuthContext $auth): array
            {
                return [
                    'test' => 'test'
                ];
            }
        } 

        $this->app->singleton(AuthMeta::class, CustomRefreshTokenMeta::class);

        $user = User::first();

        $refreshToken = $user->createRefreshToken();

        $response = $this->withHeaders(['accept' => 'application/json'])
            ->withMiddleware()
            ->withCookie('refresh_token', $refreshToken->token)
            ->post('/refresh-token');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->has('access_token')
                    ->has('user')
                    ->has('meta.test')
                    ->etc();
            });
    });  
});