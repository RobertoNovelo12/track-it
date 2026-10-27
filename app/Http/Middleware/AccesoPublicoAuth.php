<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Registro y recuperación de contraseña públicos (config auth.acceso_publico).
 * Apagados: la ruta responde 404 como si no existiera.
 */
class AccesoPublicoAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('auth.acceso_publico'), 404);

        return $next($request);
    }
}
