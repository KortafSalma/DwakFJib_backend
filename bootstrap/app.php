<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'audit' => \App\Http\Middleware\AuditMiddleware::class,
        ]);

        $middleware->api(prepend: \Illuminate\Routing\Middleware\ThrottleRequests::class . ':60,1');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'data' => null,
                'errors' => ['authentication' => ['You must be logged in to access this resource']],
            ], 401);
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'data' => null,
                'errors' => ['not_found' => ['The requested resource was not found']],
            ], 404);
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found',
                'data' => null,
                'errors' => ['not_found' => ['The requested route was not found']],
            ], 404);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed',
                'data' => null,
                'errors' => ['method_not_allowed' => ['The HTTP method is not allowed for this route']],
            ], 405);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'HTTP error',
                    'data' => null,
                    'errors' => ['error' => [$e->getMessage() ?: 'An error occurred']],
                ], $e->getStatusCode());
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                    'data' => null,
                    'errors' => config('app.debug') ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : ['server' => ['An unexpected error occurred. Please try again later.']],
                ], 500);
            }
        });
    })->create();
