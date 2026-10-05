<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Json;
use Livewire\Component;

class AsignacionCreate extends Component
{
    public string $tipoMovimiento = 'asignacion';

    public string $idEquipoAsignar = '';
    public string $hostEquipo = '';

    public string $nombreColaborador = '';
    public string $numeroColaborador = '';

    public string $idSedeAsignar = '';
    public string $idAreaAsignar = '';
    public string $idDepartamentoAsignar = '';
    public string $idUbicacionAsignar = '';

    public string $fechaAsignacion = '';
    public string $idTipoAsignacion = '';
    public string $observacionesAsignacion = '';

    /*
    |--------------------------------------------------------------------------
    | Cache únicamente durante el request actual
    |--------------------------------------------------------------------------
    |
    | Evita consultar dos veces tipos de asignación durante mount/render
    | o durante save/render.
    |
    */

    private ?array $tiposAsignacionRequestCache = null;

    public function mount(): void
    {
        $this->fechaAsignacion = now()->toDateString();

        $tipos = $this->getTiposAsignacion();

        if (count($tipos) === 1) {
            $this->idTipoAsignacion =
                (string) $tipos[0]['value'];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Compatibilidad temporal
    |--------------------------------------------------------------------------
    |
    | Estos hooks quedan mientras cambiamos el Blade.
    | Cuando los selects usen x-model local ya no se ejecutarán al hacer clic.
    |
    */

    public function updatedIdEquipoAsignar(): void
    {
        $this->hostEquipo = '';

        if ($this->idEquipoAsignar === '') {
            return;
        }

        $host = DB::table('equipos')
            ->where(
                'id_equipo',
                (int) $this->idEquipoAsignar
            )
            ->value('host');

        $this->hostEquipo =
            (string) ($host ?? '');
    }

    public function updatedIdSedeAsignar(): void
    {
        $this->idAreaAsignar = '';
        $this->idDepartamentoAsignar = '';
        $this->idUbicacionAsignar = '';
    }

    public function updatedIdAreaAsignar(): void
    {
        $this->idDepartamentoAsignar = '';
        $this->idUbicacionAsignar = '';
    }

    public function updatedIdDepartamentoAsignar(): void
    {
        $this->idUbicacionAsignar = '';
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar
    |--------------------------------------------------------------------------
    */

    #[Json]
    public function save(array $payload): array
    {
        $payload =
            $this->normalizePayload($payload);

        $validated =
            Validator::make(
                $payload,
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

                    'idSedeAsignar' => [
                        'required',
                        'integer',
                        'exists:sedes,id_sede',
                    ],

                    'idAreaAsignar' => [
                        'required',
                        'integer',
                        'exists:areas,id_area',
                    ],

                    'idDepartamentoAsignar' => [
                        'required',
                        'integer',
                        'exists:departamentos,id_departamento',
                    ],

                    'idUbicacionAsignar' => [
                        'nullable',
                        'integer',
                        'exists:ubicaciones,id_ubicacion',
                    ],

                    'fechaAsignacion' => [
                        'required',
                        'date',
                    ],

                    'idTipoAsignacion' => [
                        'required',
                        'integer',
                        'exists:catalogo_valores,id_valor',
                    ],

                    'observacionesAsignacion' => [
                        'nullable',
                        'string',
                        'max:2000',
                    ],
                ],
                [
                    'idEquipoAsignar.required' =>
                        'Selecciona un equipo.',

                    'nombreColaborador.required' =>
                        'Escribe el nombre completo del colaborador.',

                    'idSedeAsignar.required' =>
                        'Selecciona una sede.',

                    'idAreaAsignar.required' =>
                        'Selecciona un área.',

                    'idDepartamentoAsignar.required' =>
                        'Selecciona un departamento.',

                    'fechaAsignacion.required' =>
                        'Selecciona la fecha de asignación.',

                    'idTipoAsignacion.required' =>
                        'Selecciona el tipo de asignación.',
                ]
            )
                ->validate();

        /*
        |--------------------------------------------------------------------------
        | Sede -> Área
        |--------------------------------------------------------------------------
        */

        $areaValida =
            DB::table('areas')
                ->where(
                    'id_area',
                    (int) $validated['idAreaAsignar']
                )
                ->where(
                    'id_sede',
                    (int) $validated['idSedeAsignar']
                )
                ->exists();

        if (! $areaValida) {
            throw ValidationException::withMessages([
                'idAreaAsignar' =>
                    'El área seleccionada no pertenece a la sede indicada.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Área -> Departamento
        |--------------------------------------------------------------------------
        */

        $departamentoValido =
            DB::table('departamentos')
                ->where(
                    'id_departamento',
                    (int) $validated['idDepartamentoAsignar']
                )
                ->where(
                    'id_area',
                    (int) $validated['idAreaAsignar']
                )
                ->exists();

        if (! $departamentoValido) {
            throw ValidationException::withMessages([
                'idDepartamentoAsignar' =>
                    'El departamento no pertenece al área seleccionada.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Ubicación
        |--------------------------------------------------------------------------
        */

        if ($payload['idUbicacionAsignar'] !== '') {
            $ubicacionValida =
                DB::table('ubicaciones')
                    ->where(
                        'id_ubicacion',
                        (int) $payload['idUbicacionAsignar']
                    )
                    ->where(
                        'id_sede',
                        (int) $validated['idSedeAsignar']
                    )
                    ->where(
                        function ($query) use ($validated) {
                            $query
                                ->whereNull('id_area')
                                ->orWhere(
                                    'id_area',
                                    (int) $validated['idAreaAsignar']
                                );
                        }
                    )
                    ->exists();

            if (! $ubicacionValida) {
                throw ValidationException::withMessages([
                    'idUbicacionAsignar' =>
                        'La ubicación seleccionada no corresponde a la sede o al área.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Tipo de asignación
        |--------------------------------------------------------------------------
        */

        $tiposAsignacion =
            collect(
                $this->getTiposAsignacion()
            )
                ->pluck('value')
                ->map(
                    fn ($value) =>
                        (string) $value
                )
                ->all();

        if (
            ! in_array(
                (string) $validated['idTipoAsignacion'],
                $tiposAsignacion,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'idTipoAsignacion' =>
                    'El tipo de asignación seleccionado no es válido.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Fecha
        |--------------------------------------------------------------------------
        */

        $now = now();

        $fechaAsignacion =
            Carbon::parse(
                $validated['fechaAsignacion']
            )
                ->setTime(
                    $now->hour,
                    $now->minute,
                    $now->second
                );

        /*
        |--------------------------------------------------------------------------
        | Guardar
        |--------------------------------------------------------------------------
        */

        $resultado =
            DB::transaction(
                function () use (
                    $payload,
                    $validated,
                    $fechaAsignacion
                ): array {

                    $equipoId =
                        (int) $validated['idEquipoAsignar'];

                    /*
                    |--------------------------------------------------------------------------
                    | Bloquear equipo
                    |--------------------------------------------------------------------------
                    */

                    $equipo =
                        DB::table('equipos')
                            ->where(
                                'id_equipo',
                                $equipoId
                            )
                            ->lockForUpdate()
                            ->first([
                                'id_equipo',
                                'codigo_inventario',
                                'nombre_equipo',
                                'host',
                            ]);

                    if (! $equipo) {
                        throw ValidationException::withMessages([
                            'idEquipoAsignar' =>
                                'El equipo seleccionado ya no existe.',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Evitar doble asignación
                    |--------------------------------------------------------------------------
                    */

                    $yaAsignado =
                        DB::table('asignaciones')
                            ->where(
                                'id_equipo',
                                $equipoId
                            )
                            ->whereNull(
                                'fecha_fin'
                            )
                            ->exists();

                    if ($yaAsignado) {
                        throw ValidationException::withMessages([
                            'idEquipoAsignar' =>
                                'Este equipo ya tiene una asignación activa.',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Evitar equipos dados de baja
                    |--------------------------------------------------------------------------
                    */

                    $estaDeBaja =
                        DB::table('bajas')
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
                    */

                    $idAsignacion =
                        DB::table('asignaciones')
                            ->insertGetId(
                                [
                                    'id_equipo' =>
                                        $equipoId,

                                    'nombre_colaborador' =>
                                        trim(
                                            (string) $validated['nombreColaborador']
                                        ),

                                    'numero_colaborador' =>
                                        $this->nullableString(
                                            $payload['numeroColaborador']
                                        ),

                                    /*
                                     * Guardamos departamento.
                                     * El área puede obtenerse desde departamento.
                                     */
                                    'id_area' =>
                                        null,

                                    'id_departamento' =>
                                        (int) $validated['idDepartamentoAsignar'],

                                    'id_ubicacion' =>
                                        $this->nullableInt(
                                            $payload['idUbicacionAsignar']
                                        ),

                                    'id_tipo_asignacion' =>
                                        (int) $validated['idTipoAsignacion'],

                                    'fecha_asignacion' =>
                                        $fechaAsignacion,

                                    'fecha_fin' =>
                                        null,

                                    'observaciones' =>
                                        $this->nullableString(
                                            $payload['observacionesAsignacion']
                                        ),

                                    'asignado_por' =>
                                        auth()->id(),
                                ],
                                'id_asignacion'
                            );

                    /*
                    |--------------------------------------------------------------------------
                    | Estado ASIGNADO
                    |--------------------------------------------------------------------------
                    */

                    $estadoAsignado =
                        DB::table('estados_equipo')
                            ->whereRaw(
                                'LOWER(clave) = ?',
                                ['asignado']
                            )
                            ->value(
                                'id_estado_equipo'
                            );

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

                    return [
                        'idAsignacion' =>
                            $idAsignacion,

                        'equipoId' =>
                            $equipoId,

                        'codigoInventario' =>
                            (string) $equipo->codigo_inventario,

                        'nombreEquipo' =>
                            (string) (
                                $equipo->nombre_equipo
                                ?: $equipo->codigo_inventario
                            ),

                        'host' =>
                            (string) (
                                $equipo->host
                                ?? ''
                            ),
                    ];
                }
            );

        return [
            'ok' => true,

            'message' =>
                'El equipo fue asignado correctamente.',

            'record' => [
                'id' =>
                    $resultado['idAsignacion'],

                'equipmentId' =>
                    $resultado['equipoId'],

                'code' =>
                    $resultado['codigoInventario'],

                'name' =>
                    $resultado['nombreEquipo'],

                'host' =>
                    $resultado['host'],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | JSON inicial
    |--------------------------------------------------------------------------
    |
    | Todo lo que necesita Alpine se obtiene aquí.
    |
    | Después:
    |
    | Equipo -> Host
    | Sede -> Área
    | Área -> Departamento
    | Sede/Área -> Ubicación
    |
    | se resolverá en navegador sin requests Livewire.
    |
    */

    private function getAssignmentFrontendData(): array
    {
        $equipos =
            $this->getEquiposDisponibles();

        $sedes =
            $this->getSedes();

        $areas =
            DB::table('areas')
                ->where(
                    'activo',
                    true
                )
                ->orderBy(
                    'nombre'
                )
                ->get([
                    'id_area',
                    'id_sede',
                    'nombre',
                ])
                ->map(
                    fn ($area): array => [
                        'value' =>
                            (string) $area->id_area,

                        'label' =>
                            (string) $area->nombre,

                        'idSede' =>
                            (string) $area->id_sede,
                    ]
                )
                ->values()
                ->all();

        $departamentos =
            DB::table('departamentos')
                ->where(
                    'activo',
                    true
                )
                ->orderBy(
                    'nombre'
                )
                ->get([
                    'id_departamento',
                    'id_area',
                    'nombre',
                ])
                ->map(
                    fn ($departamento): array => [
                        'value' =>
                            (string) $departamento->id_departamento,

                        'label' =>
                            (string) $departamento->nombre,

                        'idArea' =>
                            (string) $departamento->id_area,
                    ]
                )
                ->values()
                ->all();

        $ubicaciones =
            DB::table('ubicaciones')
                ->where(
                    'activo',
                    true
                )
                ->orderBy(
                    'nombre'
                )
                ->get([
                    'id_ubicacion',
                    'id_sede',
                    'id_area',
                    'nombre',
                ])
                ->map(
                    fn ($ubicacion): array => [
                        'value' =>
                            (string) $ubicacion->id_ubicacion,

                        'label' =>
                            (string) $ubicacion->nombre,

                        'idSede' =>
                            (string) $ubicacion->id_sede,

                        'idArea' =>
                            $ubicacion->id_area !== null
                                ? (string) $ubicacion->id_area
                                : '',
                    ]
                )
                ->values()
                ->all();

        $tiposAsignacion =
            $this->getTiposAsignacion();

        return [
            'equipos' =>
                $equipos,

            'sedes' =>
                $sedes,

            'areas' =>
                $areas,

            'departamentos' =>
                $departamentos,

            'ubicaciones' =>
                $ubicaciones,

            'tiposAsignacion' =>
                $tiposAsignacion,

            'defaults' => [
                'fechaAsignacion' =>
                    $this->fechaAsignacion,

                'idTipoAsignacion' =>
                    count($tiposAsignacion) === 1
                        ? (string) $tiposAsignacion[0]['value']
                        : (string) $this->idTipoAsignacion,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Equipos disponibles
    |--------------------------------------------------------------------------
    */

    private function getEquiposDisponibles(): array
    {
        return DB::table('equipos as e')
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
                'e.host',
                'e.numero_serie',
                'e.service_tag',
                'te.nombre as tipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
            ])
            ->map(
                function ($equipo): array {
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

                        'code' =>
                            (string) $equipo->codigo_inventario,

                        'name' =>
                            (string) (
                                $equipo->nombre_equipo
                                ?: $equipo->codigo_inventario
                            ),

                        /*
                         * Lo usaremos para llenar Host localmente.
                         */
                        'host' =>
                            (string) (
                                $equipo->host
                                ?? ''
                            ),

                        'serial' =>
                            (string) (
                                $equipo->numero_serie
                                ?? ''
                            ),

                        'serviceTag' =>
                            (string) (
                                $equipo->service_tag
                                ?? ''
                            ),
                    ];
                }
            )
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Sedes
    |--------------------------------------------------------------------------
    */

    private function getSedes(): array
    {
        return DB::table('sedes')
            ->where(
                'activo',
                true
            )
            ->orderBy(
                'nombre'
            )
            ->get([
                'id_sede',
                'nombre',
            ])
            ->map(
                fn ($sede): array => [
                    'value' =>
                        (string) $sede->id_sede,

                    'label' =>
                        (string) $sede->nombre,
                ]
            )
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Tipos de asignación
    |--------------------------------------------------------------------------
    */

    private function getTiposAsignacion(): array
    {
        if ($this->tiposAsignacionRequestCache !== null) {
            return $this->tiposAsignacionRequestCache;
        }

        $tipos =
            DB::table('catalogo_valores as cv')
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
                                ['%asign%']
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
                    fn ($item): array => [
                        'value' =>
                            (string) $item->id_valor,

                        'label' =>
                            (string) $item->nombre,
                    ]
                )
                ->values()
                ->all();

        /*
         * Fallback conservado de la versión actual.
         */
        if ($tipos === []) {
            $idsUsados =
                DB::table('asignaciones')
                    ->whereNotNull(
                        'id_tipo_asignacion'
                    )
                    ->distinct()
                    ->pluck(
                        'id_tipo_asignacion'
                    );

            if ($idsUsados->isNotEmpty()) {
                $tipos =
                    DB::table('catalogo_valores')
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
                            fn ($item): array => [
                                'value' =>
                                    (string) $item->id_valor,

                                'label' =>
                                    (string) $item->nombre,
                            ]
                        )
                        ->values()
                        ->all();
            }
        }

        return $this->tiposAsignacionRequestCache =
            $tipos;
    }

    /*
    |--------------------------------------------------------------------------
    | Payload
    |--------------------------------------------------------------------------
    */

    private function normalizePayload(array $payload): array
    {
        return array_merge(
            [
                'tipoMovimiento' =>
                    'asignacion',

                'idEquipoAsignar' =>
                    '',

                'nombreColaborador' =>
                    '',

                'numeroColaborador' =>
                    '',

                'idSedeAsignar' =>
                    '',

                'idAreaAsignar' =>
                    '',

                'idDepartamentoAsignar' =>
                    '',

                'idUbicacionAsignar' =>
                    '',

                'fechaAsignacion' =>
                    '',

                'idTipoAsignacion' =>
                    '',

                'observacionesAsignacion' =>
                    '',
            ],
            $payload
        );
    }

    private function nullableInt(mixed $value): ?int
    {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        return is_numeric($value)
            ? (int) $value
            : null;
    }

    private function nullableString(mixed $value): ?string
    {
        if (
            $value === null
            || ! is_string($value)
        ) {
            return null;
        }

        $value =
            trim($value);

        return $value !== ''
            ? $value
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Placeholder
    |--------------------------------------------------------------------------
    */

    public function placeholder(): View
    {
        return view(
            'livewire.placeholders.asignacion-create'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(): View
    {
        $assignmentData =
            $this->getAssignmentFrontendData();

        /*
         * Compatibilidad temporal con el Blade actual.
         * En el siguiente paso estos filtros se harán con Alpine.
         */

        $equiposDisponibles =
            collect(
                $assignmentData['equipos']
            )
                ->map(
                    fn (array $equipo): array => [
                        'value' =>
                            $equipo['value'],

                        'label' =>
                            $equipo['label'],
                    ]
                )
                ->values()
                ->all();

        $areas =
            $this->idSedeAsignar === ''
                ? []
                : collect(
                    $assignmentData['areas']
                )
                    ->filter(
                        fn (array $area): bool =>
                            (string) $area['idSede']
                            ===
                            (string) $this->idSedeAsignar
                    )
                    ->values()
                    ->all();

        $departamentos =
            $this->idAreaAsignar === ''
                ? []
                : collect(
                    $assignmentData['departamentos']
                )
                    ->filter(
                        fn (array $departamento): bool =>
                            (string) $departamento['idArea']
                            ===
                            (string) $this->idAreaAsignar
                    )
                    ->values()
                    ->all();

        $ubicaciones =
            $this->idSedeAsignar === ''
                ? []
                : collect(
                    $assignmentData['ubicaciones']
                )
                    ->filter(
                        function (array $ubicacion): bool {
                            if (
                                (string) $ubicacion['idSede']
                                !==
                                (string) $this->idSedeAsignar
                            ) {
                                return false;
                            }

                            if ($this->idAreaAsignar === '') {
                                return true;
                            }

                            return
                                $ubicacion['idArea'] === ''
                                ||
                                (string) $ubicacion['idArea']
                                ===
                                (string) $this->idAreaAsignar;
                        }
                    )
                    ->values()
                    ->all();

        return view(
            'livewire.asignacion-create',
            [
                /*
                 * Nuevo paquete JSON.
                 */
                'assignmentData' =>
                    $assignmentData,

                /*
                 * Compatibilidad temporal con la vista actual.
                 */
                'equiposDisponibles' =>
                    $equiposDisponibles,

                'equiposMeta' =>
                    $assignmentData['equipos'],

                'sedes' =>
                    $assignmentData['sedes'],

                'areas' =>
                    $areas,

                'departamentos' =>
                    $departamentos,

                'ubicaciones' =>
                    $ubicaciones,

                'tiposAsignacion' =>
                    $assignmentData['tiposAsignacion'],
            ]
        );
    }
}