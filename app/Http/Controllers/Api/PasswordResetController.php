<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Account is suspended'
            ], 403);
        }

        $token = Password::createToken($user);

        $resetUrl = config('app.frontend_url') . "/reset-password?token={$token}&email={$request->email}";

        return response()->json([
            'message' => 'Password reset link sent',
            'reset_url' => $resetUrl,
            'token' => $token,
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Account is suspended'
            ], 403);
        }

        $token = Password::createToken($user);

        // In production, send this via email
        $resetUrl = config('app.frontend_url') . "/reset-password?token={$token}&email={$request->email}";

        return response()->json([
            'message' => 'If the email exists, a password reset link has been sent.',
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid email'
            ], 400);
        }

        if (!Password::tokenExists($user, $request->token)) {
            return response()->json([
                'message' => 'Invalid or expired token'
            ], 400);
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        Password::deleteToken($user);

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password reset successfully. Please login again.'
        ]);
    }

    public function verifyToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Password::tokenExists($user, $request->token)) {
            return response()->json([
                'message' => 'Invalid or expired token'
            ], 400);
        }

        return response()->json([
            'message' => 'Token is valid',
            'email' => $request->email,
        ]);
    }
}
