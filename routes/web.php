<?php

use App\Http\Controllers\ReporteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\OrganizacionLogoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Inicio
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // Pantalla de inicio elegida en Ajustes › Preferencias.
    return auth()->check()
        ? redirect()->to(app(\App\Services\PreferenciasUsuarioService::class)->urlInicio())
        : redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| Logotipo de la organización
|--------------------------------------------------------------------------
|
| Público: también se muestra en inicio de sesión y registro.
|
*/

Route::get(
    '/organizacion/logo/{version}',
    OrganizacionLogoController::class
)
    ->where('version', '[A-Za-z0-9]{8,40}')
    ->name('organizacion.logo');


/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
*/

Route::middleware([
    'active.session',
    'auth',
    'password.temporal',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Búsqueda global de equipos
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Escáner del teléfono → bandeja «Equipos recibidos» (de la misma cuenta)
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/equipos/escaneo/{codigo}',
        [\App\Http\Controllers\EquiposRecibidosController::class, 'buscar']
    )
        // Cualquier texto: también números de serie (con puntos, barras, espacios…).
        ->where('codigo', '.{1,100}')
        ->name('equipos.escaneo.buscar');

    Route::post(
        '/equipos/recibidos',
        [\App\Http\Controllers\EquiposRecibidosController::class, 'enviar']
    )->name('equipos.recibidos.enviar');

    /*
    |--------------------------------------------------------------------------
    | Código de barras de un equipo (SVG)
    |--------------------------------------------------------------------------
    |
    | Solo el número del código de inventario (ACT-000061 → 000061).
    | Siempre es el mismo para un número: el navegador lo guarda en caché.
    | ?descargar=1 lo baja como archivo (ACT-000061.svg).
    |
    */
    Route::get(
        '/equipos/codigo-barras/{numero}.svg',
        function (string $numero) {
            // ?alto= solo cambia la altura; el ancho de las barras (2 px por
            // módulo) se mantiene para que siempre se pueda leer.
            $alto = max(24, min(120, (int) request()->query('alto', 60)));

            $svg = \App\Support\CodigoBarras::svg($numero, $alto);

            $respuesta = response($svg, 200, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'private, max-age=31536000, immutable',
                'X-Content-Type-Options' => 'nosniff',
            ]);

            if (request()->boolean('descargar')) {
                $respuesta->header('Content-Disposition', 'attachment; filename="ACT-' . $numero . '.svg"');
            }

            return $respuesta;
        }
    )
        ->where('numero', '[0-9]{1,40}')
        ->name('equipos.codigo-barras');

    /*
     * La búsqueda ahora se muestra en la tabla de Equipos tecnológicos.
     * Se conserva la ruta para enlaces anteriores (/buscar-equipos?q=...).
     */
    Route::get(
        '/buscar-equipos',
        fn () => redirect()->route(
            'equipos.index',
            array_filter(['buscar' => trim((string) request('q', ''))])
        )
    )->name('equipos.search');


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Equipos tecnológicos
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/equipos',
        [EquipoController::class, 'index']
    )->name('equipos.index');


    /*
    |--------------------------------------------------------------------------
    | Crear equipo
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/equipos/crear',
        fn () => view('equipos.create')
    )->name('equipos.create')->middleware('can:gestionar');


    /*
    |--------------------------------------------------------------------------
    | Ver detalle de equipo
    |--------------------------------------------------------------------------
    */

    Route::get('/equipos/{equipo}/exportar', [EquipoController::class, 'exportar'])
        ->whereNumber('equipo')->name('equipos.ficha');

    Route::get(
        '/equipos/{equipo}',
        function (int $equipo) {
            return view('equipos.show', [
                'equipoId' => $equipo,
            ]);
        }
    )
        ->whereNumber('equipo')
        ->name('equipos.show');


    /*
    |--------------------------------------------------------------------------
    | Editar equipo
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/equipos/{equipo}/editar',
        function (int $equipo) {
            return view('equipos.edit', [
                'equipoId' => $equipo,
            ]);
        }
    )
        ->whereNumber('equipo')
        ->name('equipos.edit');

    /*
    |--------------------------------------------------------------------------
    | Dar de baja equipo
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/equipos/{equipo}/baja',
        function (int $equipo) {
            return view('equipos.baja', [
                'equipoId' => $equipo,
            ]);
        }
    )
        ->whereNumber('equipo')
        ->name('equipos.baja')->middleware('can:gestionar');


    /*
    |--------------------------------------------------------------------------
    | Mantenimientos
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/mantenimientos',
        fn () => view('mantenimientos.index')
    )->name('mantenimientos.index')->middleware('can:gestionar');

    Route::get(
        '/mantenimientos/crear',
        fn () => view('mantenimientos.create')
    )->name('mantenimientos.create')->middleware('can:gestionar');

    Route::get(
        '/mantenimientos/{mantenimiento}/editar',
        function (int $mantenimiento) {
            return view('mantenimientos.edit', [
                'mantenimientoId' => $mantenimiento,
            ]);
        }
    )
        ->whereNumber('mantenimiento')
        ->name('mantenimientos.edit')->middleware('can:gestionar');


    /*
    |--------------------------------------------------------------------------
    | Generación de reportes
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/reportes',
        fn () => view('reportes.index')
    )->name('reportes.index')->middleware('can:gestionar');

    Route::get(
        '/reportes/{reporte}',
        [ReporteController::class, 'show']
    )
        ->whereNumber('reporte')
        ->name('reportes.show')->middleware('can:gestionar');

    Route::get(
        '/reportes/{reporte}/descargar',
        [ReporteController::class, 'download']
    )
        ->whereNumber('reporte')
        ->name('reportes.download')->middleware('can:gestionar');


    /*
    |--------------------------------------------------------------------------
    | Asignaciones y movimientos
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/asignaciones',
        fn () => view('asignaciones.index')
    )->name('asignaciones.index')->middleware('can:gestionar');


    /*
    |--------------------------------------------------------------------------
    | Asignar equipo
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/asignaciones/asignar',
        fn () => view('asignaciones.create')
    )->name('asignaciones.create')->middleware('can:gestionar');

    /*
    |--------------------------------------------------------------------------
    | Reasignar equipo
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/asignaciones/reasignar',
        fn () => view('asignaciones.reassign')
    )->name('asignaciones.reassign')->middleware('can:gestionar');

    /*
    |--------------------------------------------------------------------------
    | Historial de asignaciones y movimientos
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/asignaciones/historial',
        fn () => view('asignaciones.history')
    )->name('asignaciones.history')->middleware('can:gestionar');


    /*
    |--------------------------------------------------------------------------
    | Catálogos base
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/catalogos',
        fn () => view('catalogos.index')
    )->name('catalogos.index')->middleware('can:gestionar');


    /*
    |--------------------------------------------------------------------------
    | Editar configuración de tipo de equipo
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/catalogos/tipos-equipo/{tipoEquipo}/editar',
        function (int $tipoEquipo) {
            return view(
                'catalogos.tipos-equipo.edit',
                [
                    'tipoEquipoId' =>
                        $tipoEquipo,
                ]
            );
        }
    )
        ->whereNumber(
            'tipoEquipo'
        )
        ->name('catalogos.tipos-equipo.edit')->middleware('can:gestionar');


    /*
    |--------------------------------------------------------------------------
    | Crear marca o modelo
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/catalogos/crear',
        fn () => view('catalogos.create')
    )->name('catalogos.create')->middleware('can:gestionar');

    Route::get(
    '/catalogos/hoteles/{hotel}/editar',
    function (int $hotel) {
        return view('catalogos.hoteles.edit', [
            'hotelId' => $hotel,
        ]);
    }
)
    ->whereNumber('hotel')
    ->name('catalogos.hoteles.edit')->middleware('can:administrar');

    Route::get(
        '/catalogos/sedes/crear',
        fn () => view('catalogos.sedes.create')
    )->name('catalogos.sedes.create')->middleware('can:administrar');

    Route::get(
        '/catalogos/sedes/{sede}/editar',
        function (int $sede) {
            return view('catalogos.sedes.edit', [
                'sedeId' => $sede,
            ]);
        }
    )
        ->whereNumber('sede')
        ->name('catalogos.sedes.edit')->middleware('can:administrar');


    /*
    |--------------------------------------------------------------------------
    | Editar marca o modelo
    |--------------------------------------------------------------------------
    */

    /*
     * Ubicaciones y tipos de ubicación: agregar y editar en páginas propias.
     */
    Route::get(
        '/catalogos/ubicaciones/nueva',
        fn () => view('catalogos.ubicaciones.create')
    )->name('catalogos.ubicaciones.create')->middleware('can:administrar');

    Route::get(
        '/catalogos/ubicaciones/{ubicacion}/editar',
        fn (int $ubicacion) => view('catalogos.ubicaciones.edit', ['ubicacionId' => $ubicacion])
    )->whereNumber('ubicacion')->name('catalogos.ubicaciones.edit')->middleware('can:administrar');

    Route::get(
        '/catalogos/ubicaciones/tipos',
        fn () => view('catalogos.ubicaciones.tipos.index')
    )->name('catalogos.tipos-ubicacion.index')->middleware('can:administrar');

    Route::get(
        '/catalogos/ubicaciones/tipos/nuevo',
        fn () => view('catalogos.ubicaciones.tipos.create')
    )->name('catalogos.tipos-ubicacion.create')->middleware('can:administrar');

    Route::get(
        '/catalogos/ubicaciones/tipos/{tipo}/editar',
        fn (int $tipo) => view('catalogos.ubicaciones.tipos.edit', ['tipoId' => $tipo])
    )->whereNumber('tipo')->name('catalogos.tipos-ubicacion.edit')->middleware('can:administrar');

    Route::get(
        '/catalogos/departamentos/nuevo',
        fn () => view('catalogos.departamentos.create')
    )->name('catalogos.departamentos.create')->middleware('can:administrar');

    Route::get(
        '/catalogos/departamentos/{departamento}/editar',
        function (int $departamento) {
            return view('catalogos.departamentos.edit', [
                'departamentoId' => $departamento,
            ]);
        }
    )
        ->whereNumber('departamento')
        ->name('catalogos.departamentos.edit')->middleware('can:administrar');

    Route::get(
        '/catalogos/{tipo}/{registro}/editar',
        function (
            string $tipo,
            int $registro
        ) {
            return view(
                'catalogos.edit',
                [
                    'tipo' =>
                        $tipo,

                    'registroId' =>
                        $registro,
                ]
            );
        }
    )
        ->whereIn(
            'tipo',
            [
                'marca',
                'modelo',
            ]
        )
        ->whereNumber(
            'registro'
        )
        ->name('catalogos.edit')->middleware('can:gestionar');


    /*
    |--------------------------------------------------------------------------
    | Seguridad y roles
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Guía de componentes de carga (UX)
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/ui/componentes',
        fn () => view('ui.componentes')
    )->name('ui.componentes')->middleware('can:administrar');


    /*
    |--------------------------------------------------------------------------
    | Tema desde el menú (se guarda en Ajustes › Preferencias)
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/preferencias/tema',
        function (\Illuminate\Http\Request $request) {
            $datos = $request->validate([
                'tema' => ['required', 'in:light,dark,system'],
            ]);

            app(\App\Services\PreferenciasUsuarioService::class)
                ->guardar((int) auth()->id(), ['tema' => $datos['tema']]);

            return response()->json(['ok' => true]);
        }
    )->name('preferencias.tema');


    Route::get(
        '/seguridad',
        fn () => view('seguridad.index')
    )->name('usuarios.index')->middleware('can:administrar');

    Route::get(
        '/seguridad/usuarios/nuevo',
        fn () => view('seguridad.usuarios.create')
    )->name('usuarios.create')->middleware('can:administrar');


    /*
    |--------------------------------------------------------------------------
    | Ajustes
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/ajustes',
        fn () => view('ajustes.index')
    )->name('ajustes.index');
});


/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';