<?php

use Illuminate\Support\Facades\Auth;
use Narakode\FineAuth\Auth\Default\Authenticator;
use Workbench\App\Models\User;

describe('attempt', function () {
   test('returns false when auth attempt fails', function () {
        Auth::expects('attempt')
            ->once()
            ->withAnyArgs()
            ->andReturnFalse();

        $this->assertFalse((new Authenticator)->attempt([]));
   });

   test('returns user when auth attempt success', function () {
        $user = new User;

        Auth::expects('attempt')
            ->once()
            ->withAnyArgs()
            ->andReturnTrue();
        
        Auth::expects('user')
            ->once()
            ->withAnyArgs()
            ->andReturn($user);

        $this->assertSame($user, (new Authenticator)->attempt([]));
   }); 
});