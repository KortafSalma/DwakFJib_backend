<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;

class AuditMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->user() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $route = $request->route()?->getName() ?? $request->route()?->getActionName();

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => strtolower($request->method()),
                'model_type' => 'request',
                'model_id' => 0,
                'old_values' => null,
                'new_values' => [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'route' => $route,
                    'input' => $request->except(['password', 'password_confirmation']),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }
}
