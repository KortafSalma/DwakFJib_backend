<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pharmacy;
use App\Models\Distributor;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'sometimes|in:USER,PHARMACY,DISTRIBUTOR',
        ];

        if ($request->role === 'PHARMACY') {
            $rules['pharmacyAddress'] = 'required|string|max:500';
            $rules['authorizationType'] = 'required|string|max:100';
        }

        if ($request->role === 'DISTRIBUTOR') {
            $rules['companyName'] = 'required|string|max:255';
        }

        $request->validate($rules);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => $request->role ?? User::ROLE_USER,
            ]);

            if ($request->role === User::ROLE_PHARMACY) {
                Pharmacy::create([
                    'user_id' => $user->id,
                    'name' => $request->name,
                    'address' => $request->pharmacyAddress,
                    'authorization_type' => $request->authorizationType,
                ]);
            }

            if ($request->role === User::ROLE_DISTRIBUTOR) {
                Distributor::create([
                    'user_id' => $user->id,
                    'name' => $request->companyName,
                ]);
            }

            return $user;
        });

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        if ($user->banned_at || !$user->is_active) {
            return response()->json([
                'message' => 'Account is suspended'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user()->load(['pharmacy', 'distributor']));
    }
}
