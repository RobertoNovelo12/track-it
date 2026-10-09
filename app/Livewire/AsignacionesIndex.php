<?php

namespace App\Livewire;

use App\Services\SystemCacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class AsignacionesIndex extends Component
{
    /*
    |--------------------------------------------------------------------------
    | CACHE
    |--------------------------------------------------------------------------
    */

    private const CACHE_MODULE =
        'asignaciones';

    private const CATALOG_CACHE_MODULE =
        'catalogos';

    private const CACHE_TTL_HOURS =
        12;

    private const CATALOG_CACHE_TTL_HOURS =
        24;


    private function systemCache(): SystemCacheService
    {
        return app(
            SystemCacheService::class
        );
    }


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

        $tipos =
            $this->getTiposAsignacion();


        if (
            count($tipos) === 1
        ) {

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
            $this->idDepartamentoAsignar !== ''
            &&
            $this->idAreaAsignar !== ''
        ) {

            $departamentoValido =
                DB::table(
                    'departamentos'
                )

                    ->where(
                        'id_departamento',
                        (int) $this->idDepartamentoAsignar
                    )

                    ->where(
                        'id_area',
                        (int) $this->idAreaAsignar
                    )

                    ->exists();


            if (
                ! $departamentoValido
            ) {

                $this->addError(
                    'idDepartamentoAsignar',
                    'El departamento no pertenece al área seleccionada.'
                );

                return;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ID antes de limpiar formulario
        |--------------------------------------------------------------------------
        */

        $equipoIdAsignado =
            (int) $this->idEquipoAsignar;


        /*
        |--------------------------------------------------------------------------
        | Guardar
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $equipoIdAsignado
            ) {

                $equipoId =
                    $equipoIdAsignado;


                /*
                 * Bloqueamos el equipo durante la operación.
                 */

                DB::table(
                    'equipos'
                )

                    ->where(
                        'id_equipo',
                        $equipoId
                    )

                    ->lockForUpdate()

                    ->first();


                /*
                 * Evitar doble asignación.
                 */

                $yaAsignado =
                    DB::table(
                        'asignaciones'
                    )

                        ->where(
                            'id_equipo',
                            $equipoId
                        )

                        ->whereNull(
                            'fecha_fin'
                        )

                        ->exists();


                if (
                    $yaAsignado
                ) {

                    throw ValidationException::withMessages([
                        'idEquipoAsignar' =>
                            'Este equipo ya tiene una asignación activa.',
                    ]);
                }


                /*
                 * Evitar asignar un equipo dado de baja.
                 */

                $estaDeBaja =
                    DB::table(
                        'bajas'
                    )

                        ->where(
                            'id_equipo',
                            $equipoId
                        )

                        ->exists();


                if (
                    $estaDeBaja
                ) {

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

                DB::table(
                    'asignaciones'
                )
                    ->insert([

                        'id_equipo' =>
                            $equipoId,

                        'nombre_colaborador' =>
                            trim(
                                $this->nombreColaborador
                            ),

                        'numero_colaborador' =>
                            trim(
                                $this->numeroColaborador
                            ) !== ''

                                ? trim(
                                    $this->numeroColaborador
                                )

                                : null,

                        'id_area' =>
                            $this->idDepartamentoAsignar !== ''

                                ? null

                                : (
                                    $this->idAreaAsignar !== ''

                                        ? (int)
                                            $this->idAreaAsignar

                                        : null
                                ),

                        'id_departamento' =>
                            $this->idDepartamentoAsignar !== ''

                                ? (int)
                                    $this->idDepartamentoAsignar

                                : null,

                        'id_ubicacion' =>
                            $this->idUbicacionAsignar !== ''

                                ? (int)
                                    $this->idUbicacionAsignar

                                : null,

                        'id_tipo_asignacion' =>
                            (int)
                            $this->idTipoAsignacion,

                        'fecha_asignacion' =>
                            now(),

                        'fecha_fin' =>
                            null,

                        'observaciones' =>
                            trim(
                                $this->observacionesAsignacion
                            ) !== ''

                                ? trim(
                                    $this->observacionesAsignacion
                                )

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
                |
                */

                $estadoAsignado =
                    DB::table(
                        'estados_equipo'
                    )

                        ->whereRaw(
                            'LOWER(clave) = ?',
                            [
                                'asignado',
                            ]
                        )

                        ->value(
                            'id_estado_equipo'
                        );


                if (
                    $estadoAsignado !== null
                ) {

                    DB::table(
                        'equipos'
                    )

                        ->where(
                            'id_equipo',
                            $equipoId
                        )

                        ->update([
                            'id_estado_activo' =>
                                $estadoAsignado,
                        ]);
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Invalidar caches relacionados
        |--------------------------------------------------------------------------
        |
        | La asignación modifica información visible en:
        |
        | - Asignaciones
        | - Equipos
        | - Dashboard
        |
        */

        Cache::forget(
            'equipos.edit.base.'
            . $equipoIdAsignado
        );


        $cache =
            $this->systemCache();


        $cache->refreshModule(
            self::CACHE_MODULE
        );


        $cache->refreshModule(
            'equipos'
        );


        $cache->refreshModule(
            'dashboard'
        );


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
    */

    private function getTiposAsignacion(): array
    {
        $cache =
            $this->systemCache();


        return $cache->remember(
            self::CATALOG_CACHE_MODULE,

            'asignaciones.tipos',

            [
                /*
                 * El fallback depende de asignaciones.
                 */
                'asignaciones_version' =>
                    $cache->moduleVersion(
                        self::CACHE_MODULE
                    ),
            ],

            function (): array {

                $tipos =
                    DB::table(
                        'catalogo_valores as cv'
                    )

                        ->join(
                            'catalogos as c',
                            'c.id_catalogo',
                            '=',
                            'cv.id_catalogo'
                        )

                        ->where(
                            'cv.activo',
                            true
                        )

                        ->where(
                            function ($query) {

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
                                        [
                                            '%asign%',
                                        ]
                                    );
                            }
                        )

                        ->orderBy(
                            'cv.orden'
                        )

                        ->orderBy(
                            'cv.nombre'
                        )

                        ->get([
                            'cv.id_valor',
                            'cv.nombre',
                        ])

                        ->map(
                            fn ($item) => [
                                'value' =>
                                    (string)
                                    $item->id_valor,

                                'label' =>
                                    $item->nombre,
                            ]
                        )

                        ->values()

                        ->toArray();


                /*
                 * Fallback:
                 *
                 * Si el catálogo no coincide con lo esperado,
                 * usamos tipos ya utilizados.
                 */

                if (
                    empty($tipos)
                ) {

                    $idsUsados =
                        DB::table(
                            'asignaciones'
                        )

                            ->whereNotNull(
                                'id_tipo_asignacion'
                            )

                            ->distinct()

                            ->pluck(
                                'id_tipo_asignacion'
                            );


                    if (
                        $idsUsados->isNotEmpty()
                    ) {

                        $tipos =
                            DB::table(
                                'catalogo_valores'
                            )

                                ->whereIn(
                                    'id_valor',
                                    $idsUsados
                                )

                                ->where(
                                    'activo',
                                    true
                                )

                                ->orderBy(
                                    'orden'
                                )

                                ->orderBy(
                                    'nombre'
                                )

                                ->get([
                                    'id_valor',
                                    'nombre',
                                ])

                                ->map(
                                    fn ($item) => [
                                        'value' =>
                                            (string)
                                            $item->id_valor,

                                        'label' =>
                                            $item->nombre,
                                    ]
                                )

                                ->values()

                                ->toArray();
                    }
                }


                return $tipos;
            },

            self::CATALOG_CACHE_TTL_HOURS
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resumen
    |--------------------------------------------------------------------------
    |
    | Antes estos indicadores generaban varias consultas independientes.
    |
    | Ahora todos se obtienen mediante una sola ida a PostgreSQL.
    |
    */

    private function getResumen(): array
    {
        $cache =
            $this->systemCache();


        return $cache->remember(
            self::CACHE_MODULE,

            'index.resumen',

            [
                /*
                 * Evita mantener indicadores diarios
                 * después de cambiar de fecha.
                 */

                'fecha' =>
                    now()->toDateString(),


                /*
                 * Disponibilidad también depende de Equipos.
                 */

                'equipos_version' =>
                    $cache->moduleVersion(
                        'equipos'
                    ),
            ],

            function (): array {

                $inicioHoy =
                    now()
                        ->startOfDay();


                $inicioManana =
                    $inicioHoy
                        ->copy()
                        ->addDay();


                $inicioMes =
                    now()
                        ->startOfMonth();


                $inicioSiguienteMes =
                    $inicioMes
                        ->copy()
                        ->addMonth();


                $inicioUltimosSieteDias =
                    now()
                        ->subDays(7);


                $row =
                    DB::selectOne(
                        <<<'SQL'
                        SELECT

                            (
                                SELECT
                                    COUNT(
                                        DISTINCT a.id_equipo
                                    )

                                FROM asignaciones a

                                WHERE
                                    a.fecha_fin IS NULL

                            ) AS equipos_asignados,


                            (
                                SELECT
                                    COUNT(*)

                                FROM equipos e

                                WHERE
                                    NOT EXISTS (
                                        SELECT 1

                                        FROM asignaciones a

                                        WHERE
                                            a.id_equipo =
                                                e.id_equipo

                                            AND
                                            a.fecha_fin
                                                IS NULL
                                    )

                                    AND

                                    NOT EXISTS (
                                        SELECT 1

                                        FROM bajas b

                                        WHERE
                                            b.id_equipo =
                                                e.id_equipo
                                    )

                            ) AS equipos_no_asignados,


                            (
                                SELECT
                                    COUNT(*)

                                FROM movimientos m

                                WHERE
                                    m.fecha_hora >= ?

                            ) AS movimientos_recientes,


                            (
                                SELECT
                                    COUNT(*)

                                FROM asignaciones a

                                WHERE
                                    a.fecha_asignacion >= ?

                                    AND

                                    a.fecha_asignacion < ?

                                    AND

                                    NOT EXISTS (
                                        SELECT 1

                                        FROM movimientos m

                                        WHERE
                                            m.id_asignacion_nueva =
                                                a.id_asignacion

                                            AND

                                            m.id_asignacion_anterior
                                                IS NOT NULL
                                    )

                            ) AS asignaciones_hoy,


                            (
                                SELECT
                                    COUNT(*)

                                FROM movimientos m

                                WHERE
                                    m.fecha_hora >= ?

                                    AND

                                    m.fecha_hora < ?

                                    AND

                                    m.id_asignacion_anterior
                                        IS NOT NULL

                                    AND

                                    m.id_asignacion_nueva
                                        IS NOT NULL

                            ) AS reasignaciones_hoy,


                            (
                                SELECT
                                    COUNT(*)

                                FROM asignaciones a

                                WHERE
                                    a.fecha_asignacion >= ?

                                    AND

                                    a.fecha_asignacion < ?

                                    AND

                                    NOT EXISTS (
                                        SELECT 1

                                        FROM movimientos m

                                        WHERE
                                            m.id_asignacion_nueva =
                                                a.id_asignacion

                                            AND

                                            m.id_asignacion_anterior
                                                IS NOT NULL
                                    )

                            ) AS asignaciones_mes,


                            (
                                SELECT
                                    COUNT(*)

                                FROM movimientos m

                                WHERE
                                    m.fecha_hora >= ?

                                    AND

                                    m.fecha_hora < ?

                                    AND

                                    m.id_asignacion_anterior
                                        IS NOT NULL

                                    AND

                                    m.id_asignacion_nueva
                                        IS NOT NULL

                            ) AS reasignaciones_mes
                        SQL,

                        [
                            $inicioUltimosSieteDias,

                            $inicioHoy,
                            $inicioManana,

                            $inicioHoy,
                            $inicioManana,

                            $inicioMes,
                            $inicioSiguienteMes,

                            $inicioMes,
                            $inicioSiguienteMes,
                        ]
                    );


                return [
                    'equiposAsignados' =>
                        (int) (
                            $row->equipos_asignados
                            ?? 0
                        ),

                    'equiposNoAsignados' =>
                        (int) (
                            $row->equipos_no_asignados
                            ?? 0
                        ),

                    'movimientosRecientes' =>
                        (int) (
                            $row->movimientos_recientes
                            ?? 0
                        ),

                    'asignacionesHoy' =>
                        (int) (
                            $row->asignaciones_hoy
                            ?? 0
                        ),

                    'reasignacionesHoy' =>
                        (int) (
                            $row->reasignaciones_hoy
                            ?? 0
                        ),

                    'asignacionesMes' =>
                        (int) (
                            $row->asignaciones_mes
                            ?? 0
                        ),

                    'reasignacionesMes' =>
                        (int) (
                            $row->reasignaciones_mes
                            ?? 0
                        ),
                ];
            },

            self::CACHE_TTL_HOURS
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Equipos asignados por área
    |--------------------------------------------------------------------------
    */

    private function getEquiposPorArea()
    {
        $cache =
            $this->systemCache();


        /*
        * Cacheamos únicamente arrays simples.
        *
        * No guardamos Collection ni stdClass directamente
        * para que el tipo sea idéntico después de recuperar
        * el valor desde cache.
        */

        $rows =
            $cache->remember(
                self::CACHE_MODULE,

                'index.equipos_por_area.v2',

                [
                    'equipos_version' =>
                        $cache->moduleVersion(
                            'equipos'
                        ),

                    'catalogos_version' =>
                        $cache->moduleVersion(
                            self::CATALOG_CACHE_MODULE
                        ),
                ],

                function (): array {

                    $areaExpression = "
                        COALESCE(
                            area_asig.nombre,
                            area_dep_asig.nombre,
                            area_equipo.nombre,
                            area_dep_equipo.nombre,
                            'Sin área'
                        )
                    ";


                    return DB::table(
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
                            $areaExpression
                            . ' as area'
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

                        ->get()

                        ->map(
                            function ($item): array {

                                return [
                                    'area' =>
                                        (string) (
                                            $item->area
                                            ?? 'Sin área'
                                        ),

                                    'total' =>
                                        (int) (
                                            $item->total
                                            ?? 0
                                        ),
                                ];
                            }
                        )

                        ->values()

                        ->all();
                },

                self::CACHE_TTL_HOURS
            );


        /*
        * El Blade actual trabaja con:
        *
        * $item->area
        * $item->total
        *
        * por eso reconstruimos objetos después del cache.
        */

        return collect(
            $rows
        )
            ->map(
                function ($item) {

                    $item =
                        (array) $item;


                    return (object) [
                        'area' =>
                            (string) (
                                $item['area']
                                ?? 'Sin área'
                            ),

                        'total' =>
                            (int) (
                                $item['total']
                                ?? 0
                            ),
                    ];
                }
            )

            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | Actividad reciente
    |--------------------------------------------------------------------------
    */

    private function getActividad()
    {
        $cache =
            $this->systemCache();


        /*
        * Igual que en la gráfica:
        *
        * guardamos en cache solamente arrays simples
        * y reconstruimos los objetos después.
        */

        $rows =
            $cache->remember(
                self::CACHE_MODULE,

                'index.actividad.v2',

                [
                    'equipos_version' =>
                        $cache->moduleVersion(
                            'equipos'
                        ),
                ],

                function (): array {

                    /*
                    |--------------------------------------------------------------------------
                    | Últimas asignaciones
                    |--------------------------------------------------------------------------
                    */

                    $actividadAsignaciones =
                        DB::table(
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
                    | Últimos movimientos
                    |--------------------------------------------------------------------------
                    */

                    $actividadMovimientos =
                        DB::table(
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
                                        m.id_asignacion_anterior
                                            IS NOT NULL

                                        AND

                                        m.id_asignacion_nueva
                                            IS NOT NULL

                                    THEN
                                        'reasignacion'

                                    ELSE
                                        'movimiento'

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
                    | Unir, ordenar y normalizar
                    |--------------------------------------------------------------------------
                    */

                    return $actividadAsignaciones

                        ->concat(
                            $actividadMovimientos
                        )

                        ->sortByDesc(
                            function ($item) {

                                return strtotime(
                                    (string) (
                                        $item->fecha
                                        ?? ''
                                    )
                                );
                            }
                        )

                        ->take(8)

                        ->values()

                        ->map(
                            function ($item): array {

                                return [
                                    'id' =>
                                        (int) (
                                            $item->id
                                            ?? 0
                                        ),

                                    'fecha' =>
                                        (string) (
                                            $item->fecha
                                            ?? ''
                                        ),

                                    'tipo' =>
                                        (string) (
                                            $item->tipo
                                            ?? ''
                                        ),

                                    'id_equipo' =>
                                        (int) (
                                            $item->id_equipo
                                            ?? 0
                                        ),

                                    'codigo_inventario' =>
                                        $item->codigo_inventario
                                        ?? null,

                                    'nombre_equipo' =>
                                        $item->nombre_equipo
                                        ?? null,

                                    'nombre_colaborador' =>
                                        $item->nombre_colaborador
                                        ?? null,

                                    'usuario' =>
                                        $item->usuario
                                        ?? null,

                                    'motivo' =>
                                        $item->motivo
                                        ?? null,

                                    'observaciones' =>
                                        $item->observaciones
                                        ?? null,
                                ];
                            }
                        )

                        ->all();
                },

                self::CACHE_TTL_HOURS
            );


        /*
        * La vista existente usa notación de objeto:
        *
        * $item->fecha
        * $item->tipo
        * $item->id
        * etc.
        *
        * La reconstruimos siempre de la misma manera.
        */

        return collect(
            $rows
        )
            ->map(
                function ($item) {

                    $item =
                        (array) $item;


                    return (object) [
                        'id' =>
                            (int) (
                                $item['id']
                                ?? 0
                            ),

                        'fecha' =>
                            (string) (
                                $item['fecha']
                                ?? ''
                            ),

                        'tipo' =>
                            (string) (
                                $item['tipo']
                                ?? ''
                            ),

                        'id_equipo' =>
                            (int) (
                                $item['id_equipo']
                                ?? 0
                            ),

                        'codigo_inventario' =>
                            $item[
                                'codigo_inventario'
                            ]
                            ?? null,

                        'nombre_equipo' =>
                            $item[
                                'nombre_equipo'
                            ]
                            ?? null,

                        'nombre_colaborador' =>
                            $item[
                                'nombre_colaborador'
                            ]
                            ?? null,

                        'usuario' =>
                            $item[
                                'usuario'
                            ]
                            ?? null,

                        'motivo' =>
                            $item[
                                'motivo'
                            ]
                            ?? null,

                        'observaciones' =>
                            $item[
                                'observaciones'
                            ]
                            ?? null,
                    ];
                }
            )

            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | Equipos disponibles para asignar
    |--------------------------------------------------------------------------
    */

    private function getEquiposDisponibles(): array
    {
        $cache =
            $this->systemCache();


        return $cache->remember(
            self::CACHE_MODULE,

            'index.equipos_disponibles',

            [
                'equipos_version' =>
                    $cache->moduleVersion(
                        'equipos'
                    ),
            ],

            function (): array {

                return DB::table(
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
                                ->selectRaw(
                                    '1'
                                )

                                ->from(
                                    'asignaciones as a'
                                )

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
                                ->selectRaw(
                                    '1'
                                )

                                ->from(
                                    'bajas as b'
                                )

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

                            $descripcion =
                                trim(
                                    implode(
                                        ' ',
                                        array_filter([
                                            $equipo->tipo,
                                            $equipo->marca,
                                            $equipo->modelo,
                                        ])
                                    )
                                );


                            if (
                                $descripcion === ''
                            ) {

                                $descripcion =
                                    $equipo->nombre_equipo
                                    ?: 'Equipo';
                            }


                            return [
                                'value' =>
                                    (string)
                                    $equipo->id_equipo,

                                'label' =>
                                    $equipo->codigo_inventario
                                    . ' — '
                                    . $descripcion,
                            ];
                        }
                    )

                    ->values()

                    ->toArray();
            },

            self::CACHE_TTL_HOURS
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Áreas
    |--------------------------------------------------------------------------
    */

    private function getAreas(): array
    {
        return $this->systemCache()
            ->rememberSimple(
                self::CATALOG_CACHE_MODULE,

                'asignaciones.areas',

                fn (): array =>
                    DB::table(
                        'areas'
                    )

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
                                    (string)
                                    $area->id_area,

                                'label' =>
                                    $area->nombre,
                            ]
                        )

                        ->values()

                        ->toArray(),

                self::CATALOG_CACHE_TTL_HOURS
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Departamentos
    |--------------------------------------------------------------------------
    */

    private function getDepartamentos(): array
    {
        return $this->systemCache()
            ->remember(
                self::CATALOG_CACHE_MODULE,

                'asignaciones.departamentos',

                [
                    'area' =>
                        $this->idAreaAsignar,
                ],

                function (): array {

                    $query =
                        DB::table(
                            'departamentos'
                        )

                            ->where(
                                'activo',
                                true
                            );


                    if (
                        $this->idAreaAsignar !== ''
                    ) {

                        $query->where(
                            'id_area',
                            (int)
                            $this->idAreaAsignar
                        );
                    }


                    return $query

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
                                    (string)
                                    $departamento
                                        ->id_departamento,

                                'label' =>
                                    $departamento
                                        ->nombre,
                            ]
                        )

                        ->values()

                        ->toArray();
                },

                self::CATALOG_CACHE_TTL_HOURS
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Ubicaciones
    |--------------------------------------------------------------------------
    */

    private function getUbicaciones(): array
    {
        return $this->systemCache()
            ->remember(
                self::CATALOG_CACHE_MODULE,

                'asignaciones.ubicaciones',

                [
                    'area' =>
                        $this->idAreaAsignar,
                ],

                function (): array {

                    $query =
                        DB::table(
                            'ubicaciones'
                        )

                            ->where(
                                'activo',
                                true
                            );


                    if (
                        $this->idAreaAsignar !== ''
                    ) {

                        $query->where(
                            'id_area',
                            (int)
                            $this->idAreaAsignar
                        );
                    }


                    return $query

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
                                    (string)
                                    $ubicacion
                                        ->id_ubicacion,

                                'label' =>
                                    $ubicacion
                                        ->nombre,
                            ]
                        )

                        ->values()

                        ->toArray();
                },

                self::CATALOG_CACHE_TTL_HOURS
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        $resumen =
            $this->getResumen();


        return view(
            'livewire.asignaciones-index',
            [

                /*
                 * Cards principales
                 */

                'equiposAsignados' =>
                    $resumen[
                        'equiposAsignados'
                    ],

                'equiposNoAsignados' =>
                    $resumen[
                        'equiposNoAsignados'
                    ],

                'movimientosRecientes' =>
                    $resumen[
                        'movimientosRecientes'
                    ],


                /*
                 * Resumen
                 */

                'asignacionesHoy' =>
                    $resumen[
                        'asignacionesHoy'
                    ],

                'reasignacionesHoy' =>
                    $resumen[
                        'reasignacionesHoy'
                    ],

                'asignacionesMes' =>
                    $resumen[
                        'asignacionesMes'
                    ],

                'reasignacionesMes' =>
                    $resumen[
                        'reasignacionesMes'
                    ],


                /*
                 * Gráfica y actividad
                 */

                'equiposPorArea' =>
                    $this->getEquiposPorArea(),

                'actividad' =>
                    $this->getActividad(),


                /*
                 * Modal de asignación
                 */

                'equiposDisponibles' =>
                    $this->getEquiposDisponibles(),

                'areas' =>
                    $this->getAreas(),

                'departamentos' =>
                    $this->getDepartamentos(),

                'ubicaciones' =>
                    $this->getUbicaciones(),

                'tiposAsignacion' =>
                    $this->getTiposAsignacion(),
            ]
        );
    }
}