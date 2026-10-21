<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| Contraseña temporal
|--------------------------------------------------------------------------
|
| Si un administrador restableció la contraseña (debe_cambiar_password),
| el usuario solo puede elegir una nueva o cerrar sesión.
|
*/

class ExigirCambioContrasena
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (
            $usuario
            && (bool) ($usuario->debe_cambiar_password ?? false)
            && ! $request->routeIs('password.cambiar', 'password.cambiar.guardar', 'logout')
        ) {
            if ($request->expectsJson()) {
                abort(403, 'Debes cambiar tu contraseña temporal.');
            }

            return redirect()->route('password.cambiar');
        }

        return $next($request);
    }
}
