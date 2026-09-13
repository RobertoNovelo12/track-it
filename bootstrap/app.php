<?php

use App\Http\Middleware\EnsureActiveSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(
        function (Middleware $middleware): void {

            /*
            |--------------------------------------------------------------------------
            | Proxy
            |--------------------------------------------------------------------------
            */

            $middleware->trustProxies(
                at: '*',
                headers:
                    Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_HOST
                    | Request::HEADER_X_FORWARDED_PORT
                    | Request::HEADER_X_FORWARDED_PROTO
            );


            /*
            |--------------------------------------------------------------------------
            | Middleware personalizado
            |--------------------------------------------------------------------------
            |
            | Lo registramos con el alias:
            |
            | active.session
            |
            | Todavía no se ejecutará hasta que lo agreguemos
            | explícitamente a nuestras rutas protegidas.
            |
            */

            $middleware->alias([
                'active.session' =>
                    EnsureActiveSession::class,
            ]);

        }
    )

    ->withExceptions(
        function (Exceptions $exceptions): void {

            $exceptions->shouldRenderJsonWhen(
                fn (Request $request) =>
                    $request->is('api/*')
                    || $request->expectsJson(),
            );

        }
    )

    ->create();