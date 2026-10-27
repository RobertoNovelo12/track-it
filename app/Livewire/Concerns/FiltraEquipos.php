<?php

namespace App\Livewire\Concerns;

use App\Livewire\EquiposIndex;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\TipoEquipo;
use App\Services\SystemCacheService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/** Filtros y catálogos comunes a Inventario y la selección de bajas múltiples. */
trait FiltraEquipos
{
    private function applyFilters(
        Builder $query
    ): void {
        $this->applySituacion($query);
        $this->applyPrograma($query);

        /*
         * Tanto null como '' significan:
         * "sin filtro".
         */

        $tipoActivo =
            trim(
                (string) (
                    $this->tipoActivo
                    ?? ''
                )
            );


        $marca =
            trim(
                (string) (
                    $this->marca
                    ?? ''
                )
            );


        $modelo =
            trim(
                (string) (
                    $this->modelo
                    ?? ''
                )
            );


        $nombreEquipo =
            trim(
                (string) (
                    $this->nombreEquipo
                    ?? ''
                )
            );


        $numeroSerie =
            trim(
                (string) (
                    $this->numeroSerie
                    ?? ''
                )
            );


        $serviceTag =
            trim(
                (string) (
                    $this->serviceTag
                    ?? ''
                )
            );


        $direccionIp =
            trim(
                (string) (
                    $this->direccionIp
                    ?? ''
                )
            );


        $estado =
            trim(
                (string) (
                    $this->estado
                    ?? ''
                )
            );


        $area =
            trim(
                (string) (
                    $this->area
                    ?? ''
                )
            );


        /*
        |--------------------------------------------------------------------------
        | TIPO DE ACTIVO
        |--------------------------------------------------------------------------
        */

        $query->when(
            $tipoActivo !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.id_tipo_equipo',
                    (int) $tipoActivo
                )
        );


        /*
        |--------------------------------------------------------------------------
        | MARCA
        |--------------------------------------------------------------------------
        */

