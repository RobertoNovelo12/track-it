<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| 2FA obligatorio para administradores
|--------------------------------------------------------------------------
|
| Un administrador sin verificación en dos pasos solo puede entrar a
| Ajustes (para activarla) y cerrar sesión. Al activarla, todo vuelve a
| la normalidad. Los demás roles no cambian (2FA sigue siendo opcional).
|
*/

class ExigirDosFactoresAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (
            $usuario
            && method_exists($usuario, 'esAdministrador')
            && $usuario->esAdministrador()
            && ! $usuario->hasTwoFactorEnabled()
            && ! $request->routeIs('ajustes.*', 'logout', 'password.cambiar', 'password.cambiar.guardar')
        ) {
            if ($request->expectsJson()) {
                abort(403, 'Activa la verificación en dos pasos para continuar.');
            }

            return redirect()
                ->route('ajustes.index', ['section' => 'seguridad'])
                ->with('aviso_2fa_obligatorio', true);
        }

        return $next($request);
    }
}
