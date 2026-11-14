<?php

namespace App\Http\Middleware;

use App\Services\RestablecerInstanciaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| Instancia recién restablecida
|--------------------------------------------------------------------------
|
| El usuario temporal («instalacion») solo puede usar el asistente de
| configuración inicial y cerrar sesión (App\Http\Controllers\InstanciaController).
|
*/

class ExigirConfiguracionInstancia
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            RestablecerInstanciaService::esUsuarioTemporal($request->user())
            && ! $request->routeIs('instancia.configurar', 'instancia.configurar.guardar', 'logout')
        ) {
            if ($request->expectsJson()) {
                abort(403, 'Termina la configuración inicial para continuar.');
            }

            return redirect()->route('instancia.configurar');
        }

        return $next($request);
    }
}
