<?php

use Illuminate\Testing\Fluent\AssertableJson;
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

test('returns new access token and user when success', function () {
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