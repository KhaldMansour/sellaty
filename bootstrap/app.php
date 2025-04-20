<?php

use App\Http\Middleware\JwtMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'JwtMiddleware' => JwtMiddleware::class,
            'SetLocale' => App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e, $request) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'status' => 'error',
                    'error' => 'Validation failed',
                    'data' => null,
                    'message' => $e->errors()
                ], 422);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json([
                    'status' => 'error',
                    'data' => null,
                    'error' => 'Resource not found.',
                    'message' => $e->getMessage()
                ], 404);
            }

            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'status' => 'error',
                    'data' => null,
                    'error' => $e->getMessage(),
                    'message' => $e->getMessage()
                ], $e->getStatusCode());
            }

            return response()->json([
                'status' => 'error',
                'data' => null,
                'error' => 'Something went wrong.',
                'message' => $e->getMessage()
            ], 500);
        });
    })
    ->create();
