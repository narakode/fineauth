<?php

namespace Narakode\FineAuth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
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

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'The provided credentials do not match our records.'
            ], 401);
        }

        $user = Auth::user();

        $user->refreshTokens()->delete();
        $user->refreshTokens()->create([
            'token' => Str::random()
        ]);

        return [
            'access_token' => $user->createToken('api')->plainTextToken,
            'user' => $user
        ];
    }
}