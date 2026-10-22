<?php

namespace App\Support;

use App\Services\PlantillaInstalacionService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ENCABEZADO DE PÁGINA (barra superior)
|--------------------------------------------------------------------------
|
| Título del módulo y ruta de navegación según la ruta actual.
| Lo muestra layouts/app: en escritorio dentro de la barra superior
| y en móvil arriba del contenido.
|
| Formato:
|   [
|       'titulo' => 'Inventario',
|       'migas'  => [
|           ['label' => 'Inicio', 'href' => '/'],
|           ['label' => 'Inventario', 'href' => '/equipos'],
|           ['label' => 'Editar', 'href' => null],   // pantalla actual
|       ],
|   ]
|
| Para agregar una pantalla nueva basta con añadir su ruta al match().
|
*/

class Encabezado
{
    public static function actual(): ?array
    {
        $ruta = request()->route();
        $nombre = $ruta?->getName();

        if (! $nombre) {
            return null;
        }

        $parametro = fn (string $clave) => $ruta->parameter($clave);

        [$titulo, $migas] = match ($nombre) {
            'dashboard' => ['Vista general', []],

            // Inventario
            'equipos.index' => ['Inventario', [
                self::miga('Inventario'),
            ]],
            'equipos.create' => ['Inventario', [
                self::miga('Inventario', 'equipos.index'),
                self::miga('Nuevo equipo'),
            ]],
            'equipos.show' => ['Inventario', [
                self::miga('Inventario', 'equipos.index'),
                self::miga('Detalle'),
            ]],
            'equipos.edit' => ['Inventario', [
                self::miga('Inventario', 'equipos.index'),
                self::miga('Detalle', 'equipos.show', [$parametro('equipo')]),
                self::miga('Editar'),
            ]],
            'equipos.baja' => ['Inventario', [
                self::miga('Inventario', 'equipos.index'),
                self::miga('Detalle', 'equipos.show', [$parametro('equipo')]),
                self::miga('Editar', 'equipos.edit', [$parametro('equipo')]),
                self::miga('Dar de baja'),
            ]],

            // Asignación / Movimientos
            'asignaciones.index' => ['Asignación y movimientos', [
                self::miga('Asignación y movimientos'),
            ]],
            'asignaciones.create' => ['Asignación y movimientos', [
                self::miga('Asignación y movimientos', 'asignaciones.index'),
                self::miga('Asignar'),
            ]],
            'asignaciones.reassign' => ['Asignación y movimientos', [
                self::miga('Asignación y movimientos', 'asignaciones.index'),
                self::miga('Reasignar'),
            ]],
            'asignaciones.history' => ['Asignación y movimientos', [
                self::miga('Asignación y movimientos', 'asignaciones.index'),
                self::miga('Historial'),
            ]],

            // Mantenimientos
            'mantenimientos.index' => ['Mantenimientos', [
                self::miga('Mantenimientos'),
            ]],
            'mantenimientos.create' => ['Mantenimientos', [
                self::miga('Mantenimientos', 'mantenimientos.index'),
                self::miga('Nuevo mantenimiento'),
            ]],
            'mantenimientos.edit' => ['Mantenimientos', [
                self::miga('Mantenimientos', 'mantenimientos.index'),
                self::miga('Editar'),
            ]],

            // Reportes
            'reportes.index' => ['Reportes', [
                self::miga('Reportes'),
            ]],

            // Seguridad
            'usuarios.index' => ['Seguridad y roles', [
                self::miga('Seguridad y roles'),
            ]],
            'usuarios.create' => ['Seguridad y roles', [
                self::miga('Seguridad y roles', 'usuarios.index'),
                self::miga('Nuevo usuario'),
            ]],

            // Administración (la sección activa la agrega la propia pantalla)
            'catalogos.index' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
            ]],
            'catalogos.create' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
                self::miga('Marcas y modelos', 'catalogos.index', ['section' => 'marcas']),
                self::miga('Nuevo registro'),
            ]],
            'catalogos.edit' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
                $parametro('tipo') === 'marca'
                    ? self::miga('Marcas', 'catalogos.index', ['section' => 'marcas'])
                    : self::miga('Modelos', 'catalogos.index', ['section' => 'modelos']),
                self::miga('Editar'),
            ]],
            'catalogos.tipos-equipo.edit' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
                self::miga('Tipos de equipo', 'catalogos.index', ['section' => 'equipos']),
                self::miga('Editar'),
            ]],
            'catalogos.hoteles.edit' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
                self::miga(self::etiqueta('unidad_plural', 'Hoteles'), 'catalogos.index', ['section' => 'hoteles']),
                self::miga('Editar'),
            ]],
            'catalogos.sedes.create' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
                self::miga('Sedes', 'catalogos.index', ['section' => 'organizacion', 'tab' => 'sedes']),
                self::miga('Nueva'),
            ]],
            'catalogos.sedes.edit' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
                self::miga('Sedes', 'catalogos.index', ['section' => 'organizacion', 'tab' => 'sedes']),
                self::miga('Editar'),
            ]],
            'catalogos.ubicaciones.create' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
                self::miga('Ubicaciones', 'catalogos.index', ['section' => 'ubicaciones']),
                self::miga('Nueva'),
            ]],
            'catalogos.ubicaciones.edit' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
                self::miga('Ubicaciones', 'catalogos.index', ['section' => 'ubicaciones']),
                self::miga('Editar'),
            ]],
            'catalogos.tipos-ubicacion.index' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
                self::miga('Ubicaciones', 'catalogos.index', ['section' => 'ubicaciones']),
                self::miga('Tipos de ubicación'),
            ]],
            'catalogos.tipos-ubicacion.create' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
                self::miga('Ubicaciones', 'catalogos.index', ['section' => 'ubicaciones']),
                self::miga('Tipos de ubicación', 'catalogos.tipos-ubicacion.index'),
                self::miga('Nuevo'),
            ]],
            'catalogos.tipos-ubicacion.edit' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
                self::miga('Ubicaciones', 'catalogos.index', ['section' => 'ubicaciones']),
                self::miga('Tipos de ubicación', 'catalogos.tipos-ubicacion.index'),
                self::miga('Editar'),
            ]],
            'catalogos.departamentos.create' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
                self::miga('Departamentos', 'catalogos.index', ['section' => 'departamentos']),
                self::miga('Nuevo'),
            ]],
            'catalogos.departamentos.edit' => ['Administración', [
                self::miga('Administración', 'catalogos.index'),
                self::miga('Departamentos', 'catalogos.index', ['section' => 'departamentos']),
                self::miga('Editar'),
            ]],

            // La pestaña activa (Cuenta, Seguridad…) la agrega la propia pantalla.
            'ajustes.index' => ['Ajustes', [
                self::miga('Ajustes', 'ajustes.index'),
            ]],

            'ui.componentes' => ['Componentes', [
                self::miga('Componentes'),
            ]],

            default => [null, []],
        };

        if ($titulo === null) {
            return null;
        }

        // «Inicio» lleva a la pantalla de inicio elegida en Preferencias.
        $inicio = $nombre === 'dashboard' && $migas === []
            ? ['label' => 'Inicio', 'href' => null]
            : ['label' => 'Inicio', 'href' => url('/')];

        return [
            'titulo' => $titulo,
            'migas' => array_merge([$inicio], $migas),
        ];
    }

    private static function miga(string $label, ?string $ruta = null, array $parametros = []): array
    {
        $href = null;

        if ($ruta && Route::has($ruta)) {
            try {
                $href = route($ruta, $parametros);
            } catch (\Throwable $e) {
                $href = null;
            }
        }

        return ['label' => $label, 'href' => $href];
    }

    private static function etiqueta(string $clave, string $respaldo): string
    {
        try {
            return (string) (app(PlantillaInstalacionService::class)->etiquetas()[$clave] ?? $respaldo);
        } catch (\Throwable $e) {
            return $respaldo;
        }
    }
}
