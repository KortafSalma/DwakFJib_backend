<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationPreference;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthService
{
    public static function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? User::ROLE_USER,
            ]);

            NotificationPreference::create([
                'user_id' => $user->id,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user->load(['pharmacy', 'distributor', 'notificationPreference']),
                'token' => $token,
            ];
        });
    }

    public static function login(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if ($user->isBanned()) {
            throw new \RuntimeException('Your account has been banned. Reason: ' . ($user->ban_reason ?? 'No reason provided'));
        }

        if (!$user->is_active) {
            throw new \RuntimeException('Your account is not active. Please contact support.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load(['pharmacy', 'distributor', 'notificationPreference']),
            'token' => $token,
        ];
    }

    public static function logout(User $user, ?string $tokenId = null): void
    {
        if ($tokenId) {
            $user->tokens()->where('id', $tokenId)->delete();
        } else {
            $user->currentAccessToken()->delete();
        }
    }

    public static function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    public static function generateResetToken(string $email): ?string
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return null;
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        return $token;
    }

    public static function verifyResetToken(string $email, string $token): bool
    {
        $reset = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$reset) {
            return false;
        }

        if (Hash::check($token, $reset->token)) {
            $createdAt = \Carbon\Carbon::parse($reset->created_at);
            return $createdAt->addHour()->isFuture();
        }

        return false;
    }

    public static function resetPassword(string $email, string $token, string $newPassword): bool
    {
        if (!self::verifyResetToken($email, $token)) {
            return false;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        self::revokeAllTokens($user);

        return true;
    }
}
