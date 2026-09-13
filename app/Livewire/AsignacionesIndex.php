<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class AsignacionesIndex extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Modal: Asignar equipo
    |--------------------------------------------------------------------------
    */

    public bool $showAsignarModal = false;


    /*
    |--------------------------------------------------------------------------
    | Campos de asignación
    |--------------------------------------------------------------------------
    */

    public string $idEquipoAsignar = '';

    public string $nombreColaborador = '';

    public string $numeroColaborador = '';

    public string $idAreaAsignar = '';

    public string $idDepartamentoAsignar = '';

    public string $idUbicacionAsignar = '';

    public string $idTipoAsignacion = '';

    public string $observacionesAsignacion = '';


    /*
    |--------------------------------------------------------------------------
    | Placeholder
    |--------------------------------------------------------------------------
    */

    public function placeholder()
    {
        return view(
            'livewire.placeholders.asignaciones-index'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Abrir modal de asignación
    |--------------------------------------------------------------------------
    */

    public function openAsignarModal(): void
    {
        $this->resetAsignacionForm();

        /*
         * Si solamente existe un tipo de asignación,
         * lo seleccionamos automáticamente.
         */

        $tipos = $this->getTiposAsignacion();

        if (count($tipos) === 1) {
            $this->idTipoAsignacion =
                (string) $tipos[0]['value'];
        }

        $this->showAsignarModal = true;
    }


    /*
    |--------------------------------------------------------------------------
    | Cerrar modal
    |--------------------------------------------------------------------------
    */

    public function closeAsignarModal(): void
    {
        $this->showAsignarModal = false;

        $this->resetAsignacionForm();

        $this->resetValidation();
    }


    /*
    |--------------------------------------------------------------------------
    | Al cambiar área
    |--------------------------------------------------------------------------
    */

    public function updatedIdAreaAsignar(): void
    {
        $this->idDepartamentoAsignar = '';
        $this->idUbicacionAsignar = '';
    }


    /*
    |--------------------------------------------------------------------------
    | Al cambiar departamento
    |--------------------------------------------------------------------------
    */

    public function updatedIdDepartamentoAsignar(): void
    {
        $this->idUbicacionAsignar = '';
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar asignación
    |--------------------------------------------------------------------------
    */

    public function guardarAsignacion(): void
    {
        $this->validate(
            [
                'idEquipoAsignar' => [
                    'required',
                    'integer',
                    'exists:equipos,id_equipo',
                ],

                'nombreColaborador' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'numeroColaborador' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'idAreaAsignar' => [
                    'nullable',
                    'integer',
                    'exists:areas,id_area',
                ],

                'idDepartamentoAsignar' => [
                    'nullable',
                    'integer',
                    'exists:departamentos,id_departamento',
                ],

                'idUbicacionAsignar' => [
                    'nullable',
                    'integer',
                    'exists:ubicaciones,id_ubicacion',
                ],

                'idTipoAsignacion' => [
                    'required',
                    'integer',
                    'exists:catalogo_valores,id_valor',
                ],

                'observacionesAsignacion' => [
                    'nullable',
                    'string',
                ],
            ],
            [
                'idEquipoAsignar.required' =>
                    'Selecciona un equipo.',

                'nombreColaborador.required' =>
                    'Escribe el nombre del colaborador.',

                'idTipoAsignacion.required' =>
                    'Selecciona el tipo de asignación.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Validar relación Área -> Departamento
        |--------------------------------------------------------------------------
        */

        if (
            $this->idDepartamentoAsignar !== '' &&
            $this->idAreaAsignar !== ''
        ) {

            $departamentoValido = DB::table('departamentos')
                ->where(
                    'id_departamento',
                    (int) $this->idDepartamentoAsignar
                )
                ->where(
                    'id_area',
                    (int) $this->idAreaAsignar
                )
                ->exists();


            if (!$departamentoValido) {

                $this->addError(
                    'idDepartamentoAsignar',
                    'El departamento no pertenece al área seleccionada.'
                );

                return;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Guardar
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () {

            $equipoId = (int) $this->idEquipoAsignar;


            /*
             * Bloqueamos el equipo durante la operación.
             */

            DB::table('equipos')
                ->where('id_equipo', $equipoId)
                ->lockForUpdate()
                ->first();


            /*
             * Evitar doble asignación.
             */

            $yaAsignado = DB::table('asignaciones')
                ->where(
                    'id_equipo',
                    $equipoId
                )
                ->whereNull('fecha_fin')
                ->exists();


            if ($yaAsignado) {

                throw ValidationException::withMessages([
                    'idEquipoAsignar' =>
                        'Este equipo ya tiene una asignación activa.',
                ]);
            }


            /*
             * Evitar asignar un equipo dado de baja.
             */

            $estaDeBaja = DB::table('bajas')
                ->where(
                    'id_equipo',
                    $equipoId
                )
                ->exists();


            if ($estaDeBaja) {

                throw ValidationException::withMessages([
                    'idEquipoAsignar' =>
                        'Este equipo se encuentra dado de baja.',
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Crear asignación
            |--------------------------------------------------------------------------
            |
            | Si se selecciona un departamento, guardamos el departamento
            | y dejamos id_area en NULL.
            |
            | Si no hay departamento, guardamos directamente el área.
            |
            */

            DB::table('asignaciones')->insert([

                'id_equipo' =>
                    $equipoId,

                'nombre_colaborador' =>
                    trim($this->nombreColaborador),

                'numero_colaborador' =>
                    trim($this->numeroColaborador) !== ''
                        ? trim($this->numeroColaborador)
                        : null,

                'id_area' =>
                    $this->idDepartamentoAsignar !== ''
                        ? null
                        : (
                            $this->idAreaAsignar !== ''
                                ? (int) $this->idAreaAsignar
                                : null
                        ),

                'id_departamento' =>
                    $this->idDepartamentoAsignar !== ''
                        ? (int) $this->idDepartamentoAsignar
                        : null,

                'id_ubicacion' =>
                    $this->idUbicacionAsignar !== ''
                        ? (int) $this->idUbicacionAsignar
                        : null,

                'id_tipo_asignacion' =>
                    (int) $this->idTipoAsignacion,

                'fecha_asignacion' =>
                    now(),

                'fecha_fin' =>
                    null,

                'observaciones' =>
                    trim($this->observacionesAsignacion) !== ''
                        ? trim($this->observacionesAsignacion)
                        : null,

                'asignado_por' =>
                    auth()->id(),

            ]);


            /*
            |--------------------------------------------------------------------------
            | Cambiar estado del equipo a ASIGNADO
            |--------------------------------------------------------------------------
            |
            | Solo lo hacemos si existe un estado cuya clave sea "ASIGNADO".
            | Así evitamos depender de un ID fijo.
            |
            */

            $estadoAsignado = DB::table('estados_equipo')
                ->whereRaw(
                    'LOWER(clave) = ?',
                    ['asignado']
                )
                ->value('id_estado_equipo');


            if ($estadoAsignado !== null) {

                DB::table('equipos')
                    ->where(
                        'id_equipo',
                        $equipoId
                    )
                    ->update([
                        'id_estado_activo' =>
                            $estadoAsignado,
                    ]);
            }
        });


        /*
        |--------------------------------------------------------------------------
        | Limpiar y cerrar
        |--------------------------------------------------------------------------
        */

        $this->showAsignarModal = false;

        $this->resetAsignacionForm();

        $this->resetValidation();


        /*
        |--------------------------------------------------------------------------
        | Mensaje para Alpine
        |--------------------------------------------------------------------------
        */

        $this->dispatch(
            'asignacion-guardada',
            message:
                'El equipo fue asignado correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Limpiar formulario
    |--------------------------------------------------------------------------
    */

    private function resetAsignacionForm(): void
    {
        $this->reset([
            'idEquipoAsignar',
            'nombreColaborador',
            'numeroColaborador',
            'idAreaAsignar',
            'idDepartamentoAsignar',
            'idUbicacionAsignar',
            'idTipoAsignacion',
            'observacionesAsignacion',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Tipos de asignación
    |--------------------------------------------------------------------------
    |
    | Buscamos el catálogo por clave o nombre en vez de depender
    | de IDs fijos.
    |
    */

    private function getTiposAsignacion(): array
    {
        $tipos = DB::table('catalogo_valores as cv')

            ->join(
                'catalogos as c',
                'c.id_catalogo',
                '=',
                'cv.id_catalogo'
            )

            ->where('cv.activo', true)

            ->where(function ($query) {

                $query
                    ->whereRaw(
                        'LOWER(c.clave) IN (?, ?)',
                        [
                            'tipo_asignacion',
                            'tipos_asignacion',
                        ]
                    )

                    ->orWhereRaw(
                        'LOWER(c.nombre) LIKE ?',
                        ['%asign%']
                    );
            })

            ->orderBy('cv.orden')
            ->orderBy('cv.nombre')

            ->get([
                'cv.id_valor',
                'cv.nombre',
            ])

            ->map(
                fn ($item) => [
                    'value' =>
                        (string) $item->id_valor,

                    'label' =>
                        $item->nombre,
                ]
            )

            ->values()

            ->toArray();


        /*
         * Fallback:
         *
         * Si el nombre del catálogo no coincide con lo esperado,
         * tomamos los tipos que ya hayan sido utilizados anteriormente
         * en asignaciones.
         */

        if (empty($tipos)) {

            $idsUsados = DB::table('asignaciones')
                ->whereNotNull('id_tipo_asignacion')
                ->distinct()
                ->pluck('id_tipo_asignacion');


            if ($idsUsados->isNotEmpty()) {

                $tipos = DB::table('catalogo_valores')
                    ->whereIn(
                        'id_valor',
                        $idsUsados
                    )
                    ->where('activo', true)
                    ->orderBy('orden')
                    ->orderBy('nombre')
                    ->get([
                        'id_valor',
                        'nombre',
                    ])
                    ->map(
                        fn ($item) => [
                            'value' =>
                                (string) $item->id_valor,

                            'label' =>
                                $item->nombre,
                        ]
                    )
                    ->values()
                    ->toArray();
            }
        }


        return $tipos;
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        /*
        |--------------------------------------------------------------------------
        | Equipos asignados
        |--------------------------------------------------------------------------
        |
        | Una asignación se considera vigente mientras fecha_fin sea NULL.
        |
        */

        $equiposAsignados = DB::table(
            'asignaciones'
        )
            ->whereNull('fecha_fin')
            ->distinct()
            ->count('id_equipo');


        /*
        |--------------------------------------------------------------------------
        | Equipos no asignados
        |--------------------------------------------------------------------------
        |
        | Equipo sin asignación vigente y que tampoco esté dado de baja.
        |
        */

        $equiposNoAsignados = DB::table('equipos as e')

            ->whereNotExists(
                function ($query) {

                    $query
                        ->selectRaw('1')

                        ->from('asignaciones as a')

                        ->whereColumn(
                            'a.id_equipo',
                            'e.id_equipo'
                        )

                        ->whereNull(
                            'a.fecha_fin'
                        );
                }
            )

            ->whereNotExists(
                function ($query) {

                    $query
                        ->selectRaw('1')

                        ->from('bajas as b')

                        ->whereColumn(
                            'b.id_equipo',
                            'e.id_equipo'
                        );
                }
            )

            ->count();


        /*
        |--------------------------------------------------------------------------
        | Movimientos últimos 7 días
        |--------------------------------------------------------------------------
        */

        $movimientosRecientes = DB::table(
            'movimientos'
        )
            ->where(
                'fecha_hora',
                '>=',
                now()->subDays(7)
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Asignaciones de hoy
        |--------------------------------------------------------------------------
        |
        | Excluimos las asignaciones que hayan sido generadas como parte
        | de una reasignación.
        |
        */

        $asignacionesHoy = DB::table(
            'asignaciones as a'
        )

            ->whereDate(
                'a.fecha_asignacion',
                today()
            )

            ->whereNotExists(
                function ($query) {

                    $query
                        ->selectRaw('1')

                        ->from('movimientos as m')

                        ->whereColumn(
                            'm.id_asignacion_nueva',
                            'a.id_asignacion'
                        )

                        ->whereNotNull(
                            'm.id_asignacion_anterior'
                        );
                }
            )

            ->count();


        /*
        |--------------------------------------------------------------------------
        | Reasignaciones de hoy
        |--------------------------------------------------------------------------
        */

        $reasignacionesHoy = DB::table(
            'movimientos'
        )
            ->whereDate(
                'fecha_hora',
                today()
            )
            ->whereNotNull(
                'id_asignacion_anterior'
            )
            ->whereNotNull(
                'id_asignacion_nueva'
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Asignaciones del mes
        |--------------------------------------------------------------------------
        */

        $asignacionesMes = DB::table(
            'asignaciones as a'
        )

            ->whereYear(
                'a.fecha_asignacion',
                now()->year
            )

            ->whereMonth(
                'a.fecha_asignacion',
                now()->month
            )

            ->whereNotExists(
                function ($query) {

                    $query
                        ->selectRaw('1')

                        ->from('movimientos as m')

                        ->whereColumn(
                            'm.id_asignacion_nueva',
                            'a.id_asignacion'
                        )

                        ->whereNotNull(
                            'm.id_asignacion_anterior'
                        );
                }
            )

            ->count();


        /*
        |--------------------------------------------------------------------------
        | Reasignaciones del mes
        |--------------------------------------------------------------------------
        */

        $reasignacionesMes = DB::table(
            'movimientos'
        )
            ->whereYear(
                'fecha_hora',
                now()->year
            )
            ->whereMonth(
                'fecha_hora',
                now()->month
            )
            ->whereNotNull(
                'id_asignacion_anterior'
            )
            ->whereNotNull(
                'id_asignacion_nueva'
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Equipos asignados por área
        |--------------------------------------------------------------------------
        |
        | Prioridad:
        |
        | 1. Área guardada en la asignación
        | 2. Área del departamento de la asignación
        | 3. Área propia del equipo
        | 4. Área del departamento del equipo
        |
        */

        $areaExpression = "
            COALESCE(
                area_asig.nombre,
                area_dep_asig.nombre,
                area_equipo.nombre,
                area_dep_equipo.nombre,
                'Sin área'
            )
        ";


        $equiposPorArea = DB::table(
            'asignaciones as a'
        )

            ->join(
                'equipos as e',
                'e.id_equipo',
                '=',
                'a.id_equipo'
            )

            ->leftJoin(
                'areas as area_asig',
                'area_asig.id_area',
                '=',
                'a.id_area'
            )

            ->leftJoin(
                'departamentos as dep_asig',
                'dep_asig.id_departamento',
                '=',
                'a.id_departamento'
            )

            ->leftJoin(
                'areas as area_dep_asig',
                'area_dep_asig.id_area',
                '=',
                'dep_asig.id_area'
            )

            ->leftJoin(
                'areas as area_equipo',
                'area_equipo.id_area',
                '=',
                'e.id_area'
            )

            ->leftJoin(
                'departamentos as dep_equipo',
                'dep_equipo.id_departamento',
                '=',
                'e.id_departamento'
            )

            ->leftJoin(
                'areas as area_dep_equipo',
                'area_dep_equipo.id_area',
                '=',
                'dep_equipo.id_area'
            )

            ->whereNull(
                'a.fecha_fin'
            )

            ->selectRaw(
                $areaExpression . ' as area'
            )

            ->selectRaw(
                'COUNT(DISTINCT a.id_equipo) as total'
            )

            ->groupByRaw(
                $areaExpression
            )

            ->orderByDesc(
                'total'
            )

            ->get();


        /*
        |--------------------------------------------------------------------------
        | Actividad: últimas asignaciones
        |--------------------------------------------------------------------------
        */

        $actividadAsignaciones = DB::table(
            'asignaciones as a'
        )

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

            ->select([
                'a.id_asignacion as id',
                'a.fecha_asignacion as fecha',
                'a.nombre_colaborador',
                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
            ])

            ->selectRaw(
                "'asignacion' as tipo"
            )

            ->selectRaw("
                TRIM(
                    CONCAT_WS(
                        ' ',
                        u.nombres,
                        u.apellido_paterno
                    )
                ) as usuario
            ")

            ->orderByDesc(
                'a.fecha_asignacion'
            )

            ->limit(10)

            ->get();


        /*
        |--------------------------------------------------------------------------
        | Actividad: últimos movimientos
        |--------------------------------------------------------------------------
        */

        $actividadMovimientos = DB::table(
            'movimientos as m'
        )

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
                'asignaciones as nueva',
                'nueva.id_asignacion',
                '=',
                'm.id_asignacion_nueva'
            )

            ->select([
                'm.id_movimiento as id',
                'm.fecha_hora as fecha',
                'm.motivo',
                'm.observaciones',
                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'nueva.nombre_colaborador',
            ])

            ->selectRaw("
                CASE

                    WHEN
                        m.id_asignacion_anterior IS NOT NULL
                        AND
                        m.id_asignacion_nueva IS NOT NULL

                    THEN 'reasignacion'

                    ELSE 'movimiento'

                END as tipo
            ")

            ->selectRaw("
                TRIM(
                    CONCAT_WS(
                        ' ',
                        u.nombres,
                        u.apellido_paterno
                    )
                ) as usuario
            ")

            ->orderByDesc(
                'm.fecha_hora'
            )

            ->limit(10)

            ->get();


        /*
        |--------------------------------------------------------------------------
        | Unir actividad y ordenar
        |--------------------------------------------------------------------------
        */

        $actividad = $actividadAsignaciones

            ->concat(
                $actividadMovimientos
            )

            ->sortByDesc(
                function ($item) {

                    return strtotime(
                        (string) $item->fecha
                    );

                }
            )

            ->take(8)

            ->values();


        /*
        |--------------------------------------------------------------------------
        | Equipos disponibles para asignar
        |--------------------------------------------------------------------------
        */

        $equiposDisponibles = DB::table(
            'equipos as e'
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

            ->whereNotExists(
                function ($query) {

                    $query
                        ->selectRaw('1')

                        ->from('asignaciones as a')

                        ->whereColumn(
                            'a.id_equipo',
                            'e.id_equipo'
                        )

                        ->whereNull(
                            'a.fecha_fin'
                        );
                }
            )

            ->whereNotExists(
                function ($query) {

                    $query
                        ->selectRaw('1')

                        ->from('bajas as b')

                        ->whereColumn(
                            'b.id_equipo',
                            'e.id_equipo'
                        );
                }
            )

            ->orderBy(
                'e.codigo_inventario'
            )

            ->get([
                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',

                'te.nombre as tipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
            ])

            ->map(
                function ($equipo) {

                    $descripcion = trim(
                        implode(
                            ' ',
                            array_filter([
                                $equipo->tipo,
                                $equipo->marca,
                                $equipo->modelo,
                            ])
                        )
                    );


                    if ($descripcion === '') {

                        $descripcion =
                            $equipo->nombre_equipo
                            ?: 'Equipo';
                    }


                    return [

                        'value' =>
                            (string) $equipo->id_equipo,

                        'label' =>
                            $equipo->codigo_inventario
                            . ' — '
                            . $descripcion,

                    ];

                }
            )

            ->values()

            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Áreas
        |--------------------------------------------------------------------------
        */

        $areas = DB::table('areas')

            ->where(
                'activo',
                true
            )

            ->orderBy(
                'nombre'
            )

            ->get([
                'id_area',
                'nombre',
            ])

            ->map(
                fn ($area) => [

                    'value' =>
                        (string) $area->id_area,

                    'label' =>
                        $area->nombre,

                ]
            )

            ->values()

            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Departamentos
        |--------------------------------------------------------------------------
        */

        $departamentosQuery = DB::table(
            'departamentos'
        )
            ->where(
                'activo',
                true
            );


        if ($this->idAreaAsignar !== '') {

            $departamentosQuery->where(
                'id_area',
                (int) $this->idAreaAsignar
            );
        }


        $departamentos = $departamentosQuery

            ->orderBy(
                'nombre'
            )

            ->get([
                'id_departamento',
                'nombre',
            ])

            ->map(
                fn ($departamento) => [

                    'value' =>
                        (string) $departamento->id_departamento,

                    'label' =>
                        $departamento->nombre,

                ]
            )

            ->values()

            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Ubicaciones
        |--------------------------------------------------------------------------
        */

        $ubicacionesQuery = DB::table(
            'ubicaciones'
        )
            ->where(
                'activo',
                true
            );


        if ($this->idAreaAsignar !== '') {

            $ubicacionesQuery->where(
                'id_area',
                (int) $this->idAreaAsignar
            );
        }


        $ubicaciones = $ubicacionesQuery

            ->orderBy(
                'nombre'
            )

            ->get([
                'id_ubicacion',
                'nombre',
            ])

            ->map(
                fn ($ubicacion) => [

                    'value' =>
                        (string) $ubicacion->id_ubicacion,

                    'label' =>
                        $ubicacion->nombre,

                ]
            )

            ->values()

            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Vista
        |--------------------------------------------------------------------------
        */

        return view(
            'livewire.asignaciones-index',
            [

                /*
                 * Cards principales
                 */

                'equiposAsignados' =>
                    $equiposAsignados,

                'equiposNoAsignados' =>
                    $equiposNoAsignados,

                'movimientosRecientes' =>
                    $movimientosRecientes,


                /*
                 * Resumen
                 */

                'asignacionesHoy' =>
                    $asignacionesHoy,

                'reasignacionesHoy' =>
                    $reasignacionesHoy,

                'asignacionesMes' =>
                    $asignacionesMes,

                'reasignacionesMes' =>
                    $reasignacionesMes,


                /*
                 * Gráfica y actividad
                 */

                'equiposPorArea' =>
                    $equiposPorArea,

                'actividad' =>
                    $actividad,


                /*
                 * Modal de asignación
                 */

                'equiposDisponibles' =>
                    $equiposDisponibles,

                'areas' =>
                    $areas,

                'departamentos' =>
                    $departamentos,

                'ubicaciones' =>
                    $ubicaciones,

                'tiposAsignacion' =>
                    $this->getTiposAsignacion(),

            ]
        );
    }
}