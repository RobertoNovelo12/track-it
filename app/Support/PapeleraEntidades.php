<?php

namespace App\Support;

/*
|--------------------------------------------------------------------------
| Qué se puede mover a la papelera y cómo
|--------------------------------------------------------------------------
|
| Cada tipo define:
|   tabla, llave   Registro principal.
|   nombre         Cómo se llama en pantalla («Marca», «Equipo»…).
|   etiqueta       Expresión SQL (alias «t») con el texto que se muestra.
|   permiso        Gate necesario para moverlo (gestionar / administrar).
|   bloqueos       [tabla, condición, «qué es»]: si hay registros vigentes
|                  que dependen de él, NO se puede borrar y se le dice al
|                  usuario qué cambiar primero.
|   ayuda          Qué hacer cuando está bloqueado.
|   hijos          [tabla, condición]: lo que le pertenece y se va con él a
|                  la papelera (y regresa al restaurar). En orden de
|                  restauración: primero lo que otros necesitan; al borrar
|                  se recorre al revés.
|   archivos       [tabla, columna]: rutas del disco «local» que se borran
|                  solo al eliminar definitivamente.
|
| Las condiciones usan «?» para el id del registro principal (una vez por
| cada «?»). Las sedes no tienen papelera, a propósito.
|
*/

class PapeleraEntidades
{
    public const DIAS = 30;

    private static ?array $memoria = null;

    public static function todas(): array
    {
        return self::$memoria ??= self::definir();
    }

