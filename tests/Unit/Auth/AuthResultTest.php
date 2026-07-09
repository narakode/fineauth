<?php

use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery\MockInterface;
use Narakode\FineAuth\Auth\AuthContext;
use Narakode\FineAuth\Auth\AuthMeta;
use Narakode\FineAuth\Auth\AuthResult;
use Workbench\App\Models\User;

describe('generateAuthResult', function () {
    test('returns user access token', function () {
        $this->freezeTime(function () {
            config()->set('fineauth.access_token_expiration', 60);

            $token = 'test';
            
            $user = $this->partialMock(User::class, function (MockInterface $mock) use ($token) {
                $mock->shouldReceive('createToken')
                    ->once()
                    ->withArgs(function ($type, $abilities, $expiration) {
                        if ($type !== 'api') {
                            return false;
                        }

                        if (!now()->addMinutes(config('fineauth.access_token_expiration'))->isSameSecond($expiration)) {
                            return false;
                        }

                        return true;
                    })
                    ->andReturn(new NewAccessToken(new PersonalAccessToken(), $token));
            });

            $result = (new AuthResult)->generateAuthResult($user);

            $this->assertSame($token, $result['access_token']); 
        });
    });
    
    test('returns current result', function () {
        $token = 'test';
        
        $user = $this->partialMock(User::class, function (MockInterface $mock) use ($token) {
            $mock->shouldReceive('createToken')
                ->once()
                ->withAnyArgs()
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

describe('generateCurrentUserResult', function () {
    test('returns user', function () {
        $user = new User;
        $result = (new AuthResult)->generateCurrentUserResult($user);

        $this->assertSame($user, $result['user']); 
    });

    test('returns meta', function () {
        $user = new User;

        $meta = [
            'test' => 'test'
        ];
        $this->partialMock(AuthMeta::class, function (MockInterface $mock) use ($meta) {
            $mock->shouldReceive('toArray')
                ->once()
                ->with(Mockery::type(AuthContext::class))
                ->andReturn($meta);
        });

        $result = (new AuthResult)->generateCurrentUserResult($user);

        $this->assertSame($meta, $result['meta']); 
    });
});