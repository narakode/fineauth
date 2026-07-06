<?php

use Narakode\FineAuth\Auth\AuthContext;
use Narakode\FineAuth\Auth\AuthMeta;
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

describe('when access token valid', function () {
    test('returns current user', function () {
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

    test('returns custom meta', function () {
        class CustomMeMeta implements AuthMeta
        {
            public function toArray(AuthContext $auth): array
            {
                return [
                    'test' => 'test'
                ];
            }
        } 

        $this->app->singleton(AuthMeta::class, CustomMeMeta::class);
        
        $user = User::first();

        $token = $user->createToken('api')->plainTextToken;

        $response = $this->withHeaders(['accept' => 'application/json', 'authorization' => 'Bearer ' . $token])
            ->get('/me');

        $response->assertStatus(200)
            ->assertJson([
                'user' => $user->toArray(),
                'meta' => [
                    'test' => 'test'
                ]
            ])
            ->assertJsonMissingPaths(['user.password']);
    });
});