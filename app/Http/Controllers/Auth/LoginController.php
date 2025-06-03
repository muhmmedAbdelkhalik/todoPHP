<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke all existing tokens
        $user->tokens()->delete();

        // Create new token with refresh token
        $token = $user->createToken('auth-token');
        $refreshToken = Str::random(64);
        $refreshTokenExpiresAt = now()->addDays(30);

        $token->accessToken->update([
            'refresh_token' => $refreshToken,
            'refresh_token_expires_at' => $refreshTokenExpiresAt,
        ]);

        return response()->json([
            'access_token' => $token->plainTextToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600, // 1 hour
        ]);
    }
} 