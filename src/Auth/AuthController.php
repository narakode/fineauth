<?php

namespace Narakode\FineAuth\Auth;

use Illuminate\Http\Request;
use Narakode\FineAuth\Auth\Exceptions\LoginException;
use Narakode\FineAuth\Auth\Exceptions\RefreshTokenException;

class AuthController
{
    public function login(Request $request, AuthCredentials $authCredentials, AuthService $authService)
    {
        $credentials = $request->validate($authCredentials->rules());

        try {
            return response()
                ->json($authService->login($credentials));
        } catch (LoginException $e) {
            return response()
                ->json(['message' => $e->getMessage()], 401);
        }
    }

    public function me(Request $request, AuthResult $authResult)
    {
        return $authResult->generateCurrentUserResult($request->user());
    }

    public function refreshToken(Request $request, AuthService $authService)
    {
        $rawToken = $request->cookie('refresh_token');

        abort_if(!$rawToken, 401, 'Unauthenticated.');

        try {
            return response()->json($authService->refreshToken($rawToken));
        } catch (RefreshTokenException $e) {
            return response()
                ->json(['message' => 'Unauthenticated.'], 401);
        }
    }
}