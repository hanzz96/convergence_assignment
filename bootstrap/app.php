<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

function handleInternalServerError(Throwable $e)
{
    return response()->json([
        'code' => 500,
        'message' => 'Internal server error',
        'error' => config('app.debug') ? $e->getMessage() : null,
        'trace' => config('app.debug') ? collect($e->getTrace())->take(5) : null,
    ], 500);
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {

        // Decide when Laravel should return JSON
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // Validation (422)
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null; // let Laravel handle web response
            }

            return response()->json([
                'code' => 422,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        });

        // Not Found (404)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'code' => 404,
                'message' => 'Http not found',
            ], 404);
        });

        // General Exception (400)
        $exceptions->render(function (Exception $e, Request $request) {

            if (! $request->expectsJson()) {
                return null;
            }

            if ($e instanceof ErrorException) {
                return handleInternalServerError($e);
            }

            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
                'trace' => config('app.debug')
                    ? collect($e->getTrace())->take(5)
                    : null,
            ], 400);
        });

        // Fallback (500)
        $exceptions->render(function (Throwable $e, Request $request) {

            if (! $request->expectsJson()) {
                return null;
            }

            return handleInternalServerError($e);
        });
    })->create();
