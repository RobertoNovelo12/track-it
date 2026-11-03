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

        $actas = $consulta
            ->get()
            ->map(function ($registro) use ($tipo) {
                $id = (int) $registro->registro_id;

                return $tipo === 'asignaciones'
                    ? $this->asignacion($id)
                    : $this->reasignacion($id);
            })
            ->filter()
            ->values();

        return $this->agrupar($actas);
    }

    /*
     * Actas de movimientos concretos (al terminar de asignar o
     * reasignar se descarga su acta). Con varias asignaciones hechas
     * juntas sale una sola acta con todos los equipos.
     */
    public function porIds(string $tipo, array $ids): Collection
    {
        $ids = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if (! in_array($tipo, ['asignaciones', 'reasignaciones'], true) || $ids->isEmpty()) {
            return collect();
        }

        $actas = $ids
            ->map(fn (int $id) => $tipo === 'asignaciones'
                ? $this->asignacion($id)
                : $this->reasignacion($id))
            ->filter()
            ->values();

        return $this->agrupar($actas);
    }

    /*
     * Une en una sola acta las asignaciones de un mismo lote: mismo
     * responsable o departamento, misma ubicación, mismo tipo, misma
     * persona que asignó y misma fecha y hora (así las guarda
     * «Asignar varios»). Cada acta lleva 'equipos' (uno o varios).
     */
    private function agrupar(Collection $actas): Collection
    {
        $grupos = [];

        foreach ($actas as $acta) {
            $clave = $acta['lote'] ?? ('unico-'.($acta['tipo'] ?? '').'-'.($acta['referencia'] ?? spl_object_id((object) $acta)));

            if (! isset($grupos[$clave])) {
                $acta['equipos'] = [$acta['equipo']];
                $acta['referencias'] = [$acta['referencia']];
                $grupos[$clave] = $acta;

                continue;
            }

            $grupos[$clave]['equipos'][] = $acta['equipo'];
            $grupos[$clave]['referencias'][] = $acta['referencia'];
        }

        return collect(array_values($grupos));
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
                'unidades_organizativas as ar',
                'ar.id_unidad',
                '=',
                'a.id_unidad'
            )

            ->leftJoin(
                'ubicaciones as ub',
                'ub.id_ubicacion',
                '=',
                'a.id_ubicacion'
            )
            ->leftJoin(
                'sedes as sede_hotel',
                'sede_hotel.id_sede',
                '=',
                'ar.id_sede'
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
                'ar.nombre as hotel_directo',
                'dep.nombre as departamento',
                'ub.nombre as ubicacion',
                'sede_hotel.nombre as sede_directa',
                'sede_ubicacion.nombre as sede_ubicacion',
                'tipo.nombre as tipo_asignacion',
            ]);

        if (!$registro) {
            return null;
        }

        $hotel = $registro->hotel_directo;

        $sede = $registro->sede_directa
            ?: $registro->sede_ubicacion;

        return [
            'id_asignacion' => $registro->id_asignacion,
            'nombre' => $registro->nombre_colaborador,
            'numero' => $registro->numero_colaborador,
            'sede' => $sede,
            'hotel' => $hotel,
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

            // Clave del lote (ver agrupar()).
            'lote' => 'asignacion-'.md5(implode('|', [
                $registro->asignado_por ?? '',
                (string) ($registro->fecha_asignacion ?? ''),
                $registro->id_usuario ?? '',
                mb_strtolower(trim((string) ($registro->nombre_colaborador ?? ''))),
                $registro->id_unidad ?? '',
                $registro->id_departamento ?? '',
                $registro->id_ubicacion ?? '',
                $registro->id_tipo_asignacion ?? '',
            ])),
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
            'hotel_anterior' => $anterior['hotel'] ?? null,

            'departamento_anterior' =>
                $anterior['departamento'] ?? null,

            'ubicacion_anterior' =>
                $anterior['ubicacion'] ?? null,

            'sede_nueva' => $nuevo['sede'] ?? null,
            'hotel_nuevo' => $nuevo['hotel'] ?? null,

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
