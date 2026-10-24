<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReporteAltasActaService
{
    public function obtener(
        array $filtros = [],
        ?int $limite = null
    ): Collection {
        $consulta = app(ReporteDataService::class)
            ->query('altas', $filtros)
            ->select('e.id_equipo as registro_id');

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

        $equipos = DB::table('equipos as e')
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
                'proveedores as p',
                'p.id_proveedor',
                '=',
                'e.id_proveedor'
            )
            ->leftJoin(
                'catalogo_valores as estado',
                'estado.id_valor',
                '=',
                'e.id_estado_activo'
            )
            ->leftJoin(
                'catalogo_valores as condicion',
                'condicion.id_valor',
                '=',
                'e.id_condicion_activo'
            )
            ->leftJoin(
                'departamentos as dep',
                'dep.id_departamento',
                '=',
                'e.id_departamento'
            )
            ->leftJoin(
                'areas as ar',
                'ar.id_area',
                '=',
                'e.id_area'
            )
            ->leftJoin(
                'areas as ar_dep',
                'ar_dep.id_area',
                '=',
                'dep.id_area'
            )
            ->whereIn('e.id_equipo', $ids->all())
            ->select([
                'e.id_equipo',
                'e.codigo_inventario',
                'e.fecha_registro',
                'e.nombre_equipo',
                'e.host',
                'e.numero_serie',
                'e.direccion_mac',
                'e.fecha_compra',
                'e.numero_factura',
                'e.fecha_fin_garantia',
                'e.comentarios',
                'te.nombre as tipo_equipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'p.nombre as proveedor',
                'estado.nombre as estado',
                'condicion.nombre as condicion',
                'ar.nombre as area_directa',
                'ar_dep.nombre as area_departamento',
                'dep.nombre as departamento',
            ])
            ->get()
            ->keyBy('id_equipo');

        $responsables = DB::table('asignaciones')
            ->whereIn('id_equipo', $ids->all())
            ->whereNull('fecha_fin')
            ->orderByDesc('id_asignacion')
            ->get([
                'id_equipo',
                'nombre_colaborador',
            ])
            ->unique('id_equipo')
            ->keyBy('id_equipo');

        // Programas instalados (Administración › Programas), por equipo.
        $programas = app(ProgramasService::class)->disponible()
            ? \Illuminate\Support\Facades\DB::table('equipo_programa as ep')
                ->join('programas as p', 'p.id_programa', '=', 'ep.id_programa')
                ->whereIn('ep.id_equipo', $ids->all())
                ->orderBy('p.nombre')
                ->get(['ep.id_equipo', 'p.nombre', 'p.version'])
                ->groupBy('id_equipo')
                ->map(fn ($grupo) => $grupo->map(fn ($p) => trim($p->nombre . (filled($p->version) ? ' v' . ltrim((string) $p->version, 'vV') : '')))->all())
            : collect();

        return $ids
            ->map(function (int $id) use (
                $equipos,
                $responsables,
                $programas
            ) {
                $equipo = $equipos->get($id);

                if (!$equipo) {
                    return null;
                }

                $responsable = $responsables->get($id);

                return [
                    'id' => $id,

                    'codigo' =>
                        $equipo->codigo_inventario,

                    'fecha_registro' => $this->fecha(
                        $equipo->fecha_registro
                    ),

                    'nombre' => $equipo->nombre_equipo,
                    'host' => $equipo->host,
                    'tipo' => $equipo->tipo_equipo,
                    'marca' => $equipo->marca,
                    'modelo' => $equipo->modelo,
                    'serie' => $equipo->numero_serie,
                    'mac' => $equipo->direccion_mac,

                    'fecha_compra' => $this->fecha(
                        $equipo->fecha_compra
                    ),

                    'factura' => $equipo->numero_factura,
                    'proveedor' => $equipo->proveedor,

                    'garantia' => $this->fecha(
                        $equipo->fecha_fin_garantia
                    ),

                    'estado' => $equipo->estado,
                    'condicion' => $equipo->condicion,

                    'responsable' =>
                        $responsable->nombre_colaborador ?? null,

                    'area' =>
                        $equipo->area_directa
                        ?: $equipo->area_departamento,

                    'departamento' => $equipo->departamento,
                    'comentarios' => $equipo->comentarios,
                    'programas' => $programas->get($id, []),
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
}
