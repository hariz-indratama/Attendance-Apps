<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $deviceId = $request->header('X-Device-Id');
        $token = $user->createToken($deviceId ?? 'unknown-device');
        $expiration = config('sanctum.expiration');

        if ($expiration) {
            $token->accessToken->expires_at = now()->addMinutes($expiration);
            $token->accessToken->save();
        }

        if ($deviceId && $token->accessToken) {
            $token->accessToken->forceFill(['device_id' => $deviceId])->save();
        }

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
        ], 201);
    }

    /**
     * Login user and create token.
     */
    public function login(Request $request): JsonResponse
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

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is deactivated.'],
            ]);
        }

        $deviceId = $request->header('X-Device-Id');
        $token = $user->createToken($deviceId ?? 'unknown-device');
        $expiration = config('sanctum.expiration');

        if ($expiration) {
            $token->accessToken->expires_at = now()->addMinutes($expiration);
            $token->accessToken->save();
        }

        if ($deviceId && $token->accessToken) {
            $token->accessToken->forceFill(['device_id' => $deviceId])->save();
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load(['shift', 'location']),
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
        ]);
    }

    /**
     * Logout user and revoke token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get current user info.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load(['shift', 'location']),
        ]);
    }

    /**
     * Get current session info.
     */
    public function session(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user->currentAccessToken();

        return response()->json([
            'user' => $user->load(['shift', 'location']),
            'expires_at' => $token->expires_at?->toIso8601String(),
            'created_at' => $token->created_at?->toIso8601String(),
        ]);
    }

    /**
     * Refresh session token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        // Revoke current token
        $currentToken->delete();

        // Create new token
        $newToken = $user->createToken('auth-token');

        return response()->json([
            'message' => 'Session refreshed successfully',
            'expires_at' => $newToken->accessToken->expires_at?->toIso8601String(),
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'position' => 'sometimes|string|max:100',
            'department' => 'sometimes|string|max:100',
        ]);

        $request->user()->update($request->only([
            'name',
            'phone',
            'position',
            'department',
        ]));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $request->user()->fresh(['shift', 'location']),
        ]);
    }

    /**
     * Send password reset link to user email.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email',
            ]);
        }

        return response()->json([
            'message' => 'Unable to send reset link',
        ], 400);
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'token', 'password', 'password_confirmation'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password,
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password reset successfully',
            ]);
        }

        return response()->json([
            'message' => 'Invalid token or email',
        ], 400);
    }
}
