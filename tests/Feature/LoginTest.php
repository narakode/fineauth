<?php

use Illuminate\Testing\Fluent\AssertableJson;
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

test('login returns access token when attempt success', function () {
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

test('login returns user object when attempt success', function () {
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