<?php

namespace Narakode\FineAuth\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Narakode\FineAuth\RefreshToken\RefreshToken;

class AuthController
{
    public function login(Request $request, AuthService $authService, AuthResponse $authResponse)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => [
                'required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ]);

        abort_if(!Auth::attempt($credentials), 401, 'The provided credentials do not match our records.');

        $user = Auth::user();

        $refreshToken = $user->createRefreshToken();

        return response()
            ->json($authResponse->toArray($authService->authenticate($user)))
            ->withCookie(cookie(
                name: 'refresh_token',
                value: $refreshToken->token,
                minutes: now()->diffInMinutes($refreshToken->expire_at),
                path: '/',
                domain: null,
                secure: true,
                httpOnly: true,
                sameSite: 'Strict'
            ));
    }

    public function me(Request $request)
    {
        return [
            'user' => $request->user()
        ];
    }

    public function refreshToken(Request $request, AuthService $authService, AuthResponse $authResponse)
    {
        $rawToken = $request->cookie('refresh_token');

        abort_if(!$rawToken, 401, 'Unauthenticated.');

        $refreshToken = RefreshToken::with('user')
            ->firstWhere('token', $rawToken);

        abort_if(!$refreshToken, 401, 'Unauthenticated.');
        abort_if($refreshToken->expire_at->lessThan(now()), 401, 'Unauthenticated.');

        return response()->json($authResponse->toArray($authService->authenticate($refreshToken->user)));
    }
}