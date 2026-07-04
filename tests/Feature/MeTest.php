<?php

use Workbench\App\Models\User;

test('returns unauthorized when access token is empty', function () {
    $response = $this->withHeaders(['accept' => 'application/json'])
        ->get('/me');

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.'
        ]);
});

test('returns unauthorized when access token is invalid', function () {
    $response = $this->withHeaders(['accept' => 'application/json', 'authorization' => 'test'])
        ->get('/me');

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.'
        ]);
});

test('returns current user when access token valid', function () {
    $user = User::first();

    $token = $user->createToken('api')->plainTextToken;

    $response = $this->withHeaders(['accept' => 'application/json', 'authorization' => 'Bearer ' . $token])
        ->get('/me');

    $response->assertStatus(200)
        ->assertJson([
            'user' => $user->toArray()
        ])
        ->assertJsonMissingPaths(['user.password']);
});