<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AsignacionesHistory extends Component
{
    use WithPagination;

    #[Url(as: 'buscar')]
    public string $search = '';

    #[Url(as: 'tipo')]
    public string $tipo = '';

    #[Url(as: 'desde')]
    public string $fechaDesde = '';

    #[Url(as: 'hasta')]
    public string $fechaHasta = '';

    public int $perPage = 10;


    /*
    |--------------------------------------------------------------------------
    | Paginación
    |--------------------------------------------------------------------------
    */

    public function updatingSearch(): void
    {
        $this->resetPage('historyPage');
    }

    public function updatingTipo(): void
    {
        $this->resetPage('historyPage');
    }

    public function updatingFechaDesde(): void
    {
        $this->resetPage('historyPage');
    }

    public function updatingFechaHasta(): void
    {
        $this->resetPage('historyPage');
    }

    public function updatingPerPage(): void
    {
        $this->resetPage('historyPage');
    }

    public function buscar(): void
    {
        $this->resetPage('historyPage');
    }


    /*
    |--------------------------------------------------------------------------
    | Limpiar filtros
    |--------------------------------------------------------------------------
    */

    public function clearFilters(): void
    {
        $this->search = '';
        $this->tipo = '';
        $this->fechaDesde = '';
        $this->fechaHasta = '';

        $this->resetPage('historyPage');
    }


    /*
    |--------------------------------------------------------------------------
    | Asignaciones normales
    |--------------------------------------------------------------------------
    |
    | Excluimos una asignación cuando fue creada como consecuencia de una
    | reasignación, porque ese evento se mostrará mediante movimientos.
    |
    */

    private function assignmentsQuery(): Builder
    {
        return DB::table('asignaciones as a')

            ->join(
                'equipos as e',
                'e.id_equipo',
                '=',
                'a.id_equipo'
            )

            ->leftJoin(
                'usuarios as u',
                'u.id_usuario',
                '=',
                'a.asignado_por'
            )

            ->leftJoin(
                'departamentos as d',
                'd.id_departamento',
                '=',
                'a.id_departamento'
            )

            ->leftJoin(
                'areas as area_directa',
                'area_directa.id_area',
                '=',
                'a.id_area'
            )

            ->leftJoin(
                'areas as area_departamento',
                'area_departamento.id_area',
                '=',
                'd.id_area'
            )

            ->leftJoin(
                'ubicaciones as ub',
                'ub.id_ubicacion',
                '=',
                'a.id_ubicacion'
            )

            ->leftJoin(
                'sedes as sede_directa',
                'sede_directa.id_sede',
                '=',
                'area_directa.id_sede'
            )

            ->leftJoin(
                'sedes as sede_departamento',
                'sede_departamento.id_sede',
                '=',
                'area_departamento.id_sede'
            )

            ->leftJoin(
                'sedes as sede_ubicacion',
                'sede_ubicacion.id_sede',
                '=',
                'ub.id_sede'
            )

            ->leftJoin(
                'catalogo_valores as tipo_asignacion',
                'tipo_asignacion.id_valor',
                '=',
                'a.id_tipo_asignacion'
            )

            ->whereNotExists(
                function ($query) {
                    $query
                        ->selectRaw('1')
                        ->from('movimientos as movimiento_reasignacion')
                        ->whereColumn(
                            'movimiento_reasignacion.id_asignacion_nueva',
                            'a.id_asignacion'
                        )
                        ->whereNotNull(
                            'movimiento_reasignacion.id_asignacion_anterior'
                        );
                }
            )

            /*
             * IMPORTANTE:
             *
             * Estas columnas deben tener exactamente el mismo orden
             * que movementsQuery().
             */

            ->selectRaw(
                'a.id_asignacion as id'
            )

            ->selectRaw(
                'a.id_asignacion as id_asignacion'
            )

            ->selectRaw(
                'NULL as id_movimiento'
            )

            ->selectRaw(
                'NULL as id_asignacion_anterior'
            )

            ->selectRaw(
                'a.id_asignacion as id_asignacion_nueva'
            )

            ->selectRaw(
                'a.id_equipo as id_equipo'
            )

            ->selectRaw(
                'a.fecha_asignacion as fecha'
            )

            ->selectRaw(
                'a.fecha_fin as fecha_fin'
            )

            ->selectRaw(
                "'asignacion' as tipo_registro"
            )

            ->selectRaw(
                "'Asignación' as movimiento"
            )

            ->selectRaw(
                'e.codigo_inventario as codigo_inventario'
            )

            ->selectRaw(
                'e.nombre_equipo as nombre_equipo'
            )

            ->selectRaw(
                'e.host as host'
            )

            ->selectRaw(
                'a.nombre_colaborador as nombre_colaborador'
            )

            ->selectRaw(
                'a.numero_colaborador as numero_colaborador'
            )

            ->selectRaw(
                'NULL as colaborador_anterior'
            )

            ->selectRaw(
                'tipo_asignacion.nombre as tipo_asignacion'
            )

            ->selectRaw(
                'NULL as motivo'
            )

            ->selectRaw(
                'a.observaciones as observaciones'
            )

            ->selectRaw("
                COALESCE(
                    area_departamento.nombre,
                    area_directa.nombre,
                    ''
                ) as area
            ")

            ->selectRaw(
                'd.nombre as departamento'
            )

            ->selectRaw(
                'ub.nombre as ubicacion'
            )

            ->selectRaw("
                COALESCE(
                    sede_departamento.nombre,
                    sede_directa.nombre,
                    sede_ubicacion.nombre,
                    ''
                ) as sede
            ")

            ->selectRaw("
                TRIM(
                    CONCAT_WS(
                        ' ',
                        u.nombres,
                        u.apellido_paterno,
                        u.apellido_materno
                    )
                ) as usuario
            ");
    }


    /*
    |--------------------------------------------------------------------------
    | Movimientos
    |--------------------------------------------------------------------------
    */

    private function movementsQuery(): Builder
    {
        return DB::table('movimientos as m')

            ->join(
                'equipos as e',
                'e.id_equipo',
                '=',
                'm.id_equipo'
            )

            ->leftJoin(
                'usuarios as u',
                'u.id_usuario',
                '=',
                'm.realizado_por'
            )

            ->leftJoin(
                'catalogo_valores as tipo_movimiento',
                'tipo_movimiento.id_valor',
                '=',
                'm.id_tipo_movimiento'
            )

            /*
             * Asignación anterior.
             */
            ->leftJoin(
                'asignaciones as anterior',
                'anterior.id_asignacion',
                '=',
                'm.id_asignacion_anterior'
            )

            /*
             * Nueva asignación.
             */
            ->leftJoin(
                'asignaciones as nueva',
                'nueva.id_asignacion',
                '=',
                'm.id_asignacion_nueva'
            )

            /*
             * Para movimientos que no tengan asignación nueva,
             * usamos la anterior como referencia organizacional.
             */
            ->leftJoin(
                'departamentos as d',
                function ($join) {
                    $join->on(
                        'd.id_departamento',
                        '=',
                        DB::raw(
                            'COALESCE(
                                nueva.id_departamento,
                                anterior.id_departamento
                            )'
                        )
                    );
                }
            )

            ->leftJoin(
                'areas as area_directa',
                function ($join) {
                    $join->on(
                        'area_directa.id_area',
                        '=',
                        DB::raw(
                            'COALESCE(
                                nueva.id_area,
                                anterior.id_area
                            )'
                        )
                    );
                }
            )

            ->leftJoin(
                'areas as area_departamento',
                'area_departamento.id_area',
                '=',
                'd.id_area'
            )

            ->leftJoin(
                'ubicaciones as ub',
                function ($join) {
                    $join->on(
                        'ub.id_ubicacion',
                        '=',
                        DB::raw(
                            'COALESCE(
                                nueva.id_ubicacion,
                                anterior.id_ubicacion
                            )'
                        )
                    );
                }
            )

            ->leftJoin(
                'sedes as sede_directa',
                'sede_directa.id_sede',
                '=',
                'area_directa.id_sede'
            )

            ->leftJoin(
                'sedes as sede_departamento',
                'sede_departamento.id_sede',
                '=',
                'area_departamento.id_sede'
            )

            ->leftJoin(
                'sedes as sede_ubicacion',
                'sede_ubicacion.id_sede',
                '=',
                'ub.id_sede'
            )

            ->leftJoin(
                'catalogo_valores as tipo_asignacion',
                function ($join) {
                    $join->on(
                        'tipo_asignacion.id_valor',
                        '=',
                        DB::raw(
                            'COALESCE(
                                nueva.id_tipo_asignacion,
                                anterior.id_tipo_asignacion
                            )'
                        )
                    );
                }
            )

            /*
             * MISMAS 24 COLUMNAS,
             * MISMO ORDEN que assignmentsQuery().
             */

            ->selectRaw(
                'm.id_movimiento as id'
            )

            ->selectRaw(
                'COALESCE(
                    nueva.id_asignacion,
                    anterior.id_asignacion
                ) as id_asignacion'
            )

            ->selectRaw(
                'm.id_movimiento as id_movimiento'
            )

            ->selectRaw(
                'm.id_asignacion_anterior as id_asignacion_anterior'
            )

            ->selectRaw(
                'm.id_asignacion_nueva as id_asignacion_nueva'
            )

            ->selectRaw(
                'm.id_equipo as id_equipo'
            )

            ->selectRaw(
                'm.fecha_hora as fecha'
            )

            ->selectRaw(
                'NULL as fecha_fin'
            )

            ->selectRaw("
                CASE
                    WHEN
                        m.id_asignacion_anterior IS NOT NULL
                        AND
                        m.id_asignacion_nueva IS NOT NULL
                    THEN 'reasignacion'

                    ELSE 'movimiento'
                END as tipo_registro
            ")

            ->selectRaw("
                CASE
                    WHEN
                        m.id_asignacion_anterior IS NOT NULL
                        AND
                        m.id_asignacion_nueva IS NOT NULL
                    THEN 'Reasignación'

                    ELSE COALESCE(
                        tipo_movimiento.nombre,
                        'Movimiento'
                    )
                END as movimiento
            ")

            ->selectRaw(
                'e.codigo_inventario as codigo_inventario'
            )

            ->selectRaw(
                'e.nombre_equipo as nombre_equipo'
            )

            ->selectRaw(
                'e.host as host'
            )

            ->selectRaw("
                COALESCE(
                    nueva.nombre_colaborador,
                    anterior.nombre_colaborador
                ) as nombre_colaborador
            ")

            ->selectRaw("
                COALESCE(
                    nueva.numero_colaborador,
                    anterior.numero_colaborador
                ) as numero_colaborador
            ")

            ->selectRaw(
                'anterior.nombre_colaborador as colaborador_anterior'
            )

            ->selectRaw(
                'tipo_asignacion.nombre as tipo_asignacion'
            )

            ->selectRaw(
                'm.motivo as motivo'
            )

            ->selectRaw(
                'm.observaciones as observaciones'
            )

            ->selectRaw("
                COALESCE(
                    area_departamento.nombre,
                    area_directa.nombre,
                    ''
                ) as area
            ")

            ->selectRaw(
                'd.nombre as departamento'
            )

            ->selectRaw(
                'ub.nombre as ubicacion'
            )

            ->selectRaw("
                COALESCE(
                    sede_departamento.nombre,
                    sede_directa.nombre,
                    sede_ubicacion.nombre,
                    ''
                ) as sede
            ")

            ->selectRaw("
                TRIM(
                    CONCAT_WS(
                        ' ',
                        u.nombres,
                        u.apellido_paterno,
                        u.apellido_materno
                    )
                ) as usuario
            ");
    }


    /*
    |--------------------------------------------------------------------------
    | Historial unificado
    |--------------------------------------------------------------------------
    */

    private function historyQuery(): Builder
    {
        $union =
            $this
                ->assignmentsQuery()

                ->unionAll(
                    $this->movementsQuery()
                );


        $query =
            DB::query()
                ->fromSub(
                    $union,
                    'history'
                );


        /*
        |--------------------------------------------------------------------------
        | Búsqueda
        |--------------------------------------------------------------------------
        */

        $search =
            trim(
                $this->search
            );

        if ($search !== '') {
            $like =
                '%'
                . mb_strtolower($search)
                . '%';

            $query->where(
                function ($query) use ($like) {
                    $query
                        ->whereRaw(
                            "LOWER(COALESCE(history.codigo_inventario, '')) LIKE ?",
                            [$like]
                        )

                        ->orWhereRaw(
                            "LOWER(COALESCE(history.nombre_equipo, '')) LIKE ?",
                            [$like]
                        )

                        ->orWhereRaw(
                            "LOWER(COALESCE(history.host, '')) LIKE ?",
                            [$like]
                        )

                        ->orWhereRaw(
                            "LOWER(COALESCE(history.nombre_colaborador, '')) LIKE ?",
                            [$like]
                        )

                        ->orWhereRaw(
                            "LOWER(COALESCE(history.numero_colaborador, '')) LIKE ?",
                            [$like]
                        )

                        ->orWhereRaw(
                            "LOWER(COALESCE(history.colaborador_anterior, '')) LIKE ?",
                            [$like]
                        )

                        ->orWhereRaw(
                            "LOWER(COALESCE(history.movimiento, '')) LIKE ?",
                            [$like]
                        )

                        ->orWhereRaw(
                            "LOWER(COALESCE(history.tipo_asignacion, '')) LIKE ?",
                            [$like]
                        )

                        ->orWhereRaw(
                            "LOWER(COALESCE(history.motivo, '')) LIKE ?",
                            [$like]
                        )

                        ->orWhereRaw(
                            "LOWER(COALESCE(history.area, '')) LIKE ?",
                            [$like]
                        )

                        ->orWhereRaw(
                            "LOWER(COALESCE(history.departamento, '')) LIKE ?",
                            [$like]
                        )

                        ->orWhereRaw(
                            "LOWER(COALESCE(history.ubicacion, '')) LIKE ?",
                            [$like]
                        )

                        ->orWhereRaw(
                            "LOWER(COALESCE(history.sede, '')) LIKE ?",
                            [$like]
                        )

                        ->orWhereRaw(
                            "LOWER(COALESCE(history.usuario, '')) LIKE ?",
                            [$like]
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Tipo
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $this->tipo,
                [
                    'asignacion',
                    'reasignacion',
                    'movimiento',
                ],
                true
            )
        ) {
            $query->where(
                'history.tipo_registro',
                $this->tipo
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Fecha desde
        |--------------------------------------------------------------------------
        */

        if ($this->fechaDesde !== '') {
            $query->whereDate(
                'history.fecha',
                '>=',
                $this->fechaDesde
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Fecha hasta
        |--------------------------------------------------------------------------
        */

        if ($this->fechaHasta !== '') {
            $query->whereDate(
                'history.fecha',
                '<=',
                $this->fechaHasta
            );
        }


        return $query
            ->orderByDesc(
                'history.fecha'
            )
            ->orderByDesc(
                'history.id'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Opciones de tipo
    |--------------------------------------------------------------------------
    */

    private function getTypeOptions(): array
    {
        return [
            [
                'value' => '',
                'label' => 'Todos los movimientos',
            ],

            [
                'value' => 'asignacion',
                'label' => 'Asignaciones',
            ],

            [
                'value' => 'reasignacion',
                'label' => 'Reasignaciones',
            ],

            [
                'value' => 'movimiento',
                'label' => 'Otros movimientos',
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Placeholder
    |--------------------------------------------------------------------------
    */

    public function placeholder(): View
    {
        return view(
            'livewire.placeholders.asignaciones-history'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(): View
    {
        $historial =
            $this
                ->historyQuery()
                ->paginate(
                    $this->perPage,
                    ['*'],
                    'historyPage'
                );


        return view(
            'livewire.asignaciones-history',
            [
                'historial' =>
                    $historial,

                'tiposFiltro' =>
                    $this->getTypeOptions(),
            ]
        );
    }
}