        $query->when(
            $marca !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.id_marca',
                    (int) $marca
                )
        );


        /*
        |--------------------------------------------------------------------------
        | MODELO
        |--------------------------------------------------------------------------
        */

        $query->when(
            $modelo !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.id_modelo',
                    (int) $modelo
                )
        );


        /*
        |--------------------------------------------------------------------------
        | NOMBRE DE EQUIPO
        |--------------------------------------------------------------------------
        */

        $query->when(
            $nombreEquipo !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.nombre_equipo',
                    'ilike',
                    '%'
                    . $nombreEquipo
                    . '%'
                )
        );


        /*
        |--------------------------------------------------------------------------
        | NÚMERO DE SERIE
        |--------------------------------------------------------------------------
        */

        $query->when(
            $numeroSerie !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.numero_serie',
                    'ilike',
                    '%'
                    . $numeroSerie
                    . '%'
                )
        );


        /*
        |--------------------------------------------------------------------------
        | SERVICE TAG
        |--------------------------------------------------------------------------
        */

        $query->when(
            $serviceTag !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.service_tag',
                    'ilike',
                    '%'
                    . $serviceTag
                    . '%'
                )
        );


        /*
        |--------------------------------------------------------------------------
        | DIRECCIÓN IP
        |--------------------------------------------------------------------------
        */

        $query->when(
            $direccionIp !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.direccion_ip',
                    'ilike',
                    '%'
                    . $direccionIp
                    . '%'
                )
        );


        /*
        |--------------------------------------------------------------------------
        | ESTADO
        |--------------------------------------------------------------------------
        */

        $query->when(
            $estado !== '',
            fn (Builder $q) =>
                $q->where(
                    'e.id_estado_activo',
                    (int) $estado
                )
        );


        /*
        |--------------------------------------------------------------------------
        | HOTEL / DEPARTAMENTO
        |--------------------------------------------------------------------------
        */

        $query->when(
            $area !== '',
            function (
                Builder $q
            ) use (
                $area
            ): void {
                $areaId =
                    (int) $area;


                $q->where(
                    function (
                        Builder $sub
                    ) use (
                        $areaId
                    ): void {
                        $sub
                            ->where(
                                'e.id_unidad',
                                $areaId
                            );
                    }
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | NÚMERO DE FACTURA
        |--------------------------------------------------------------------------
        */

        $numeroFactura = trim((string) ($this->numeroFactura ?? ''));

        $query->when(
            $numeroFactura !== '',
            fn (Builder $q) => $q->where(
                'e.numero_factura',
                'ilike',
                '%' . $numeroFactura . '%'
            )
        );

        /*
        |--------------------------------------------------------------------------
        | FILTROS ESPECÍFICOS DEL TIPO
        |--------------------------------------------------------------------------
        */

        if ($tipoActivo !== '') {
            $this->applyCamposTipo(
                $query,
                (int) $tipoActivo
            );
        }
    }

    private function camposTipoActivos(): array
    {
        if (trim((string) ($this->tipoActivo ?? '')) === '') {
            return [];
        }

        $activos = [];

        foreach ($this->camposTipo as $id => $valor) {
            if (! is_numeric($id) || is_array($valor) || $valor === null) {
                continue;
            }

            $valor = trim((string) $valor);

            if ($valor === '') {
                continue;
            }

            $activos[(string) (int) $id] = $valor;
        }

        ksort($activos);

        return $activos;
    }

    private function getCamposFiltrables(int $tipoId): array
    {
        if ($tipoId <= 0) {
            return [];
        }

        return app(SystemCacheService::class)->rememberSimple(
            'catalogos',
            'filtros.campos_tipo.' . $tipoId . '.v1',
            fn (): array => $this->leerCamposFiltrables($tipoId),
            24
        );
    }

    private function leerCamposFiltrables(int $tipoId): array
    {
        $filas = DB::table('tipo_equipo_campos as tec')
            ->join('campos_equipo as ce', 'ce.id_campo', '=', 'tec.id_campo')
            ->join('tipo_equipo_secciones as tes', 'tes.id_seccion', '=', 'tec.id_seccion')
            ->where('tec.id_tipo_equipo', $tipoId)
            ->where('ce.activo', true)
            ->where('tec.visible', true)
            ->whereIn('tec.origen_dato', ['equipos', 'tabla_tipo', 'atributo'])
            ->whereNotIn('tes.clave', self::SECCIONES_NO_ESPECIFICAS)
            ->orderBy('tes.orden')
            ->orderBy('tec.orden')
            ->orderBy('tec.id_tipo_campo')
            ->get([
                'tec.id_tipo_campo',
                'tec.id_campo',
                'tec.origen_dato',
                'tec.tabla_destino',
                'tec.columna_destino',
                'tec.etiqueta',
                'tec.fuente_opciones',
                'tec.id_catalogo',
                'ce.nombre as nombre_base',
                'ce.tipo_control',
            ]);

        $identificador = '/^[a-z_][a-z0-9_]*$/';
        $campos = [];

        foreach ($filas as $fila) {
            $origen = (string) $fila->origen_dato;
            $tabla = (string) ($fila->tabla_destino ?? '');
            $columna = (string) ($fila->columna_destino ?? '');
            $control = (string) $fila->tipo_control;

            if (! in_array($control, ['text', 'textarea', 'number', 'date', 'boolean', 'select'], true)) {
                continue;
            }

            /*
             * Los nombres de tabla/columna vienen de la configuración y se
             * usan como identificadores SQL: solo se aceptan nombres simples.
             */
            if ($origen === 'equipos') {
                if (
                    ! preg_match($identificador, $columna)
                    || ($tabla !== '' && $tabla !== 'equipos')
                    || in_array($columna, self::COLUMNAS_FILTRO_BASE, true)
                ) {
                    continue;
                }
            } elseif ($origen === 'tabla_tipo') {
                if (! preg_match($identificador, $tabla) || ! preg_match($identificador, $columna)) {
                    continue;
                }
            }

            $etiqueta = trim((string) ($fila->etiqueta ?? ''));

            $campos[] = [
                'id' => (string) $fila->id_tipo_campo,
                'id_campo' => (int) $fila->id_campo,
                'etiqueta' => $etiqueta !== '' ? $etiqueta : (string) $fila->nombre_base,
                'control' => $control === 'textarea' ? 'text' : $control,
                'origen' => $origen,
                'tabla' => $tabla,
                'columna' => $columna,
                'opciones' => match ($control) {
                    'boolean' => [
                        ['value' => '1', 'label' => 'Sí'],
                        ['value' => '0', 'label' => 'No'],
                    ],
                    'select' => $this->opcionesCampo($fila),
                    default => [],
                },
            ];
        }

        return $campos;
    }

    private function opcionesCampo(object $fila): array
    {
        $fuente = (string) ($fila->fuente_opciones ?? '');

        if ($fuente === 'personalizado') {
            $items = DB::table('tipo_equipo_campo_opciones')
                ->where('id_tipo_campo', $fila->id_tipo_campo)
                ->where('activo', true)
                ->orderBy('orden')
                ->orderBy('id_opcion')
                ->get(['id_opcion as value', 'nombre as label']);
        } elseif ($fuente === 'catalogo') {
            $items = DB::table('catalogo_valores')
                ->where('id_catalogo', $fila->id_catalogo)
                ->where('activo', true)
                ->orderBy('orden')
                ->orderBy('nombre')
                ->get(['id_valor as value', 'nombre as label']);
        } else {
            $fuentes = [
                'estados_equipo' => 'id_estado_equipo',
                'marcas' => 'id_marca',
                'modelos' => 'id_modelo',
                'proveedores' => 'id_proveedor',
                'unidades_organizativas' => 'id_unidad',
                'departamentos' => 'id_departamento',
            ];

            if (! array_key_exists($fuente, $fuentes)) {
                return [];
            }

            $items = DB::table($fuente)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get([$fuentes[$fuente] . ' as value', 'nombre as label']);
        }

        return $items
            ->map(fn ($item): array => [
                'value' => (string) $item->value,
                'label' => (string) $item->label,
            ])
            ->values()
            ->all();
    }

    private function applyCamposTipo(Builder $query, int $tipoId): void
    {
        $activos = $this->camposTipoActivos();

        if ($activos === []) {
            return;
        }

        $definiciones = collect($this->getCamposFiltrables($tipoId))->keyBy('id');

        foreach ($activos as $id => $valor) {
            $campo = $definiciones->get((string) $id);

            if (! $campo) {
                continue;
            }

            if ($campo['origen'] === 'equipos') {
                $this->condicionCampo($query, '"e"."' . $campo['columna'] . '"', $campo['control'], $valor);

                continue;
            }

            if ($campo['origen'] === 'tabla_tipo') {
                $query->whereExists(function (Builder $sub) use ($campo, $valor): void {
                    $sub->selectRaw('1')
                        ->from($campo['tabla'] . ' as ft')
                        ->whereColumn('ft.id_equipo', 'e.id_equipo');

                    $this->condicionCampo($sub, '"ft"."' . $campo['columna'] . '"', $campo['control'], $valor);
                });

                continue;
            }

            // atributo: atributos_equipo (campos agregados desde el constructor)
            $query->whereExists(function (Builder $sub) use ($campo, $valor): void {
                $sub->selectRaw('1')
                    ->from('atributos_equipo as fa')
                    ->whereColumn('fa.id_equipo', 'e.id_equipo')
                    ->where('fa.id_campo', $campo['id_campo']);

                $this->condicionCampo($sub, '"fa"."valor"', $campo['control'], $valor);
            });
        }
    }

    private function condicionCampo(Builder $query, string $columna, string $control, string $valor): void
    {
        $texto = 'CAST(' . $columna . ' AS TEXT)';

        switch ($control) {
            case 'select':
                $query->whereRaw($texto . ' = ?', [$valor]);
                break;

            case 'boolean':
                $query->whereRaw(
                    $texto . ' IN (?, ?)',
                    $valor === '1' ? ['1', 'true'] : ['0', 'false']
                );
                break;

            case 'number':
                if (! is_numeric($valor)) {
                    $query->whereRaw('1 = 0');
                    break;
                }

                // CASE evita convertir valores no numéricos (atributos libres).
                $query->whereRaw(
                    '(CASE WHEN ' . $texto . " ~ '^-{0,1}[0-9]+([.][0-9]+){0,1}$' THEN CAST(" . $texto . ' AS NUMERIC) END) = CAST(? AS NUMERIC)',
                    [$valor]
                );
                break;

            case 'date':
                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                    $query->whereRaw('1 = 0');
                    break;
                }

                $query->whereRaw('LEFT(' . $texto . ', 10) = ?', [$valor]);
                break;

            default:
                $query->whereRaw($texto . ' ILIKE ?', ['%' . addcslashes($valor, '%_\\') . '%']);
        }
    }

    private function applySituacion(
        Builder $query
    ): void {
        $situacion = (string) $this->situacion;

        if (! array_key_exists($situacion, EquiposIndex::SITUACIONES)) {
            return;
        }

        $hoy = now()->toDateString();

        /*
         * Todas las situaciones se calculan sobre el inventario
         * vigente: sin baja histórica ni estado BAJA.
         */
        $query
            ->where(function (Builder $q): void {
                $q->whereNull('ee.clave')
                    ->orWhere('ee.clave', '<>', 'BAJA');
            })
            ->whereNotExists(function (Builder $q): void {
                $q->selectRaw('1')
                    ->from('bajas as b_situacion')
                    ->whereColumn('b_situacion.id_equipo', 'e.id_equipo');
            });

        match ($situacion) {
            'asignados' => $query->whereExists(function (Builder $q): void {
                $q->selectRaw('1')
                    ->from('asignaciones as a_situacion')
                    ->whereColumn('a_situacion.id_equipo', 'e.id_equipo')
                    ->whereNull('a_situacion.fecha_fin');
            }),

            // Vigentes sin asignación activa (tarjeta «Equipos no asignados»).
            'sin_asignar' => $query->whereNotExists(function (Builder $q): void {
                $q->selectRaw('1')
                    ->from('asignaciones as a_situacion')
                    ->whereColumn('a_situacion.id_equipo', 'e.id_equipo')
                    ->whereNull('a_situacion.fecha_fin');
            }),

            'en_mantenimiento' => $query->whereExists(function (Builder $q): void {
                $q->selectRaw('1')
                    ->from('mantenimientos as m_situacion')
                    ->join('catalogo_valores as cv_situacion', 'cv_situacion.id_valor', '=', 'm_situacion.id_estado_mantenimiento')
                    ->whereColumn('m_situacion.id_equipo', 'e.id_equipo')
                    ->where('cv_situacion.clave', 'EN_PROCESO');
            }),

            'mantenimiento_vencido' => $query->whereExists(function (Builder $q) use ($hoy): void {
                $q->selectRaw('1')
                    ->from('mantenimientos as m_situacion')
                    ->join('catalogo_valores as cv_situacion', 'cv_situacion.id_valor', '=', 'm_situacion.id_estado_mantenimiento')
                    ->whereColumn('m_situacion.id_equipo', 'e.id_equipo')
                    ->whereNotIn('cv_situacion.clave', ['COMPLETADO', 'CANCELADO'])
                    ->whereNotNull('m_situacion.fecha_proximo_mantenimiento')
                    ->whereDate('m_situacion.fecha_proximo_mantenimiento', '<', $hoy);
            }),

            'garantia_vencida' => $query
                ->whereNotNull('e.fecha_fin_garantia')
                ->whereDate('e.fecha_fin_garantia', '<', $hoy),

            'garantia_por_vencer' => $query
                ->whereNotNull('e.fecha_fin_garantia')
                ->whereBetween('e.fecha_fin_garantia', [
                    $hoy,
                    now()->addDays(30)->toDateString(),
                ]),

            default => null,
        };
    }

    private function getTiposActivo(): array
    {
        return app(SystemCacheService::class)->rememberSimple(
            'catalogos',
            'filtros.tipos_equipo.arrays.v3',
            fn (): array => TipoEquipo::where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_tipo_equipo', 'nombre'])
                    ->map(fn ($row): array => [
                        'id_tipo_equipo' => (int) $row->id_tipo_equipo,
                        'nombre' => (string) $row->nombre,
                    ])
                    ->all(),
            24
        );
    }

    private function getMarcas(): array
    {
        return app(SystemCacheService::class)->rememberSimple(
            'catalogos',
            'filtros.marcas.arrays.v3',
            fn (): array => Marca::where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_marca', 'nombre'])
                    ->map(fn ($row): array => [
                        'id_marca' => (int) $row->id_marca,
                        'nombre' => (string) $row->nombre,
                    ])
                    ->all(),
            24
        );
    }

    private function getModelos(): array
    {
        return app(SystemCacheService::class)->rememberSimple(
            'catalogos',
            'filtros.modelos.arrays.v3',
            fn (): array => Modelo::where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_modelo', 'nombre'])
                    ->map(fn ($row): array => [
                        'id_modelo' => (int) $row->id_modelo,
                        'nombre' => (string) $row->nombre,
                    ])
                    ->all(),
            24
        );
    }

    private function getEstados(): array
    {
        return app(SystemCacheService::class)->rememberSimple(
            'catalogos',
            'filtros.estados_equipo.arrays.v3',
            fn (): array => DB::table('estados_equipo')
                    ->where('activo', true)
                    ->orderBy('orden')
                    ->get(['id_estado_equipo', 'nombre'])
                    ->map(fn ($row): array => [
                        'id_valor' => (int) $row->id_estado_equipo,
                        'nombre' => (string) $row->nombre,
                    ])
                    ->all(),
            24
        );
    }

    private function getAreas(): array
    {
        return app(SystemCacheService::class)->rememberSimple(
            'catalogos',
            'filtros.unidades.arrays.v2',
            fn (): array => DB::table('unidades_organizativas')->where('activo', true)
                    ->orderBy('nombre')
                    ->get(['id_unidad', 'nombre'])
                    ->map(fn ($row): array => [
                        'id_unidad' => (int) $row->id_unidad,
                        'nombre' => (string) $row->nombre,
                    ])
                    ->all(),
            24
        );
    }

    private const COLUMNAS_FILTRO_BASE = [
        'codigo_inventario',
        'id_tipo_equipo',
        'nombre_equipo',
        'id_estado_activo',
        'id_marca',
        'id_modelo',
        'numero_serie',
        'numero_factura',
        'id_unidad',
    ];

    private const SECCIONES_NO_ESPECIFICAS = [
        'general',
        'adicional',
    ];

    /*
     * Programa instalado (?programa=ID). Solo en los componentes que
     * tienen la propiedad (Inventario); la baja múltiple no la usa.
     */
    private function applyPrograma(Builder $query): void
    {
        if (! property_exists($this, 'programa')) {
            return;
        }

        $programa = (string) ($this->programa ?? '');

        if (! ctype_digit($programa) || ! app(\App\Services\ProgramasService::class)->disponible()) {
            return;
        }

        $query->whereExists(fn ($sub) => $sub->selectRaw('1')
            ->from('equipo_programa as ep_filtro')
            ->whereColumn('ep_filtro.id_equipo', 'e.id_equipo')
            ->where('ep_filtro.id_programa', (int) $programa));
    }
}