    private static function definir(): array
    {
        $hotel = 'Hotel';

        try {
            $hotel = (string) (app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'] ?? 'Hotel');
        } catch (\Throwable) {
        }

        return [
            'equipo' => [
                'tabla' => 'equipos',
                'llave' => 'id_equipo',
                'nombre' => 'Equipo',
                'etiqueta' => "TRIM(CONCAT(t.codigo_inventario, ' — ', COALESCE(t.nombre_equipo, '')))",
                'permiso' => 'gestionar',
                'bloqueos' => [
                    // 4.º elemento: se puede resolver desde la papelera («Desasignar y mover»).
                    ['asignaciones', 'id_equipo = ? AND fecha_fin IS NULL', 'responsable asignado', 'desasignar'],
                ],
                'ayuda' => 'Devuelve el equipo (menú › Devolver) y vuelve a intentarlo.',
                // Las tablas de especificaciones (laptops, monitores…) se agregan solas: PapeleraService::hijos().
                'hijos' => [
                    ['atributos_equipo', 'id_equipo = ?'],
                    ['equipo_programa', 'id_equipo = ?'],
                    ['asignaciones', 'id_equipo = ?'],
                    ['movimientos', 'id_equipo = ?'],
                    ['mantenimientos', 'id_equipo = ?'],
                    ['mantenimiento_refacciones', 'id_mantenimiento IN (SELECT id_mantenimiento FROM mantenimientos WHERE id_equipo = ?)'],
                    ['bajas', 'id_equipo = ?'],
                    ['baja_evidencias', 'id_baja IN (SELECT id_baja FROM bajas WHERE id_equipo = ?)'],
                    ['evidencias', 'id_baja IN (SELECT id_baja FROM bajas WHERE id_equipo = ?) OR id_mantenimiento IN (SELECT id_mantenimiento FROM mantenimientos WHERE id_equipo = ?)'],
                    ['equipos_recibidos', 'id_equipo = ?'],
                ],
                'archivos' => [
                    ['bajas', 'ruta_pdf'],
                    ['baja_evidencias', 'foto_frontal'],
                    ['baja_evidencias', 'foto_trasera'],
                    ['baja_evidencias', 'foto_lateral_1'],
                    ['baja_evidencias', 'foto_lateral_2'],
                    ['evidencias', 'ruta_archivo'],
                ],
            ],

            'usuario' => [
                'tabla' => 'usuarios',
                'llave' => 'id_usuario',
                'nombre' => 'Usuario',
                'etiqueta' => "TRIM(CONCAT(t.nombres, ' ', t.apellido_paterno, ' (', t.username, ')'))",
                'permiso' => 'administrar',
                'bloqueos' => [
                    ['equipos', 'registrado_por = ?', 'equipo(s) registrados'],
                    ['asignaciones', 'asignado_por = ? OR id_usuario = ?', 'asignación(es)'],
                    ['movimientos', 'realizado_por = ?', 'movimiento(s)'],
                    ['mantenimientos', 'id_tecnico = ?', 'mantenimiento(s)'],
                    ['bajas', 'realizado_por = ?', 'baja(s)'],
                    ['evidencias', 'subido_por = ?', 'evidencia(s)'],
                    ['reportes_generados', 'id_usuario = ?', 'reporte(s) generados'],
                    ['bitacora_auditoria', 'id_usuario = ?', 'registro(s) en la bitácora'],
                    ['configuracion_organizacion', 'actualizado_por = ?', 'cambio(s) en la organización'],
                    ['equipos_recibidos', 'id_remitente = ?', 'envío(s) desde el teléfono'],
                    ['solicitudes_cambio_organizacion', 'revisado_por = ?', 'solicitud(es) revisadas'],
                    ['solicitudes_restablecimiento', 'id_atendio = ?', 'solicitud(es) atendidas'],
                ],
                'ayuda' => 'La cuenta ya tiene historial en el sistema: desactívala desde Seguridad en lugar de borrarla.',
                'hijos' => [
                    ['preferencias_usuario', 'id_usuario = ?'],
                    ['preferencias_notificaciones', 'id_usuario = ?'],
                    ['preferencias_seguridad', 'id_usuario = ?'],
                    ['notificaciones', 'id_usuario = ?'],
                    ['suscripciones_push', 'id_usuario = ?'],
                    ['equipos_recibidos', 'id_usuario = ?'],
                    ['solicitudes_cambio_organizacion', 'id_usuario = ?'],
                    ['solicitudes_restablecimiento', 'id_usuario = ?'],
                ],
            ],

            'mantenimiento' => [
                'tabla' => 'mantenimientos',
                'llave' => 'id_mantenimiento',
                'nombre' => 'Mantenimiento',
                'etiqueta' => "CONCAT(t.folio, ' · ', TO_CHAR(t.fecha_intervencion, 'DD/MM/YYYY'))",
                'permiso' => 'gestionar',
                'hijos' => [
                    ['mantenimiento_refacciones', 'id_mantenimiento = ?'],
                    ['evidencias', 'id_mantenimiento = ?'],
                ],
                'archivos' => [
                    ['evidencias', 'ruta_archivo'],
                ],
            ],

            'reporte' => [
                'tabla' => 'reportes_generados',
                'llave' => 'id_reporte',
                'nombre' => 'Reporte',
                'etiqueta' => "COALESCE(NULLIF(t.nombre_archivo, ''), CONCAT('Reporte ', t.tipo_reporte))",
                'permiso' => 'gestionar',
                'archivos' => [
                    ['reportes_generados', 'ruta_archivo'],
                ],
            ],

            'marca' => [
                'tabla' => 'marcas',
                'llave' => 'id_marca',
                'nombre' => 'Marca',
                'etiqueta' => 't.nombre',
                'permiso' => 'gestionar',
                'bloqueos' => [
                    ['modelos', 'id_marca = ?', 'modelo(s)'],
                    ['equipos', 'id_marca = ?', 'equipo(s)'],
                ],
                'ayuda' => 'Cambia esos equipos y modelos a otra marca, o bórralos primero.',
            ],

            'modelo' => [
                'tabla' => 'modelos',
                'llave' => 'id_modelo',
                'nombre' => 'Modelo',
                'etiqueta' => 't.nombre',
                'permiso' => 'gestionar',
                'bloqueos' => [
                    ['equipos', 'id_modelo = ?', 'equipo(s)'],
                ],
                'ayuda' => 'Cambia esos equipos a otro modelo, o bórralos primero.',
            ],

            'tipo_equipo' => [
                'tabla' => 'tipos_equipo',
                'llave' => 'id_tipo_equipo',
                'nombre' => 'Tipo de equipo',
                'etiqueta' => 't.nombre',
                'permiso' => 'gestionar',
                'bloqueos' => [
                    ['equipos', 'id_tipo_equipo = ?', 'equipo(s)'],
                    ['modelos', 'id_tipo_equipo = ?', 'modelo(s)'],
                ],
                'ayuda' => 'Cambia o borra primero los equipos y modelos de este tipo.',
                'hijos' => [
                    ['tipo_equipo_secciones', 'id_tipo_equipo = ?'],
                    ['tipo_equipo_campos', 'id_tipo_equipo = ?'],
                    ['tipo_equipo_campo_opciones', 'id_tipo_campo IN (SELECT id_tipo_campo FROM tipo_equipo_campos WHERE id_tipo_equipo = ?)'],
                    ['tipo_equipo_programa', 'id_tipo_equipo = ?'],
                ],
            ],

            'hotel' => [
                'tabla' => 'unidades_organizativas',
                'llave' => 'id_unidad',
                'nombre' => $hotel,
                'etiqueta' => 't.nombre',
                'permiso' => 'administrar',
                'bloqueos' => [
                    ['equipos', 'id_unidad = ?', 'equipo(s)'],
                    ['asignaciones', 'id_unidad = ?', 'asignación(es) en el historial'],
                    ['usuarios', 'id_unidad = ?', 'usuario(s)'],
                    ['ubicaciones', 'id_unidad = ?', 'ubicación(es)'],
                    ['modelos', 'id_unidad_uso_comun = ?', 'modelo(s) de uso común'],
                    ['preferencias_usuario', 'id_unidad_predeterminada = ?', 'usuario(s) que lo tienen como predeterminado'],
                ],
                'ayuda' => 'Cambia esos registros a otro '.mb_strtolower($hotel).' o bórralos primero.',
                'hijos' => [
                    ['departamento_unidad', 'id_unidad = ?'],
                ],
            ],

            'departamento' => [
                'tabla' => 'departamentos',
                'llave' => 'id_departamento',
                'nombre' => 'Departamento',
                'etiqueta' => 't.nombre',
                'permiso' => 'administrar',
                'bloqueos' => [
                    ['equipos', 'id_departamento = ?', 'equipo(s)'],
                    ['asignaciones', 'id_departamento = ?', 'asignación(es) en el historial'],
                    ['usuarios', 'id_departamento = ?', 'usuario(s)'],
                    ['departamentos', 'id_departamento_principal = ?', 'subdepartamento(s)'],
                ],
                'ayuda' => 'Cambia esos registros a otro departamento o bórralos primero.',
                'hijos' => [
                    ['departamento_unidad', 'id_departamento = ?'],
                    ['departamento_ubicacion', 'id_departamento = ?'],
                ],
            ],

            'ubicacion' => [
                'tabla' => 'ubicaciones',
                'llave' => 'id_ubicacion',
                'nombre' => 'Ubicación',
                'etiqueta' => 't.nombre',
                'permiso' => 'administrar',
                'bloqueos' => [
                    ['equipos', 'id_ubicacion = ?', 'equipo(s)'],
                    ['asignaciones', 'id_ubicacion = ?', 'asignación(es) en el historial'],
                ],
                'ayuda' => 'Cambia esos registros a otra ubicación o bórralos primero.',
                'hijos' => [
                    ['departamento_ubicacion', 'id_ubicacion = ?'],
                ],
            ],

            'tipo_ubicacion' => [
                'tabla' => 'tipos_ubicacion',
                'llave' => 'id_tipo_ubicacion',
                'nombre' => 'Tipo de ubicación',
                'etiqueta' => 't.nombre',
                'permiso' => 'administrar',
                'bloqueos' => [
                    ['ubicaciones', 'id_tipo_ubicacion = ?', 'ubicación(es)'],
                ],
                'ayuda' => 'Cambia esas ubicaciones a otro tipo o bórralas primero.',
            ],

            'proveedor' => [
                'tabla' => 'proveedores',
                'llave' => 'id_proveedor',
                'nombre' => 'Proveedor',
                'etiqueta' => 't.nombre',
                'permiso' => 'gestionar',
                'bloqueos' => [
                    ['equipos', 'id_proveedor = ?', 'equipo(s)'],
                    ['mantenimientos', 'id_proveedor = ?', 'mantenimiento(s)'],
                    ['marcas', 'id_proveedor_sugerido = ?', 'marca(s) que lo sugieren'],
                    ['modelos', 'id_proveedor_sugerido = ?', 'modelo(s) que lo sugieren'],
                ],
                'ayuda' => 'Cambia el proveedor en esos registros (o desactívalo para que ya no se ofrezca) y vuelve a intentarlo.',
                'hijos' => [],
            ],

            'programa' => [
                'tabla' => 'programas',
                'llave' => 'id_programa',
                'nombre' => 'Programa',
                'etiqueta' => "TRIM(CONCAT(t.nombre, ' ', COALESCE(t.version, '')))",
                'permiso' => 'gestionar',
                'bloqueos' => [
                    ['equipo_programa', 'id_programa = ?', 'equipo(s) lo tienen instalado'],
                ],
                'ayuda' => 'Quítalo de esos equipos (Editar equipo › Programas) y vuelve a intentarlo.',
                'hijos' => [
                    ['tipo_equipo_programa', 'id_programa = ?'],
                ],
            ],
        ];
    }

    public static function existe(string $tipo): bool
    {
        return array_key_exists($tipo, self::todas());
    }

    public static function de(string $tipo): array
    {
        $todas = self::todas();

        abort_unless(isset($todas[$tipo]), 404);

        return $todas[$tipo] + ['bloqueos' => [], 'hijos' => [], 'archivos' => [], 'ayuda' => null];
    }

    /*
     * Nombres para el filtro de la papelera: ['equipo' => 'Equipo', …].
     */
    public static function nombres(): array
    {
        return array_map(fn (array $d): string => $d['nombre'], self::todas());
    }

    /*
     * Opción «Mover a la papelera» para el menú de una tabla (x-data-table).
     * null si el usuario no tiene permiso: así array_filter la omite.
     */
    public static function accion(string $tipo, int|string $id): ?array
    {
        $definicion = self::todas()[$tipo] ?? null;

        if (! $definicion || ! \Illuminate\Support\Facades\Gate::allows($definicion['permiso'])) {
            return null;
        }

        return [
            'label' => 'Mover a la papelera',
            'js' => '$dispatch(\'papelera-mover\', '.\Illuminate\Support\Js::from(['tipo' => $tipo, 'id' => (int) $id]).')',
            'danger' => true,
        ];
    }
}
