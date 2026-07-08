<?php

use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery\MockInterface;
use Narakode\FineAuth\Auth\AuthResult;
use Workbench\App\Models\User;

pest()->only();

describe('generateAuthResult', function () {
    test('returns user access token', function () {
        $token = 'test';
        
        $user = $this->partialMock(User::class, function (MockInterface $mock) use ($token) {
            $mock->shouldReceive('createToken')
                ->once()
                ->with('api')
                ->andReturn(new NewAccessToken(new PersonalAccessToken(), $token));
        });

        $result = (new AuthResult)->generateAuthResult($user);

        $this->assertSame($token, $result['access_token']); 
    });
    
    test('returns current result', function () {
        $token = 'test';
        
        $user = $this->partialMock(User::class, function (MockInterface $mock) use ($token) {
            $mock->shouldReceive('createToken')
                ->once()
                ->with('api')
                ->andReturn(new NewAccessToken(new PersonalAccessToken(), $token));
        });

        $authResult = $this->partialMock(AuthResult::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('generateCurrentUserResult')
                ->once()
                ->with($user)
                ->andReturn(['test' => 'test']);
        });

        $result = $authResult->generateAuthResult($user);

        $this->assertSame('test', $result['test']); 
    });
});