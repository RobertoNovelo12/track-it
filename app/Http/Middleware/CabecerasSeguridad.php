<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| Cabeceras de seguridad
|--------------------------------------------------------------------------
|
| - nosniff: el navegador no «adivina» tipos de archivo.
| - SAMEORIGIN: la app no se puede incrustar en un iframe de otro sitio
|   (evita clickjacking).
| - Referrer-Policy: no se envía la ruta completa a sitios externos.
| - Permissions-Policy: solo la cámara (escáner) en el propio sitio.
| - HSTS (solo producción con HTTPS): el navegador siempre usa HTTPS.
|
*/

class CabecerasSeguridad
{
    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        $cabeceras = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(self), microphone=(), geolocation=(), payment=(), usb=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        if ($request->isSecure() && app()->isProduction()) {
            $cabeceras['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($cabeceras as $nombre => $valor) {
            if (! $respuesta->headers->has($nombre)) {
                $respuesta->headers->set($nombre, $valor);
            }
        }

        return $respuesta;
    }
}
