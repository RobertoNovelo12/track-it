<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Exports\InventarioEquiposExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Exclusivo de la exportación del inventario. No modifica equipos ni movimientos.
 */
class InventarioDocumentoService
{
    public function descargar(array $equipos, string $formato = 'pdf')
    {
        abort_unless(in_array($formato, ['pdf', 'xlsx'], true), 422);
        abort_if($equipos === [], 404);
        $fecha = now()->format('Ymd_His');

        if ($formato === 'xlsx') {
            $contenido = Excel::raw(new InventarioEquiposExport($equipos), \Maatwebsite\Excel\Excel::XLSX);
            return response()->streamDownload(
                static function () use ($contenido) { echo $contenido; },
                'inventario_equipos_' . $fecha . '.xlsx',
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );
        }

        if (count($equipos) === 1 && ($equipos[0]['estado'] ?? '') === 'Baja') {
            $id = (int) ($equipos[0]['id'] ?? 0);
            $baja = DB::table('bajas')->where('id_equipo', $id)->orderByDesc('id_baja')->first();
            return app(DescargaActaBajaService::class)->descargar($id, $baja);
        }

        if (count($equipos) === 1) {
            $pdf = Pdf::loadView('inventario-exportacion.ficha', [
                'equipo' => $equipos[0],
                'generadoEn' => now(),
            ])->setPaper('letter', 'portrait');

            return response()->streamDownload(
                static function () use ($pdf) { echo $pdf->output(); },
                'ficha_' . preg_replace('/[^A-Za-z0-9_-]/', '-', $equipos[0]['codigo']) . '.pdf',
                ['Content-Type' => 'application/pdf']
            );
        }

        $pdf = Pdf::loadView('inventario-exportacion.tabla', [
            'equipos' => $equipos,
            'generadoEn' => now(),
        ])->setPaper('letter', 'portrait');

        return response()->streamDownload(
            static function () use ($pdf) { echo $pdf->output(); },
            'inventario_equipos_' . $fecha . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function preparar(Collection $filas): array
    {
        $ids = $filas->pluck('id_equipo')->map(fn ($v) => (int) $v)->unique()->values()->all();
        if ($ids === []) {
            return [];
        }

        $equipos = collect();
        $asignaciones = collect();
        $bajas = collect();
        $movimientos = collect();

        foreach (array_chunk($ids, 400) as $lote) {
            $equipos = $equipos->merge(
                DB::table('equipos as e')
                    ->leftJoin('tipos_equipo as t', 't.id_tipo_equipo', '=', 'e.id_tipo_equipo')
                    ->leftJoin('marcas as ma', 'ma.id_marca', '=', 'e.id_marca')
                    ->leftJoin('modelos as mo', 'mo.id_modelo', '=', 'e.id_modelo')
                    ->leftJoin('estados_equipo as es', 'es.id_estado_equipo', '=', 'e.id_estado_activo')
                    ->leftJoin('catalogo_valores as co', 'co.id_valor', '=', 'e.id_condicion_activo')
                    ->leftJoin('unidades_organizativas as u', 'u.id_unidad', '=', 'e.id_unidad')
                    ->leftJoin('departamentos as dep', 'dep.id_departamento', '=', 'e.id_departamento')

                    ->leftJoin('ubicaciones as ub', 'ub.id_ubicacion', '=', 'e.id_ubicacion')
                    ->leftJoin('proveedores as pr', 'pr.id_proveedor', '=', 'e.id_proveedor')
                    ->whereIn('e.id_equipo', $lote)
                    ->select([
                        'e.*', 't.nombre as tipo_nombre', 'ma.nombre as marca_nombre',
                        'mo.nombre as modelo_nombre', 'es.nombre as estado_nombre',
                        'es.clave as estado_clave', 'co.nombre as condicion_nombre',
                        'u.nombre as hotel_nombre', 'dep.nombre as departamento_nombre',
                        'u.nombre as area_nombre', 'ub.nombre as ubicacion_nombre',
                        'pr.nombre as proveedor_nombre',
                    ])->get()
            );

            $asignaciones = $asignaciones->merge(
                DB::table('asignaciones as a')
                    ->leftJoin('unidades_organizativas as u', 'u.id_unidad', '=', 'a.id_unidad')
                    ->leftJoin('departamentos as d', 'd.id_departamento', '=', 'a.id_departamento')

                    ->leftJoin('ubicaciones as ub', 'ub.id_ubicacion', '=', 'a.id_ubicacion')
                    ->when(Schema::hasColumn('asignaciones', 'id_usuario'), function ($consulta) {
                        $consulta->leftJoin('usuarios as usuario_asignado', 'usuario_asignado.id_usuario', '=', 'a.id_usuario');
                    })
                    ->leftJoin('catalogo_valores as ta', 'ta.id_valor', '=', 'a.id_tipo_asignacion')
                    ->whereIn('a.id_equipo', $lote)
                    ->orderByDesc('a.fecha_asignacion')
                    ->orderByDesc('a.id_asignacion')
                    ->get([
                        ... (Schema::hasColumn('asignaciones', 'id_usuario') ? ['usuario_asignado.username as usuario_nombre', 'usuario_asignado.correo as usuario_correo'] : []),
                        'ta.nombre as tipo_asignacion', 'a.observaciones', 'a.id_equipo', 'a.nombre_colaborador', 'a.numero_colaborador',
                        'a.fecha_asignacion', 'a.fecha_fin', 'a.id_asignacion',
                        'u.nombre as hotel_nombre', 'd.nombre as departamento_nombre',
                        'u.nombre as area_nombre', 'ub.nombre as ubicacion_nombre',
                    ])
            );

            $bajas = $bajas->merge(
                DB::table('bajas as b')
                    ->leftJoin('catalogo_valores as motivo', 'motivo.id_valor', '=', 'b.id_motivo_baja')
                    ->whereIn('b.id_equipo', $lote)
                    ->orderByDesc('b.fecha_baja')
                    ->orderByDesc('b.id_baja')
                    ->get(['b.id_equipo', 'b.fecha_baja', 'motivo.nombre as motivo_baja', 'b.comentarios'])
            );

            $movimientos = $movimientos->merge(
                DB::table('movimientos as m')
                    ->leftJoin('catalogo_valores as cv', 'cv.id_valor', '=', 'm.id_tipo_movimiento')
                    ->whereIn('m.id_equipo', $lote)
                    ->orderBy('m.fecha_hora')->get(['m.*', 'cv.clave', 'cv.nombre'])
            );
        }

        $porId = $equipos->keyBy('id_equipo');
        $asignaciones = $asignaciones->groupBy('id_equipo');
        $todasBajas = $bajas->groupBy('id_equipo');
        $bitacora = $movimientos->groupBy('id_equipo');
        $bajas = $bajas->unique('id_equipo')->keyBy('id_equipo');
        $reasignaciones = $movimientos->filter(function ($m) {
            $texto = mb_strtolower((string) $m->clave . ' ' . (string) $m->nombre);
            return str_contains($texto, 'reasign');
        })->pluck('id_equipo')->map(fn ($id) => (int) $id)->flip();

        $especificaciones = $this->cargarEspecificaciones($equipos);
        $programas = $this->cargarProgramas($ids);

        // Mantener exactamente el orden que devolvió la tabla con sus filtros.
        return $filas->map(function ($fila) use ($porId, $asignaciones, $bajas, $reasignaciones, $especificaciones, $todasBajas, $bitacora, $programas) {
            $e = $porId->get((int) $fila->id_equipo);
            if (!$e) {
                return null;
            }

            $id = (int) $e->id_equipo;
            $historial = $asignaciones->get($id, collect());
            $actual = $historial->first(fn ($a) => $a->fecha_fin === null);
            $ultima = $historial->first();
            $deBaja = $bajas->has($id) || strtoupper((string) $e->estado_clave) === 'BAJA';

            $situacion = $deBaja ? 'Baja'
                : ($actual
                    ? ($reasignaciones->has($id) || str_contains(mb_strtolower((string) $actual->tipo_asignacion), 'reasign') ? 'Reasignado' : 'Asignado')
                    : ($ultima ? 'Sin asignación vigente' : 'Alta sin asignación'));

            // Mientras está en baja, el último responsable es histórico, NO el actual.
            $responsable = !$deBaja && $actual ? $actual->nombre_colaborador : null;
            $ubicacionBase = $this->unir([$e->hotel_nombre, $e->departamento_nombre, $e->ubicacion_nombre]);
            $ubicacionActual = $actual && !$deBaja
                ? $this->unir([
                    $actual->hotel_nombre ?: $e->hotel_nombre,
                    $actual->departamento_nombre ?: $e->departamento_nombre,
                    $actual->ubicacion_nombre ?: $e->ubicacion_nombre,
                ])
                : $ubicacionBase;

            return [
                'historial' => $this->historial($e, $historial, $todasBajas->get($id, collect()), $bitacora->get($id, collect())),
                'id' => $id,
                'codigo' => $this->texto($e->codigo_inventario),
                'nombre' => $this->texto($e->nombre_equipo),
                'tipo' => $this->texto($e->tipo_nombre),
                'marca' => $this->texto($e->marca_nombre),
                'modelo' => $this->texto($e->modelo_nombre),
                'serie' => $this->texto($e->numero_serie),
                'service_tag' => $this->texto($e->service_tag),
                'host' => $this->texto($e->host),
                'ip' => $this->texto($e->direccion_ip),
                'mac' => $this->texto($e->direccion_mac),
                'estado' => $deBaja ? 'Baja' : $this->texto($e->estado_nombre),
                'situacion' => $situacion,
                'condicion' => $this->texto($e->condicion_nombre),
                'usuario_nombre' => $this->texto(!$deBaja && $actual ? ($actual->usuario_nombre ?? $actual->nombre_colaborador) : null),
                'usuario_correo' => $this->texto(!$deBaja && $actual ? ($actual->usuario_correo ?? null) : null),
                'responsable' => trim((string) ($responsable ?? '')),
                'ultimo_responsable' => $this->texto($ultima?->nombre_colaborador),
                'hotel' => $this->texto($actual && !$deBaja ? ($actual->hotel_nombre ?: $e->hotel_nombre) : $e->hotel_nombre),
                'departamento' => $this->texto($actual && !$deBaja ? ($actual->departamento_nombre ?: $e->departamento_nombre) : $e->departamento_nombre),
                'area' => $this->texto($actual && !$deBaja ? ($actual->area_nombre ?: $e->area_nombre) : $e->area_nombre),
                'ubicacion' => $this->texto($ubicacionActual),
                'fecha_asignacion' => $this->texto($actual?->fecha_asignacion),
                'fecha_baja' => $this->texto($bajas->get($id)?->fecha_baja),
                'motivo_baja' => $this->texto($bajas->get($id)?->motivo_baja),
                'comentarios_baja' => $this->texto($bajas->get($id)?->comentarios),
                'fecha_registro' => $this->texto($e->fecha_registro),
                'fecha_compra' => $this->texto($e->fecha_compra),
                'fecha_garantia' => $this->texto($e->fecha_fin_garantia),
                'factura' => $this->texto($e->numero_factura),
                'proveedor' => $this->texto($e->proveedor_nombre),
                'descripcion' => $this->texto($e->comentarios),
                'especificaciones' => $especificaciones[$id] ?? [],
                // Programas instalados: [['nombre' => 'Office v2021', 'detalle' => 'Microsoft · De pago']].
                'programas' => $programas[$id] ?? [],
            ];
        })->filter()->values()->all();
    }

    private function historial(object $equipo, Collection $asignaciones, Collection $bajas, Collection $movimientos): array
    {
        $eventos = [[
            'fecha' => $this->texto($equipo->fecha_registro),
            'tipo' => 'Alta en inventario',
            'detalle' => $this->texto($equipo->comentarios),
        ]];
        foreach ($asignaciones as $a) {
            $detalle = $this->unir([
                $a->nombre_colaborador, $a->numero_colaborador,
                $a->hotel_nombre, $a->departamento_nombre, $a->ubicacion_nombre,
                $a->observaciones,
            ]);
            $eventos[] = ['fecha' => $this->texto($a->fecha_asignacion), 'tipo' => $a->tipo_asignacion ?: 'Asignación', 'detalle' => $detalle];
            if ($a->fecha_fin) {
                $eventos[] = ['fecha' => $this->texto($a->fecha_fin), 'tipo' => 'Fin de asignación', 'detalle' => $detalle];
            }
        }
        foreach ($movimientos as $m) {
            $eventos[] = [
                'fecha' => $this->texto($m->fecha_hora),
                'tipo' => 'Movimiento: '.($m->nombre ?: $m->clave),
                'detalle' => $this->unir([$m->motivo ?? null, $m->observaciones ?? null]),
            ];
        }
        foreach ($bajas as $b) {
            $eventos[] = ['fecha' => $this->texto($b->fecha_baja), 'tipo' => 'Baja', 'detalle' => $this->unir([$b->motivo_baja, $b->comentarios])];
        }
        return collect($eventos)->sortBy('fecha')->values()->all();
    }

    private function cargarEspecificaciones(Collection $equipos): array
    {
        $tipos = $equipos->pluck('id_tipo_equipo')->filter()->unique()->values()->all();
        $ids = $equipos->pluck('id_equipo')->map(fn ($id) => (int) $id)->all();
        if ($tipos === []) {
            return [];
        }

        $campos = DB::table('tipo_equipo_campos as t')
            ->join('campos_equipo as c', 'c.id_campo', '=', 't.id_campo')
            ->whereIn('t.id_tipo_equipo', $tipos)
            ->where('t.visible', true)
            ->where('c.activo', true)
            ->orderBy('t.orden')
            ->get([
                't.id_tipo_equipo', 't.id_tipo_campo', 't.id_campo',
                't.origen_dato', 't.tabla_destino', 't.columna_destino',
                't.etiqueta', 'c.nombre as nombre_campo', 'c.tipo_control',
            ]);

        $catalogos = DB::table('catalogo_valores')->pluck('nombre', 'id_valor')->all();
        $opciones = DB::table('tipo_equipo_campo_opciones')
            ->whereIn('id_tipo_campo', $campos->pluck('id_tipo_campo')->all())
            ->get(['id_tipo_campo', 'id_opcion', 'nombre'])
            ->groupBy('id_tipo_campo')
            ->map(fn ($grupo) => $grupo->pluck('nombre', 'id_opcion')->all())
            ->all();

        $atributos = collect();
        foreach (array_chunk($ids, 400) as $lote) {
            $atributos = $atributos->merge(DB::table('atributos_equipo')
                ->whereIn('id_equipo', $lote)->get(['id_equipo', 'id_campo', 'nombre', 'valor']));
        }
        $porAtributo = $atributos->groupBy('id_equipo');
        $porTipo = $equipos->groupBy('id_tipo_equipo');

        $tablas = [];
        foreach ($campos->where('origen_dato', 'tabla_tipo')->groupBy('tabla_destino') as $tabla => $grupo) {
            if (!$this->identificador($tabla) || !Schema::hasTable($tabla) || !Schema::hasColumn($tabla, 'id_equipo')) {
                continue;
            }
            $idsTipo = $porTipo->only($grupo->pluck('id_tipo_equipo')->unique()->all())
                ->flatten(1)->pluck('id_equipo')->all();
            $rows = collect();
            foreach (array_chunk($idsTipo, 400) as $lote) {
                $rows = $rows->merge(DB::table($tabla)->whereIn('id_equipo', $lote)->get());
            }
            $tablas[$tabla] = $rows->keyBy('id_equipo');
        }

        $resultado = [];
        foreach ($equipos as $e) {
            $id = (int) $e->id_equipo;
            $specs = [];
            $config = $campos->where('id_tipo_equipo', $e->id_tipo_equipo);

            foreach ($config as $campo) {
                $nombre = trim((string) ($campo->etiqueta ?: $campo->nombre_campo));
                $valor = null;

                if ($campo->origen_dato === 'equipos') {
                    $columna = (string) $campo->columna_destino;
                    // These fields already appear in the main sections. Their IDs
                    // belong to separate tables, not to catalogo_valores.
                    if (in_array($columna, [
                        'nombre_equipo', 'id_tipo_equipo', 'id_marca', 'id_modelo',
                        'id_estado_activo', 'id_condicion_activo', 'id_unidad',
                        'id_departamento', 'id_ubicacion', 'id_proveedor',
                        'codigo_inventario', 'numero_serie', 'numero_factura',
                        'fecha_compra', 'fecha_fin_garantia', 'fecha_registro',
                        'host', 'direccion_ip', 'direccion_mac', 'service_tag', 'comentarios',
                    ], true)) {
                        continue;
                    }
                    if ($this->identificador($columna)) {
                        $valor = $e->{$columna} ?? null;
                    }
                } elseif ($campo->origen_dato === 'tabla_tipo') {
                    $tabla = (string) $campo->tabla_destino;
                    $columna = (string) $campo->columna_destino;
                    if ($this->identificador($columna) && isset($tablas[$tabla])) {
                        $valor = $tablas[$tabla]->get($id)?->{$columna} ?? null;
                    }
                } elseif ($campo->origen_dato === 'atributo') {
                    $valor = $porAtributo->get($id, collect())
                        ->first(fn ($a) => (int) $a->id_campo === (int) $campo->id_campo)?->valor;
                }

                if ($valor === null || $valor === '') {
                    continue;
                }

                if ($campo->tipo_control === 'boolean') {
                    $valor = filter_var($valor, FILTER_VALIDATE_BOOLEAN) ? 'Sí' : 'No';
                } elseif ($campo->tipo_control === 'select') {
                    $valor = $opciones[$campo->id_tipo_campo][$valor]
                        ?? $catalogos[$valor] ?? $valor;
                }

                $specs[$nombre] = $this->texto($valor);
            }

            // Incluye también atributos antiguos sin vinculación al constructor actual.
            foreach ($porAtributo->get($id, collect()) as $a) {
                if (blank($a->valor) || blank($a->nombre) || isset($specs[$a->nombre])) {
                    continue;
                }
                $specs[(string) $a->nombre] = $this->texto($a->valor);
            }

            $resultado[$id] = $specs;
        }

        return $resultado;
    }

    /**
     * Programas instalados por equipo (Administración › Programas).
     */
    private function cargarProgramas(array $ids): array
    {
        if ($ids === [] || ! app(ProgramasService::class)->disponible()) {
            return [];
        }

        $licencias = ProgramasService::LICENCIAS;
        $resultado = [];

        foreach (array_chunk($ids, 400) as $lote) {
            DB::table('equipo_programa as ep')
                ->join('programas as p', 'p.id_programa', '=', 'ep.id_programa')
                ->whereIn('ep.id_equipo', $lote)
                ->orderBy('p.nombre')
                ->get(['ep.id_equipo', 'p.nombre', 'p.version', 'p.empresa', 'p.plataforma', 'p.licencia', 'p.requiere_clave'])
                ->each(function ($programa) use (&$resultado, $licencias): void {
                    $version = trim((string) ($programa->version ?? ''));

                    $resultado[(int) $programa->id_equipo][] = [
                        'nombre' => trim($programa->nombre . ($version !== '' ? ' v' . ltrim($version, 'vV') : '')),
                        'detalle' => $this->unir([
                            $programa->empresa,
                            $programa->plataforma,
                            ($programa->licencia ?? 'gratuita') !== 'gratuita' ? ($licencias[$programa->licencia] ?? null) : null,
                            $programa->requiere_clave ? 'Requiere clave' : null,
                        ]),
                    ];
                });
        }

        return $resultado;
    }

    private function identificador(?string $valor): bool
    {
        return is_string($valor) && (bool) preg_match('/^[a-z_][a-z0-9_]*$/i', $valor);
    }

    private function unir(array $partes): string
    {
        return implode(' / ', array_values(array_unique(array_filter(array_map(
            fn ($x) => trim((string) ($x ?? '')),
            $partes
        ), fn ($x) => $x !== ''))));
    }

    private function texto(mixed $valor): string
    {
        return ($valor === null || trim((string) $valor) === '') ? '—' : trim((string) $valor);
    }
}
