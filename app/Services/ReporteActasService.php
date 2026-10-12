<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReporteActasService
{
    public function obtener(
        string $tipo,
        array $filtros = [],
        ?int $limite = null
    ): Collection {
        if (!in_array(
            $tipo,
            ['asignaciones', 'reasignaciones'],
            true
        )) {
            return collect();
        }

        $consulta = app(ReporteDataService::class)
            ->query($tipo, $filtros);

        if ($limite !== null) {
            $consulta->limit(max(1, $limite));
        }

        return $consulta
            ->get()
            ->map(function ($registro) use ($tipo) {
                $id = (int) $registro->registro_id;

                return $tipo === 'asignaciones'
                    ? $this->asignacion($id)
                    : $this->reasignacion($id);
            })
            ->filter()
            ->values();
    }

    private function responsable(?int $id): ?array
    {
        if (!$id) {
            return null;
        }

        $registro = DB::table('asignaciones as a')
            ->leftJoin(
                'departamentos as dep',
                'dep.id_departamento',
                '=',
                'a.id_departamento'
            )
            ->leftJoin(
                'areas as ar',
                'ar.id_area',
                '=',
                'a.id_area'
            )
            ->leftJoin(
                'areas as ar_dep',
                'ar_dep.id_area',
                '=',
                'dep.id_area'
            )
            ->leftJoin(
                'ubicaciones as ub',
                'ub.id_ubicacion',
                '=',
                'a.id_ubicacion'
            )
            ->leftJoin(
                'sedes as sede_area',
                'sede_area.id_sede',
                '=',
                'ar.id_sede'
            )
            ->leftJoin(
                'sedes as sede_dep',
                'sede_dep.id_sede',
                '=',
                'ar_dep.id_sede'
            )
            ->leftJoin(
                'sedes as sede_ubicacion',
                'sede_ubicacion.id_sede',
                '=',
                'ub.id_sede'
            )
            ->leftJoin(
                'catalogo_valores as tipo',
                'tipo.id_valor',
                '=',
                'a.id_tipo_asignacion'
            )
            ->where('a.id_asignacion', $id)
            ->first([
                'a.id_asignacion',
                'a.nombre_colaborador',
                'a.numero_colaborador',
                'a.fecha_asignacion',
                'a.fecha_fin',
                'a.observaciones',
                'ar.nombre as area_directa',
                'ar_dep.nombre as area_departamento',
                'dep.nombre as departamento',
                'ub.nombre as ubicacion',
                'sede_area.nombre as sede_directa',
                'sede_dep.nombre as sede_departamento',
                'sede_ubicacion.nombre as sede_ubicacion',
                'tipo.nombre as tipo_asignacion',
            ]);

        if (!$registro) {
            return null;
        }

        $area = $registro->area_directa
            ?: $registro->area_departamento;

        $sede = $registro->sede_directa
            ?: $registro->sede_departamento
            ?: $registro->sede_ubicacion;

        return [
            'id_asignacion' => $registro->id_asignacion,
            'nombre' => $registro->nombre_colaborador,
            'numero' => $registro->numero_colaborador,
            'sede' => $sede,
            'area' => $area,
            'departamento' => $registro->departamento,
            'ubicacion' => $registro->ubicacion,
            'tipo' => $registro->tipo_asignacion,

            'fecha' => $this->fecha(
                $registro->fecha_asignacion
            ),

            'fecha_fin' => $this->fecha(
                $registro->fecha_fin
            ),

            'observaciones' => $registro->observaciones,
        ];
    }

    private function equipo(int $id): array
    {
        $equipo = DB::table('equipos as e')
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
                'catalogo_valores as condicion',
                'condicion.id_valor',
                '=',
                'e.id_condicion_activo'
            )
            ->where('e.id_equipo', $id)
            ->first([
                'e.codigo_inventario',
                'e.nombre_equipo',
                'e.numero_serie',
                'e.host',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'condicion.nombre as condicion',
            ]);

        return [
            'codigo' => $equipo->codigo_inventario ?? null,
            'nombre' => $equipo->nombre_equipo ?? null,
            'serie' => $equipo->numero_serie ?? null,
            'host' => $equipo->host ?? null,
            'marca' => $equipo->marca ?? null,
            'modelo' => $equipo->modelo ?? null,
            'condicion' => $equipo->condicion ?? null,
        ];
    }

    private function asignacion(int $id): ?array
    {
        $registro = DB::table('asignaciones')
            ->where('id_asignacion', $id)
            ->first();

        if (!$registro) {
            return null;
        }

        $responsable = $this->responsable($id);

        return [
            'tipo' => 'asignaciones',
            'referencia' => $id,

            'fecha' => $this->fecha(
                $registro->fecha_asignacion
            ),

            'ticket' => null,
            'hotel' => 'Grand Palladium Riviera Maya',

            'equipo' => $this->equipo(
                (int) $registro->id_equipo
            ),

            'anterior' => null,
            'nuevo' => $responsable,
            'motivo' => null,
            'observaciones' => $registro->observaciones,
        ];
    }

    private function reasignacion(int $id): ?array
    {
        $movimiento = DB::table('movimientos')
            ->where('id_movimiento', $id)
            ->first();

        if (!$movimiento) {
            return null;
        }

        $anterior = $this->responsable(
            $movimiento->id_asignacion_anterior
                ? (int) $movimiento->id_asignacion_anterior
                : null
        );

        $nuevo = $this->responsable(
            $movimiento->id_asignacion_nueva
                ? (int) $movimiento->id_asignacion_nueva
                : null
        );

        return [
            'tipo' => 'reasignaciones',
            'referencia' => $id,

            'fecha' => $this->fecha(
                $movimiento->fecha_hora
            ),

            'ticket' => null,
            'hotel' => 'Grand Palladium Riviera Maya',

            'equipo' => $this->equipo(
                (int) $movimiento->id_equipo
            ),

            'anterior' => $anterior,
            'nuevo' => $nuevo,
            'motivo' => $movimiento->motivo,
            'observaciones' => $movimiento->observaciones,

            'sede_anterior' => $anterior['sede'] ?? null,
            'area_anterior' => $anterior['area'] ?? null,

            'departamento_anterior' =>
                $anterior['departamento'] ?? null,

            'ubicacion_anterior' =>
                $anterior['ubicacion'] ?? null,

            'sede_nueva' => $nuevo['sede'] ?? null,
            'area_nueva' => $nuevo['area'] ?? null,

            'departamento_nuevo' =>
                $nuevo['departamento'] ?? null,

            'ubicacion_nueva' =>
                $nuevo['ubicacion'] ?? null,

            'fecha_asignacion_anterior' =>
                $anterior['fecha'] ?? null,

            'tipo_asignacion_anterior' =>
                $anterior['tipo'] ?? null,
        ];
    }

    private function fecha($valor): ?string
    {
        if (!$valor) {
            return null;
        }

        return Carbon::parse($valor)->format('d/m/Y');
    }
}
