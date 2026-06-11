<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'data' => null,
                'errors' => ['authentication' => ['You must be logged in to access this resource']],
            ], 401);
        }

        if ($request->user()->banned_at || !$request->user()->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is suspended',
                'data' => null,
                'errors' => ['account' => ['Your account has been suspended']],
            ], 403);
        }

        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => null,
                'errors' => ['role' => ['You do not have permission to access this resource']],
            ], 403);
        }

        return $next($request);
    }
}
