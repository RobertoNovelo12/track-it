<?php
namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReporteDataService
{
    public const TIPOS = [
        'general',
        'altas',
        'bajas',
        'asignaciones',
        'reasignaciones',
        'mantenimientos',
    ];

    public function query(string $tipo, array $filtros = []): Builder
    {
        return match ($this->normalizarTipo($tipo)) {
            'general', 'altas' => $this->queryEquipos($filtros),
            'bajas'          => $this->queryBajas($filtros),
            'asignaciones'   => $this->queryAsignaciones($filtros),
            'reasignaciones' => $this->queryReasignaciones($filtros),
            'mantenimientos' => $this->queryMantenimientos($filtros),
        };
    }

    public function datosCombinados(array $tipos, array $filtros = []): Collection
    {
        $tipos = collect($tipos)
            ->map(fn($tipo) => $this->normalizarTipo((string) $tipo))
            ->unique()
            ->values();

        if ($tipos->isEmpty()) {
            throw new InvalidArgumentException(
                'Selecciona al menos un tipo de reporte.'
            );
        }

        return $tipos->flatMap(function (string $tipo) use ($filtros) {
            $etiqueta = $this->etiqueta($tipo);

            return $this->query($tipo, $filtros)
                ->get()
                ->map(function ($fila) use ($tipo, $etiqueta) {
                    $fila->tipo_reporte          = $tipo;
                    $fila->tipo_reporte_etiqueta = $etiqueta;

                    return $fila;
                });
        })->values();
    }

    public function normalizarTipo(string $tipo): string
    {
        $tipo = strtolower(trim($tipo));

        if (! in_array($tipo, self::TIPOS, true)) {
            throw new InvalidArgumentException(
                'El tipo de reporte no es válido.'
            );
        }

        return $tipo;
    }

    public function etiqueta(string $tipo): string
    {
        return match ($this->normalizarTipo($tipo)) {
            'general'        => 'General',
            'altas'          => 'Altas',
            'bajas'          => 'Bajas',
            'asignaciones'   => 'Asignaciones',
            'reasignaciones' => 'Reasignaciones',
            'mantenimientos' => 'Mantenimientos',
        };
    }

    private function queryEquipos(array $filtros): Builder
    {
        $query = DB::table('equipos as e')
            ->leftJoin(
                'tipos_equipo as te',
                'te.id_tipo_equipo',
                '=',
                'e.id_tipo_equipo'
            )
            ->leftJoin(
                'marcas as ma',
                'ma.id_marca',
                '=',
                'e.id_marca'
            )
            ->leftJoin(
                'modelos as mo',
                'mo.id_modelo',
                '=',
                'e.id_modelo'
            )
            ->leftJoin(
                'catalogo_valores as estado',
                'estado.id_valor',
                '=',
                'e.id_estado_activo'
            )
            ->select([
                'e.id_equipo as registro_id',
                'e.codigo_inventario as codigo',
                'e.nombre_equipo as elemento',
                'te.nombre as tipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'e.numero_serie as serie',
                'estado.nombre as estado',
                DB::raw(
                    'CAST(e.fecha_registro AS DATE) as fecha'
                ),
                DB::raw(
                    "'Registro de activo' as detalle"
                ),
            ]);

        return $this->aplicarFiltros(
            $query,
            $filtros,
            'e.fecha_registro',
            'e.id_estado_activo'
        )->orderByDesc('e.fecha_registro');
    }

    private function queryBajas(array $filtros): Builder
    {
        $query = DB::table('bajas as b')
            ->join(
                'equipos as e',
                'e.id_equipo',
                '=',
                'b.id_equipo'
            )
            ->leftJoin(
                'tipos_equipo as te',
                'te.id_tipo_equipo',
                '=',
                'e.id_tipo_equipo'
            )
            ->leftJoin(
                'marcas as ma',
                'ma.id_marca',
                '=',
                'e.id_marca'
            )
            ->leftJoin(
                'modelos as mo',
                'mo.id_modelo',
                '=',
                'e.id_modelo'
            )
            ->leftJoin(
                'catalogo_valores as estado',
                'estado.id_valor',
                '=',
                'b.id_estado_baja'
            )
            ->leftJoin(
                'catalogo_valores as motivo',
                'motivo.id_valor',
                '=',
                'b.id_motivo_baja'
            )
            ->select([
                'b.id_baja as registro_id',
                'b.folio as codigo',
                'e.nombre_equipo as elemento',
                'te.nombre as tipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'e.numero_serie as serie',
                'estado.nombre as estado',
                'b.fecha_baja as fecha',
                DB::raw(
                    "concat_ws(
                        ' · ',
                        motivo.nombre,
                        NULLIF(b.comentarios, '')
                    ) as detalle"
                ),
            ]);

        return $this->aplicarFiltros(
            $query,
            $filtros,
            'b.fecha_baja',
            'b.id_estado_baja'
        )->orderByDesc('b.fecha_baja');
    }

    private function queryAsignaciones(array $filtros): Builder
    {
        $query = DB::table('asignaciones as a')
            ->join(
                'equipos as e',
                'e.id_equipo',
                '=',
                'a.id_equipo'
            )
            ->leftJoin(
                'tipos_equipo as te',
                'te.id_tipo_equipo',
                '=',
                'e.id_tipo_equipo'
            )
            ->leftJoin(
                'marcas as ma',
                'ma.id_marca',
                '=',
                'e.id_marca'
            )
            ->leftJoin(
                'modelos as mo',
                'mo.id_modelo',
                '=',
                'e.id_modelo'
            )
            ->leftJoin(
                'areas as ar',
                'ar.id_area',
                '=',
                'a.id_area'
            )
            ->leftJoin(
                'departamentos as dep',
                'dep.id_departamento',
                '=',
                'a.id_departamento'
            )
            ->leftJoin(
                'ubicaciones as ub',
                'ub.id_ubicacion',
                '=',
                'a.id_ubicacion'
            )
            ->select([
                'a.id_asignacion as registro_id',
                'e.codigo_inventario as codigo',
                'e.nombre_equipo as elemento',
                'te.nombre as tipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'e.numero_serie as serie',
                DB::raw(
                    "CASE
                        WHEN a.fecha_fin IS NULL
                            THEN 'Activa'
                        ELSE 'Finalizada'
                    END as estado"
                ),
                DB::raw(
                    'CAST(a.fecha_asignacion AS DATE) as fecha'
                ),
                DB::raw(
                    "concat_ws(
                        ' · ',
                        NULLIF(a.nombre_colaborador, ''),
                        ar.nombre,
                        dep.nombre,
                        ub.nombre
                    ) as detalle"
                ),
            ]);

        $query = $this->aplicarFiltros(
            $query,
            $filtros,
            'a.fecha_asignacion',
            null
        );

        if (($filtros['estado'] ?? '') === 'activa') {
            $query->whereNull('a.fecha_fin');
        }

        if (($filtros['estado'] ?? '') === 'finalizada') {
            $query->whereNotNull('a.fecha_fin');
        }

        return $query->orderByDesc('a.fecha_asignacion');
    }

    private function queryReasignaciones(array $filtros): Builder
    {
        $query = DB::table('movimientos as mv')
            ->join(
                'equipos as e',
                'e.id_equipo',
                '=',
                'mv.id_equipo'
            )
            ->join(
                'catalogo_valores as tipo_mov',
                'tipo_mov.id_valor',
                '=',
                'mv.id_tipo_movimiento'
            )
            ->leftJoin(
                'tipos_equipo as te',
                'te.id_tipo_equipo',
                '=',
                'e.id_tipo_equipo'
            )
            ->leftJoin(
                'marcas as ma',
                'ma.id_marca',
                '=',
                'e.id_marca'
            )
            ->leftJoin(
                'modelos as mo',
                'mo.id_modelo',
                '=',
                'e.id_modelo'
            )
            ->leftJoin(
                'catalogo_valores as estado',
                'estado.id_valor',
                '=',
                'e.id_estado_activo'
            )
            ->where(function (Builder $query): void {
                $query
                    ->whereRaw(
                        "LOWER(COALESCE(tipo_mov.clave, '')) LIKE ?",
                        ['%reasign%']
                    )
                    ->orWhereRaw(
                        "LOWER(COALESCE(tipo_mov.nombre, '')) LIKE ?",
                        ['%reasign%']
                    );
            })
            ->select([
                'mv.id_movimiento as registro_id',
                'e.codigo_inventario as codigo',
                'e.nombre_equipo as elemento',
                'te.nombre as tipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'e.numero_serie as serie',
                'estado.nombre as estado',
                DB::raw(
                    'CAST(mv.fecha_hora AS DATE) as fecha'
                ),
                DB::raw(
                    "concat_ws(
                        ' · ',
                        tipo_mov.nombre,
                        NULLIF(mv.motivo, ''),
                        NULLIF(mv.observaciones, '')
                    ) as detalle"
                ),
            ]);

        return $this->aplicarFiltros(
            $query,
            $filtros,
            'mv.fecha_hora',
            'e.id_estado_activo'
        )->orderByDesc('mv.fecha_hora');
    }

    private function queryMantenimientos(array $filtros): Builder
    {
        $query = DB::table('mantenimientos as mt')
            ->join(
                'equipos as e',
                'e.id_equipo',
                '=',
                'mt.id_equipo'
            )
            ->leftJoin(
                'tipos_equipo as te',
                'te.id_tipo_equipo',
                '=',
                'e.id_tipo_equipo'
            )
            ->leftJoin(
                'marcas as ma',
                'ma.id_marca',
                '=',
                'e.id_marca'
            )
            ->leftJoin(
                'modelos as mo',
                'mo.id_modelo',
                '=',
                'e.id_modelo'
            )
            ->leftJoin(
                'catalogo_valores as estado',
                'estado.id_valor',
                '=',
                'mt.id_estado_mantenimiento'
            )
            ->leftJoin(
                'catalogo_valores as tipo_mant',
                'tipo_mant.id_valor',
                '=',
                'mt.id_tipo_mantenimiento'
            )
            ->leftJoin(
                'usuarios as tecnico',
                'tecnico.id_usuario',
                '=',
                'mt.id_tecnico'
            )
            ->select([
                'mt.id_mantenimiento as registro_id',
                'mt.folio as codigo',
                'e.nombre_equipo as elemento',
                'te.nombre as tipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'e.numero_serie as serie',
                'estado.nombre as estado',
                'mt.fecha_intervencion as fecha',
                DB::raw(
                    "concat_ws(
                        ' · ',
                        tipo_mant.nombre,
                        NULLIF(mt.falla_reportada, ''),
                        concat_ws(
                            ' ',
                            tecnico.nombres,
                            tecnico.apellido_paterno
                        )
                    ) as detalle"
                ),
            ]);

        return $this->aplicarFiltros(
            $query,
            $filtros,
            'mt.fecha_intervencion',
            'mt.id_estado_mantenimiento'
        )->orderByDesc('mt.fecha_intervencion');
    }

    private function aplicarFiltros(
        Builder $query,
        array $filtros,
        string $columnaFecha,
        ?string $columnaEstado
    ): Builder {

        if (! empty($filtros['busqueda'])) {
            $busqueda = trim((string) $filtros['busqueda']);

            $query->where(function ($subquery) use ($busqueda) {
                $subquery
                    ->where(
                        'nombre_equipo',
                        'ilike',
                        "%{$busqueda}%"
                    )
                    ->orWhere(
                        'numero_serie',
                        'ilike',
                        "%{$busqueda}%"
                    );
            });
        }
        if (! empty($filtros['tipo_equipo'])) {
            $query->where(
                'e.id_tipo_equipo',
                (int) $filtros['tipo_equipo']
            );
        }

        if (! empty($filtros['marca'])) {
            $query->where(
                'e.id_marca',
                (int) $filtros['marca']
            );
        }

        if (! empty($filtros['modelo'])) {
            $query->where(
                'e.id_modelo',
                (int) $filtros['modelo']
            );
        }

        if (
            $columnaEstado !== null
            && ! empty($filtros['estado'])
        ) {
            $query->where(
                $columnaEstado,
                (int) $filtros['estado']
            );
        }

        if (! empty($filtros['fecha_inicio'])) {
            $query->whereDate(
                $columnaFecha,
                '>=',
                $filtros['fecha_inicio']
            );
        }

        if (! empty($filtros['fecha_fin'])) {
            $query->whereDate(
                $columnaFecha,
                '<=',
                $filtros['fecha_fin']
            );
        }

        return $query;
    }
}
