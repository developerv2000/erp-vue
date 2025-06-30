<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\EncryptHistoryMiddleware;
use Symfony\Component\HttpFoundation\Response;

class EncryptInertiaHistoryOnlyOnGetRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            App::environment('production') &&
            $request->isMethod('GET')
        ) {
            return app(EncryptHistoryMiddleware::class)->handle($request, $next);
        }

        return $next($request);
    }
}
