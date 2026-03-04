<?php

use App\Http\Exceptions\Api\ErrorException as ApiErrorException;
use App\Http\Middleware\CheckRole;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        
        // // Fallback (500)
        $exceptions->render(function (Throwable $e, Request $request) {
            // Decide when Laravel should return JSON
            if (!$request->is('api/*')) {
                return null;
            }

            if ($e instanceof ApiErrorException) {
                return response()->json([
                    'code' => 400,
                    'message' => $e->getMessage(),
                    'trace' => config('app.debug')
                        ? collect($e->getTrace())->take(5)
                        : null,
                ], 400);
            } else if ($e instanceof ValidationException) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            else if($e instanceof ConnectException) {
                return response()->json([
                    'code' => 'STRAPI_CONNECTION_FAILED',
                    'message' => 'We got trouble when trying to connect Strapi',
                    'error' => $e->getMessage(),
                    'trace' => config('app.debug')
                        ? collect($e->getTrace())->take(5)
                        : null,
                ], 400);
            }

            return handleInternalServerError($e);
        });
    })->create();
