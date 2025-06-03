<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class RefreshTokenController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $token = PersonalAccessToken::where('refresh_token', $request->refresh_token)
            ->where('refresh_token_expires_at', '>', now())
            ->first();

        if (!$token) {
            return response()->json([
                'message' => 'Invalid or expired refresh token'
            ], 401);
        }

        $user = User::find($token->tokenable_id);
        
        // Revoke the old token
        $token->delete();

        // Create new tokens
        $newToken = $user->createToken('auth-token');
        $refreshToken = Str::random(64);
        $refreshTokenExpiresAt = now()->addDays(30);

        $newToken->accessToken->update([
            'refresh_token' => $refreshToken,
            'refresh_token_expires_at' => $refreshTokenExpiresAt,
        ]);

        return response()->json([
            'access_token' => $newToken->plainTextToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600, // 1 hour
        ]);
    }
} 