<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSession
{
    /*
    |--------------------------------------------------------------------------
    | Duración máxima absoluta de una sesión
    |--------------------------------------------------------------------------
    |
    | 24 horas = 86,400 segundos.
    |
    | Esto es independiente de SESSION_LIFETIME.
    |
    */

    private const MAX_SESSION_SECONDS = 86400;


    public function handle(
        Request $request,
        Closure $next
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | ¿Este navegador ya había tenido una sesión autenticada?
        |--------------------------------------------------------------------------
        |
        | Este cookie lo agregaremos al iniciar sesión correctamente.
        |
        | Nos permitirá distinguir:
        |
        | - Usuario que nunca inició sesión -> Login normal
        | - Usuario que perdió su sesión    -> Sesión caducada
        |
        */

        $hadAuthenticatedSession =
            $request->cookie('trackit_auth_session') === '1';


        /*
        |--------------------------------------------------------------------------
        | La sesión ya no existe
        |--------------------------------------------------------------------------
        */

        if (! Auth::check()) {

            if ($hadAuthenticatedSession) {

                return redirect()
                    ->route('session.expired');
            }

            /*
             * No había sesión anterior.
             *
             * Dejamos continuar para que el middleware "auth"
             * normal de Laravel lo mande al login.
             */

            return $next($request);
        }


        /*
        |--------------------------------------------------------------------------
        | Hora en que inició la sesión
        |--------------------------------------------------------------------------
        */

        $startedAt =
            $request->session()->get(
                'auth_started_at'
            );


        /*
         * Compatibilidad con sesiones creadas antes de instalar
         * esta funcionalidad.
         */

        if (! $startedAt) {

            $startedAt = now()->timestamp;

            $request->session()->put(
                'auth_started_at',
                $startedAt
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Máximo absoluto de 24 horas
        |--------------------------------------------------------------------------
        */

        $sessionAge =
            now()->timestamp - (int) $startedAt;


        if ($sessionAge >= self::MAX_SESSION_SECONDS) {

            /*
             * Cerramos completamente la sesión.
             */

            Auth::guard('web')->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();


            /*
             * No borramos trackit_auth_session.
             *
             * De esta forma sabemos que la persona sí tenía
             * una sesión anteriormente.
             */

            return redirect()
                ->route('session.expired');
        }


        /*
        |--------------------------------------------------------------------------
        | Sesión válida
        |--------------------------------------------------------------------------
        */

        return $next($request);
    }
}