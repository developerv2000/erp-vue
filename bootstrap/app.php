<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Ensure API stateful requests are handled first (Sanctum + CSRF cookies)
        $middleware->statefulApi();

        // Apply web middleware stack
        $middleware->web(append: [
            // a) Custom CSRF handler: catch invalid CSRF tokens early
            \App\Http\Middleware\HandleInvalidCsrf::class,

            // b) Authentication-dependent logic (ensure user relations are loaded)
            \App\Http\Middleware\EnsureUserRelationsAreLoaded::class,

            // c) Locale validator / setter
            \App\Http\Middleware\ValidateLocale::class,

            // d) Inertia request handler (shared props, flash messages)
            \App\Http\Middleware\HandleInertiaRequests::class,

            // e) Custom Inertia GET-only encryption
            \App\Http\Middleware\EncryptInertiaHistoryOnlyOnGetRequests::class,

            // f) Preload asset link headers
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            // Handle server-side errors
            if (!app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), [500, 503, 404, 403])) {
                return Inertia::render('core/pages/ErrorPage', ['status' => $response->getStatusCode()])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
                // Handle expired CSRF redirection
            } elseif ($response->getStatusCode() === 419) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }

            return $response;
        });
    })->create();
