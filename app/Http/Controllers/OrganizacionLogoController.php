<?php

namespace App\Http\Controllers;

use App\Services\OrganizacionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| Logotipo de la organización
|--------------------------------------------------------------------------
|
| Sirve el logotipo guardado en configuracion_organizacion.
|
| Es público porque también se muestra en las pantallas de acceso.
| La versión forma parte de la URL, por lo que el navegador puede
| conservarlo indefinidamente: al reemplazarlo cambia la URL.
|
*/

class OrganizacionLogoController extends Controller
{
    public function __invoke(
        Request $request,
        OrganizacionService $organizacion,
        string $version
    ): Response {
        // Misma respuesta para el logotipo principal y su versión de modo oscuro.
        $oscuro = $request->routeIs('organizacion.logo.oscuro');
        $ruta = $oscuro ? 'organizacion.logo.oscuro' : 'organizacion.logo';

        $logo = $organizacion->logoContenido($oscuro);

        if (! $logo) {
            return redirect()->to(
                asset(OrganizacionService::LOGO_PREDETERMINADO)
            );
        }

        if (! hash_equals($logo['version'], $version)) {
            return redirect()->route($ruta, [
                'version' => $logo['version'],
            ]);
        }

        $etag = '"'.$logo['version'].'"';

        $headers = [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'ETag' => $etag,
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304, $headers);
        }

        return response($logo['contenido'], 200, $headers + [
            'Content-Type' => $logo['mime'],
            'Content-Length' => (string) strlen($logo['contenido']),
        ]);
    }
}
