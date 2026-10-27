<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| Devolución de equipos (desasignación)
|--------------------------------------------------------------------------
|
| Cierra la asignación activa de un equipo sin abrir otra:
| 1. asignaciones.fecha_fin = fecha de devolución.
| 2. movimientos: tipo DEVOLUCION, asignación anterior, sin nueva.
| 3. El equipo vuelve al estado «Disponible» (si existe ese estado).
| 4. Bitácora, notificación y cachés.
|
| Se usa desde la página Devolver (Asignaciones) y automáticamente al
| dar de baja la cuenta de un colaborador (Seguridad y roles).
|
*/

class DevolucionService
{
    public const MOTIVOS = [
        'baja_colaborador' => 'Baja del colaborador',
        'cambio_puesto' => 'Cambio de puesto o de hotel',
        'fin_prestamo' => 'Fin de préstamo o proyecto',
        'equipo_danado' => 'Equipo dañado o con falla',
        'reemplazo' => 'Reemplazo por otro equipo',
        'otro' => 'Otro motivo',
    ];

    /*
    |--------------------------------------------------------------------------
    | Consultas
    |--------------------------------------------------------------------------
    */

    public function disponible(): bool
    {
        return $this->idTipoMovimiento() !== null;
    }

    /*
     * Asignación vigente con los datos para mostrar y para el acta.
     */
    public function asignacionActiva(int $idEquipo): ?array
    {
        $fila = DB::table('asignaciones as a')
            ->join('equipos as e', 'e.id_equipo', '=', 'a.id_equipo')
            ->leftJoin('tipos_equipo as te', 'te.id_tipo_equipo', '=', 'e.id_tipo_equipo')
            ->leftJoin('marcas as ma', 'ma.id_marca', '=', 'e.id_marca')
            ->leftJoin('modelos as mo', 'mo.id_modelo', '=', 'e.id_modelo')
            ->leftJoin('unidades_organizativas as u', 'u.id_unidad', '=', 'a.id_unidad')
            ->leftJoin('departamentos as d', 'd.id_departamento', '=', 'a.id_departamento')
            ->where('a.id_equipo', $idEquipo)
            ->whereNull('a.fecha_fin')
            ->orderByDesc('a.fecha_asignacion')
            ->first([
                'a.id_asignacion',
                'a.nombre_colaborador',
                'a.numero_colaborador',
                'a.fecha_asignacion',
                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'e.numero_serie',
                'te.nombre as tipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'u.nombre as hotel',
                'd.nombre as departamento',
            ]);

        return $fila ? (array) $fila : null;
    }

    /*
     * Equipos con asignación activa (selector de la página Devolver).
     */
    public function equiposAsignados(): array
    {
        return DB::table('asignaciones as a')
            ->join('equipos as e', 'e.id_equipo', '=', 'a.id_equipo')
            ->leftJoin('tipos_equipo as te', 'te.id_tipo_equipo', '=', 'e.id_tipo_equipo')
            ->whereNull('a.fecha_fin')
            ->orderBy('e.codigo_inventario')
            ->get([
                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'te.nombre as tipo',
                'a.nombre_colaborador',
                'a.numero_colaborador',
            ])
            ->unique('id_equipo')
            ->map(fn ($fila): array => [
                'value' => (string) $fila->id_equipo,
                'label' => trim($fila->codigo_inventario . ' — ' . ($fila->nombre_equipo ?: $fila->tipo ?: 'Equipo'))
                    . ' · ' . ($fila->nombre_colaborador ?: 'Sin nombre'),
                // Buscar también por colaborador o número.
                'search' => trim(implode(' ', array_filter([$fila->nombre_colaborador, $fila->numero_colaborador, $fila->tipo]))),
            ])
            ->values()
            ->all();
    }

