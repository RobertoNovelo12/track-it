<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReporteMantenimientoActaService
{
    public function obtener(
        array $filtros = [],
        ?int $limite = null
    ): Collection {
        $consulta = app(ReporteDataService::class)
            ->query('mantenimientos', $filtros)
            ->select('mt.id_mantenimiento as registro_id');

        if ($limite !== null) {
            $consulta->limit(max(1, $limite));
        }

        $ids = $consulta
            ->get()
            ->pluck('registro_id')
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $registros = DB::table('mantenimientos as m')
            ->join(
                'equipos as e',
                'e.id_equipo',
                '=',
                'm.id_equipo'
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
                'catalogo_valores as tm',
                'tm.id_valor',
                '=',
                'm.id_tipo_mantenimiento'
            )
            ->leftJoin(
                'catalogo_valores as ti',
                'ti.id_valor',
                '=',
                'm.id_tipo_intervencion'
            )
            ->leftJoin(
                'catalogo_valores as em',
                'em.id_valor',
                '=',
                'm.id_estado_mantenimiento'
            )
            ->leftJoin(
                'catalogo_valores as pr',
                'pr.id_valor',
                '=',
                'm.id_prioridad'
            )
            ->leftJoin(
                'usuarios as u',
                'u.id_usuario',
                '=',
                'm.id_tecnico'
            )
            ->leftJoin('proveedores as proveedor', 'proveedor.id_proveedor', '=', 'm.id_proveedor')
            ->whereIn('m.id_mantenimiento', $ids->all())
            ->select([
                'm.id_mantenimiento',
                'm.folio',
                'm.fecha_intervencion',
                'm.fecha_fin',
                'm.hora_inicio',
                'm.hora_fin',
                'm.falla_reportada',
                'm.diagnostico',
                'm.intervencion_realizada',
                'm.observaciones',
                'm.fecha_proximo_mantenimiento',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'e.numero_serie',
                'e.host',
                'e.fecha_fin_garantia',
                'proveedor.nombre as proveedor',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'tm.nombre as tipo_mantenimiento',
                'ti.nombre as tipo_intervencion',
                'em.nombre as estado',
                'pr.nombre as prioridad',
                'u.nombres as tecnico_nombres',
                'u.apellido_paterno as tecnico_apellido_paterno',
                'u.apellido_materno as tecnico_apellido_materno',
            ])
            ->get()
            ->keyBy('id_mantenimiento');

        $refacciones = DB::table('mantenimiento_refacciones')
            ->whereIn('id_mantenimiento', $ids->all())
            ->orderBy('id_mantenimiento_refaccion')
            ->get([
                'id_mantenimiento',
                'nombre_refaccion',
                'codigo_parte',
                'cantidad',
                'unidad',
            ])
            ->groupBy('id_mantenimiento');

        return $ids
            ->map(function ($id) use (
                $registros,
                $refacciones
            ) {
                $m = $registros->get($id);

                if (!$m) {
                    return null;
                }

                $tecnico = trim(implode(
                    ' ',
                    array_filter([
                        $m->tecnico_nombres,
                        $m->tecnico_apellido_paterno,
                        $m->tecnico_apellido_materno,
                    ])
                ));

                return [
                    'id' => $m->id_mantenimiento,
                    'folio' => $m->folio,

                    'equipo' => [
                        'codigo' => $m->codigo_inventario,
                        'nombre' => $m->nombre_equipo,
                        'marca' => $m->marca,
                        'modelo' => $m->modelo,
                        'serie' => $m->numero_serie,
                        'host' => $m->host,
                    ],

                    'tipo_mantenimiento' =>
                        $m->tipo_mantenimiento,

                    'tipo_intervencion' =>
                        $m->tipo_intervencion,

                    'tecnico' => $tecnico ?: null,
                    'prioridad' => $m->prioridad,
                    'estado' => $m->estado,
                    'proveedor' => $m->proveedor,
                    'fin_garantia_equipo' => $this->fecha($m->fecha_fin_garantia),

                    'fecha_inicio' => $this->fecha(
                        $m->fecha_intervencion
                    ),

                    'hora_inicio' => $this->hora(
                        $m->hora_inicio
                    ),

                    'fecha_fin' => $this->fecha(
                        $m->fecha_fin
                    ),

                    'hora_fin' => $this->hora(
                        $m->hora_fin
                    ),

                    'falla_reportada' => $m->falla_reportada,
                    'diagnostico' => $m->diagnostico,

                    'intervencion_realizada' =>
                        $m->intervencion_realizada,

                    'observaciones' => $m->observaciones,

                    'proximo_mantenimiento' => $this->fecha(
                        $m->fecha_proximo_mantenimiento
                    ),

                    'refacciones' => $refacciones
                        ->get($id, collect())
                        ->map(fn ($r) => [
                            'nombre' => $r->nombre_refaccion,
                            'codigo' => $r->codigo_parte,
                            'cantidad' => $r->cantidad,
                            'unidad' => $r->unidad,
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->filter()
            ->values();
    }

    private function fecha($fecha): ?string
    {
        return filled($fecha)
            ? Carbon::parse($fecha)->format('d/m/Y')
            : null;
    }

    private function hora($hora): ?string
    {
        if (!filled($hora)) {
            return null;
        }

        return substr((string) $hora, 0, 5);
    }
}