    /*
     * Cuántos equipos tiene asignados cada usuario (cuenta), por id.
     */
    public function conteoPorUsuario(array $idsUsuario): array
    {
        if ($idsUsuario === [] || ! app(ColaboradoresService::class)->disponible()) {
            return [];
        }

        return DB::table('asignaciones')
            ->whereIn('id_usuario', $idsUsuario)
            ->whereNull('fecha_fin')
            ->groupBy('id_usuario')
            ->selectRaw('id_usuario, COUNT(DISTINCT id_equipo) as total')
            ->pluck('total', 'id_usuario')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Devolver
    |--------------------------------------------------------------------------
    |
    | Devuelve el id del movimiento creado. Lanza ValidationException
    | (campo «idEquipo» o «fecha») si no se puede.
    |
    */

    public function devolver(
        int $idEquipo,
        string $motivo,
        ?string $observaciones = null,
        ?string $recibidoPor = null,
        ?Carbon $fecha = null,
        ?int $realizadoPor = null,
        bool $notificar = true
    ): int {
        $idTipo = $this->idTipoMovimiento();

        if ($idTipo === null) {
            throw ValidationException::withMessages([
                'idEquipo' => 'Falta ejecutar la actualización de la base de datos (php artisan migrate) para registrar devoluciones.',
            ]);
        }

        $fecha ??= now();
        $realizadoPor ??= auth()->id();

        $resultado = DB::transaction(function () use ($idEquipo, $motivo, $observaciones, $recibidoPor, $fecha, $realizadoPor, $idTipo) {
            $asignacion = DB::table('asignaciones')
                ->where('id_equipo', $idEquipo)
                ->whereNull('fecha_fin')
                ->orderByDesc('fecha_asignacion')
                ->lockForUpdate()
                ->first();

            if (! $asignacion) {
                throw ValidationException::withMessages([
                    'idEquipo' => 'Este equipo ya no tiene un responsable asignado. Puede que alguien lo haya devuelto o reasignado hace un momento.',
                ]);
            }

            if ($fecha->lt(Carbon::parse($asignacion->fecha_asignacion)->startOfDay())) {
                throw ValidationException::withMessages([
                    'fecha' => 'La fecha de devolución no puede ser anterior a la asignación (' . Carbon::parse($asignacion->fecha_asignacion)->format('d/m/Y') . ').',
                ]);
            }

            // Si hubiera más de una abierta por error, se cierran todas.
            DB::table('asignaciones')
                ->where('id_equipo', $idEquipo)
                ->whereNull('fecha_fin')
                ->update(['fecha_fin' => $fecha]);

            $movimiento = [
                'id_equipo' => $idEquipo,
                'id_tipo_movimiento' => $idTipo,
                'id_asignacion_anterior' => $asignacion->id_asignacion,
                'id_asignacion_nueva' => null,
                'motivo' => $motivo,
                'observaciones' => filled($observaciones) ? trim($observaciones) : null,
                'realizado_por' => $realizadoPor,
                'fecha_hora' => $fecha,
            ];

            if ($this->tieneRecibidoPor()) {
                $movimiento['recibido_por'] = filled($recibidoPor) ? trim($recibidoPor) : null;
            }

            $idMovimiento = DB::table('movimientos')->insertGetId($movimiento, 'id_movimiento');

            if ($idDisponible = $this->idEstadoDisponible()) {
                DB::table('equipos')
                    ->where('id_equipo', $idEquipo)
                    ->update(['id_estado_activo' => $idDisponible]);
            }

            $codigo = (string) DB::table('equipos')->where('id_equipo', $idEquipo)->value('codigo_inventario');

            DB::table('bitacora_auditoria')->insert([
                'id_usuario' => $realizadoPor,
                'accion' => 'EQUIPO_DEVUELTO',
                'modulo' => 'ASIGNACIONES',
                'descripcion' => sprintf(
                    'Se devolvió el equipo %s (responsable: %s). Motivo: %s',
                    $codigo ?: 'Sin código',
                    $asignacion->nombre_colaborador ?: 'sin nombre',
                    $motivo
                ),
                'fecha_hora' => now(),
            ]);

            return ['movimiento' => (int) $idMovimiento, 'colaborador' => (string) $asignacion->nombre_colaborador];
        });

        $this->refrescarCaches($idEquipo);

        if ($notificar) {
            $notificaciones = app(NotificacionService::class);

            $notificaciones->notificar(
                'asignaciones',
                'Equipo devuelto',
                $notificaciones->nombreActor()
                    . ' registró la devolución del equipo '
                    . $notificaciones->etiquetaEquipo($idEquipo)
                    . ($resultado['colaborador'] !== '' ? ' (antes con ' . $resultado['colaborador'] . ').' : '.'),
                route('equipos.show', $idEquipo, false)
            );
        }

        return $resultado['movimiento'];
    }

    /*
     * Baja de cuenta: devuelve todos los equipos asignados a ese usuario.
     * Devuelve los códigos de los equipos devueltos.
     */
    public function devolverTodosDeUsuario(int $idUsuario, string $motivo, ?string $observaciones = null): array
    {
        if (! $this->disponible() || ! app(ColaboradoresService::class)->disponible()) {
            return [];
        }

        $equipos = DB::table('asignaciones as a')
            ->join('equipos as e', 'e.id_equipo', '=', 'a.id_equipo')
            ->where('a.id_usuario', $idUsuario)
            ->whereNull('a.fecha_fin')
            ->distinct()
            ->pluck('e.codigo_inventario', 'e.id_equipo');

        $devueltos = [];

        foreach ($equipos as $idEquipo => $codigo) {
            try {
                $this->devolver((int) $idEquipo, $motivo, $observaciones, null, now(), auth()->id(), notificar: false);
                $devueltos[] = (string) $codigo;
            } catch (ValidationException) {
                // Ya no estaba asignado: se omite.
            }
        }

        return $devueltos;
    }

    /*
    |--------------------------------------------------------------------------
    | Acta
    |--------------------------------------------------------------------------
    */

    public function datosActa(int $idMovimiento): ?array
    {
        $columnas = [
            'm.id_movimiento',
            'm.fecha_hora',
            'm.motivo',
            'm.observaciones',
            'a.nombre_colaborador',
            'a.numero_colaborador',
            'a.fecha_asignacion',
            'e.codigo_inventario',
            'e.nombre_equipo',
            'e.numero_serie',
            'e.host',
            'te.nombre as tipo',
            'ma.nombre as marca',
            'mo.nombre as modelo',
            'cond.nombre as condicion',
            'h.nombre as hotel',
            'd.nombre as departamento',
            'tm.clave as tipo_clave',
        ];

        if ($this->tieneRecibidoPor()) {
            $columnas[] = 'm.recibido_por';
        }

        $fila = DB::table('movimientos as m')
            ->join('equipos as e', 'e.id_equipo', '=', 'm.id_equipo')
            ->join('catalogo_valores as tm', 'tm.id_valor', '=', 'm.id_tipo_movimiento')
            ->leftJoin('asignaciones as a', 'a.id_asignacion', '=', 'm.id_asignacion_anterior')
            ->leftJoin('tipos_equipo as te', 'te.id_tipo_equipo', '=', 'e.id_tipo_equipo')
            ->leftJoin('marcas as ma', 'ma.id_marca', '=', 'e.id_marca')
            ->leftJoin('modelos as mo', 'mo.id_modelo', '=', 'e.id_modelo')
            ->leftJoin('catalogo_valores as cond', 'cond.id_valor', '=', 'e.id_condicion_activo')
            ->leftJoin('unidades_organizativas as h', 'h.id_unidad', '=', 'a.id_unidad')
            ->leftJoin('departamentos as d', 'd.id_departamento', '=', 'a.id_departamento')
            ->where('m.id_movimiento', $idMovimiento)
            ->first($columnas);

        if (! $fila || strtoupper((string) $fila->tipo_clave) !== 'DEVOLUCION') {
            return null;
        }

        return (array) $fila;
    }

    /*
    |--------------------------------------------------------------------------
    | Apoyo
    |--------------------------------------------------------------------------
    */

    /*
     * Comprobaciones de esquema en caché: en Postgres cada Schema::hasColumn
     * es una consulta (y la base está lejos). Solo se guarda el «sí».
     */
    private function tieneRecibidoPor(): bool
    {
        if (\Illuminate\Support\Facades\Cache::get('trackit.movimientos.recibido_por.v1') === true) {
            return true;
        }

        $existe = Schema::hasColumn('movimientos', 'recibido_por');

        if ($existe) {
            \Illuminate\Support\Facades\Cache::forever('trackit.movimientos.recibido_por.v1', true);
        }

        return $existe;
    }

    private function idTipoMovimiento(): ?int
    {
        $id = DB::table('catalogo_valores as cv')
            ->join('catalogos as c', 'c.id_catalogo', '=', 'cv.id_catalogo')
            ->whereRaw('LOWER(c.clave) = ?', ['tipo_movimiento'])
            ->whereRaw('LOWER(cv.clave) = ?', ['devolucion'])
            ->where('cv.activo', true)
            ->value('cv.id_valor');

        return $id !== null ? (int) $id : null;
    }

    /*
     * Estado «Disponible» del equipo (por clave o por nombre).
     */
    private function idEstadoDisponible(): ?int
    {
        $id = DB::table('estados_equipo')
            ->where(function ($query) {
                $query->whereRaw('UPPER(clave) IN (?, ?, ?, ?)', ['DISPONIBLE', 'EN_ALMACEN', 'ALMACEN', 'STOCK'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['disponible%']);
            })
            ->orderByRaw("CASE WHEN UPPER(clave) = 'DISPONIBLE' THEN 0 ELSE 1 END")
            ->value('id_estado_equipo');

        return $id !== null ? (int) $id : null;
    }

    private function refrescarCaches(int $idEquipo): void
    {
        $cache = app(SystemCacheService::class);

        $cache->refreshModule('asignaciones');
        $cache->refreshModule('equipos');
        $cache->refreshModule('dashboard');

        Cache::forget('equipos.edit.base.' . $idEquipo);
    }
}
