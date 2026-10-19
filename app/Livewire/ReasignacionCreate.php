<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Json;
use Livewire\Component;

class ReasignacionCreate extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Equipo / asignación actual
    |--------------------------------------------------------------------------
    */

    public string $idEquipoReasignar = '';

    public string $idAsignacionActual = '';
    public string $responsableActual = '';
    public string $numeroColaboradorActual = '';
    public string $hostActual = '';
    public string $unidadDepartamentoActual = '';
    public string $ubicacionActual = '';
    public string $fechaAsignacionActual = '';
    public string $tipoAsignacionActual = '';

    public string $idUnidadActual = '';
    public string $idDepartamentoActual = '';
    public string $idUbicacionActual = '';
    public string $idTipoAsignacionActual = '';

    /*
    |--------------------------------------------------------------------------
    | Nueva reasignación
    |--------------------------------------------------------------------------
    */

    public string $motivoReasignacion = '';
    public string $nombreNuevoColaborador = '';

    public string $idUnidadNueva = '';
    public string $idDepartamentoNuevo = '';
    public string $idUbicacionNueva = '';

    public string $fechaReasignacion = '';
    public string $observacionesReasignacion = '';

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->fechaReasignacion = now()->toDateString();
    }

    /*
    |--------------------------------------------------------------------------
    | Compatibilidad temporal con el Blade actual
    |--------------------------------------------------------------------------
    |
    | Cuando pasemos los selects a Alpine estos hooks dejarán de dispararse,
    | porque las selecciones ya no se sincronizarán con Livewire.
    |
    */

    public function updatedIdEquipoReasignar(): void
    {
        $this->resetCurrentAssignment();
        $this->resetNewDestination();

        if ($this->idEquipoReasignar === '') {
            return;
        }

        $this->loadCurrentAssignment(
            (int) $this->idEquipoReasignar
        );
    }

    public function updatedIdUnidadNueva(): void
    {
        $this->idDepartamentoNuevo = '';
        $this->idUbicacionNueva = '';
    }

    public function updatedIdDepartamentoNuevo(): void
    {
        $this->idUbicacionNueva = '';
    }

    /*
    |--------------------------------------------------------------------------
    | Cargar asignación actual
    |--------------------------------------------------------------------------
    |
    | Este método queda únicamente por compatibilidad con la vista actual.
    | Después de mover la selección de equipo a Alpine ya no será necesario
    | hacer esta consulta al seleccionar.
    |
    */

    private function loadCurrentAssignment(int $equipoId): void
    {
        $actual = $this->asignacionesVigentesQuery()
            ->where(
                'a.id_equipo',
                $equipoId
            )
            ->orderByDesc(
                'a.fecha_asignacion'
            )
            ->first();

        if (! $actual) {
            $this->idEquipoReasignar = '';

            throw ValidationException::withMessages([
                'idEquipoReasignar' =>
                    'El equipo seleccionado no tiene una asignación activa.',
            ]);
        }

        $this->idAsignacionActual =
            (string) $actual->id_asignacion;

        $this->responsableActual =
            (string) $actual->nombre_colaborador;

        $this->numeroColaboradorActual =
            (string) ($actual->numero_colaborador ?? '');

        $this->hostActual =
            (string) ($actual->host ?? '');

        $this->idUnidadActual =
            (string) ($actual->unidad_id ?? '');

        $this->idDepartamentoActual =
            (string) ($actual->id_departamento ?? '');

        $this->idUbicacionActual =
            (string) ($actual->id_ubicacion ?? '');

        $this->idTipoAsignacionActual =
            (string) $actual->id_tipo_asignacion;

        $this->unidadDepartamentoActual =
            $this->buildAreaDepartmentLabel(
                $actual->unidad_nombre ?? null,
                $actual->departamento_nombre ?? null
            );

        $this->ubicacionActual =
            filled($actual->ubicacion_nombre ?? null)
                ? (string) $actual->ubicacion_nombre
                : 'Sin ubicación específica';

        $this->fechaAsignacionActual =
            $actual->fecha_asignacion
                ? Carbon::parse(
                    $actual->fecha_asignacion
                )->format('d/m/Y')
                : '';

        $this->tipoAsignacionActual =
            (string) (
                $actual->tipo_asignacion_nombre
                ?? 'Sin especificar'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar reasignación
    |--------------------------------------------------------------------------
    */

    #[Json]
    public function save(array $payload): array
    {
        // Permiso por rol (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('gestionar');

        $payload =
            $this->normalizePayload(
                $payload
            );

        $validated =
            Validator::make(
                $payload,
                [
                    'idEquipoReasignar' => [
                        'required',
                        'integer',
                        'exists:equipos,id_equipo',
                    ],

                    'idAsignacionActual' => [
                        'required',
                        'integer',
                        'exists:asignaciones,id_asignacion',
                    ],

                    'motivoReasignacion' => [
                        'required',
                        'string',
                        'max:500',
                    ],

                    'nombreNuevoColaborador' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'idUnidadNueva' => [
                        'required',
                        'integer',
                    ],

                    'idDepartamentoNuevo' => [
                        'required',
                        'integer',
                        'exists:departamentos,id_departamento',
                    ],

                    'idUbicacionNueva' => [
                        'nullable',
                        'integer',
                        'exists:ubicaciones,id_ubicacion',
                    ],

                    'fechaReasignacion' => [
                        'required',
                        'date',
                    ],

                    'observacionesReasignacion' => [
                        'nullable',
                        'string',
                        'max:2000',
                    ],
                ],
                [
                    'idEquipoReasignar.required' =>
                        'Selecciona el equipo que se reasignará.',

                    'idAsignacionActual.required' =>
                        'No se encontró la asignación actual del equipo.',

                    'motivoReasignacion.required' =>
                        'Escribe el motivo de la reasignación.',

                    'nombreNuevoColaborador.required' =>
                        'Escribe el nombre del nuevo colaborador.',

                    'idUnidadNueva.required' =>
                        'Selecciona el nuevo hotel.',

                    'idDepartamentoNuevo.required' =>
                        'Selecciona el nuevo departamento.',

                    'fechaReasignacion.required' =>
                        'Selecciona la fecha de reasignación.',
                ]
            )
                ->validate();

        /*
        |--------------------------------------------------------------------------
        | Validar Hotel -> Departamento -> Ubicación
        |--------------------------------------------------------------------------
        */

        $organizacion =
            app(\App\Services\UbicacionOrganizacionalService::class)
                ->validar(
                    $validated['idUnidadNueva'],
                    $validated['idDepartamentoNuevo'],
                    $payload['idUbicacionNueva'],
                    [
                        'unidad' => 'idUnidadNueva',
                        'departamento' => 'idDepartamentoNuevo',
                        'ubicacion' => 'idUbicacionNueva',
                    ]
                );

        /*
        |--------------------------------------------------------------------------
        | Tipo de movimiento
        |--------------------------------------------------------------------------
        */

        $idTipoMovimiento =
            $this->resolveReassignmentMovementType();

        if ($idTipoMovimiento === null) {
            throw ValidationException::withMessages([
                'motivoReasignacion' =>
                    'No existe un tipo de movimiento activo para Reasignación. Revisa el catálogo de movimientos.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Fecha
        |--------------------------------------------------------------------------
        */

        $now = now();

        $fechaReasignacion =
            Carbon::parse(
                $validated['fechaReasignacion']
            )
                ->setTime(
                    $now->hour,
                    $now->minute,
                    $now->second
                );

        /*
        |--------------------------------------------------------------------------
        | Transacción
        |--------------------------------------------------------------------------
        */

        $resultado =
            DB::transaction(
                function () use (
                    $payload,
                    $validated,
                    $organizacion,
                    $idTipoMovimiento,
                    $fechaReasignacion
                ): array {

                    $equipoId =
                        (int) $validated['idEquipoReasignar'];

                    $asignacionActualId =
                        (int) $validated['idAsignacionActual'];

                    /*
                    |--------------------------------------------------------------------------
                    | Equipo
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
                            'idEquipoReasignar' =>
                                'El equipo seleccionado ya no existe.',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Asignación vigente
                    |--------------------------------------------------------------------------
                    */

                    $asignacionActual =
                        DB::table('asignaciones')
                            ->where(
                                'id_asignacion',
                                $asignacionActualId
                            )
                            ->where(
                                'id_equipo',
                                $equipoId
                            )
                            ->whereNull(
                                'fecha_fin'
                            )
                            ->lockForUpdate()
                            ->first();

                    if (! $asignacionActual) {
                        throw ValidationException::withMessages([
                            'idEquipoReasignar' =>
                                'La asignación del equipo cambió o ya no está vigente. Vuelve a seleccionar el equipo.',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Fecha válida
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $fechaReasignacion->lt(
                            Carbon::parse(
                                $asignacionActual->fecha_asignacion
                            )
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'fechaReasignacion' =>
                                'La fecha de reasignación no puede ser anterior a la asignación actual.',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Evitar reasignación idéntica
                    |--------------------------------------------------------------------------
                    */

                    $mismoColaborador =
                        mb_strtolower(
                            trim(
                                (string) $asignacionActual->nombre_colaborador
                            )
                        )
                        ===
                        mb_strtolower(
                            trim(
                                (string) $validated['nombreNuevoColaborador']
                            )
                        );

                    $mismoHotel =
                        (int) ($asignacionActual->id_unidad ?? 0)
                        ===
                        $organizacion['id_unidad'];

                    $mismoDepartamento =
                        (int) (
                            $asignacionActual->id_departamento
                            ?? 0
                        )
                        ===
                        $organizacion['id_departamento'];

                    $mismaUbicacion =
                        (int) (
                            $asignacionActual->id_ubicacion
                            ?? 0
                        )
                        ===
                        (int) (
                            $organizacion['id_ubicacion']
                            ?? 0
                        );

                    if (
                        $mismoColaborador
                        && $mismoHotel
                        && $mismoDepartamento
                        && $mismaUbicacion
                    ) {
                        throw ValidationException::withMessages([
                            'nombreNuevoColaborador' =>
                                'La nueva asignación es igual a la asignación actual.',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Cerrar asignación anterior
                    |--------------------------------------------------------------------------
                    */

                    DB::table('asignaciones')
                        ->where(
                            'id_asignacion',
                            $asignacionActualId
                        )
                        ->update([
                            'fecha_fin' =>
                                $fechaReasignacion,
                        ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Nueva asignación
                    |--------------------------------------------------------------------------
                    */

                    $idAsignacionNueva =
                        DB::table('asignaciones')
                            ->insertGetId(
                                [
                                    'id_equipo' =>
                                        $equipoId,

                                    'nombre_colaborador' =>
                                        trim(
                                            (string) $validated['nombreNuevoColaborador']
                                        ),

                                    'numero_colaborador' =>
                                        null,

                                    /*
                                     * Igual que en asignación: la
                                     * organización es hotel + departamento.
                                     */
                                    'id_area' =>
                                        null,

                                    'id_unidad' =>
                                        $organizacion['id_unidad'],

                                    'id_departamento' =>
                                        $organizacion['id_departamento'],

                                    'id_ubicacion' =>
                                        $organizacion['id_ubicacion'],

                                    'id_tipo_asignacion' =>
                                        (int) $asignacionActual->id_tipo_asignacion,

                                    'fecha_asignacion' =>
                                        $fechaReasignacion,

                                    'fecha_fin' =>
                                        null,

                                    'observaciones' =>
                                        $this->nullableString(
                                            $payload['observacionesReasignacion']
                                        ),

                                    'asignado_por' =>
                                        auth()->id(),
                                ],
                                'id_asignacion'
                            );

                    /*
                    |--------------------------------------------------------------------------
                    | Movimiento
                    |--------------------------------------------------------------------------
                    */

                    $idMovimiento =
                        DB::table('movimientos')
                            ->insertGetId(
                                [
                                    'id_equipo' =>
                                        $equipoId,

                                    'id_tipo_movimiento' =>
                                        $idTipoMovimiento,

                                    'id_asignacion_anterior' =>
                                        $asignacionActualId,

                                    'id_asignacion_nueva' =>
                                        $idAsignacionNueva,

                                    'motivo' =>
                                        trim(
                                            (string) $validated['motivoReasignacion']
                                        ),

                                    'observaciones' =>
                                        $this->nullableString(
                                            $payload['observacionesReasignacion']
                                        ),

                                    'realizado_por' =>
                                        auth()->id(),

                                    'fecha_hora' =>
                                        $fechaReasignacion,
                                ],
                                'id_movimiento'
                            );

                    /*
                    |--------------------------------------------------------------------------
                    | Mantener equipo como ASIGNADO
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
                        'idMovimiento' =>
                            $idMovimiento,

                        'idAsignacionAnterior' =>
                            $asignacionActualId,

                        'idAsignacionNueva' =>
                            $idAsignacionNueva,

                        'equipoId' =>
                            $equipoId,

                        'codigoInventario' =>
                            (string) $equipo->codigo_inventario,
                    ];
                }
            );

        $notificaciones = app(\App\Services\NotificacionService::class);

        $notificaciones->notificar(
            'asignaciones',
            'Equipo reasignado',
            $notificaciones->nombreActor()
                . ' reasignó el equipo '
                . $notificaciones->etiquetaEquipo((int) $resultado['equipoId'])
                . ' a un nuevo responsable.',
            route('equipos.show', (int) $resultado['equipoId'], false)
        );

        $this->dispatch('notificaciones-actualizadas');

        return [
            'ok' => true,

            'message' =>
                'El equipo fue reasignado correctamente.',

            'record' => [
                'movementId' =>
                    $resultado['idMovimiento'],

                'previousAssignmentId' =>
                    $resultado['idAsignacionAnterior'],

                'newAssignmentId' =>
                    $resultado['idAsignacionNueva'],

                'equipmentId' =>
                    $resultado['equipoId'],

                'code' =>
                    $resultado['codigoInventario'],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | JSON inicial para Alpine
    |--------------------------------------------------------------------------
    |
    | Este es el cambio importante.
    |
    | Toda la información que necesita el formulario se obtiene durante la
    | carga inicial. Después Alpine podrá resolver localmente:
    |
    | Equipo -> asignación actual
    | Hotel -> departamentos
    | Hotel/Departamento -> ubicaciones
    |
    */

    private function getReassignmentFrontendData(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Equipos + asignación actual
        |--------------------------------------------------------------------------
        */

        $equipos =
            $this->asignacionesVigentesQuery()
                ->orderBy(
                    'e.codigo_inventario'
                )
                ->orderByDesc(
                    'a.fecha_asignacion'
                )
                ->get()
                ->unique(
                    'id_equipo'
                )
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

                        $responsable =
                            trim(
                                (string) $equipo->nombre_colaborador
                            );

                        return [
                            /*
                             * Estas dos propiedades las consume directamente
                             * searchable-select.
                             */
                            'value' =>
                                (string) $equipo->id_equipo,

                            'label' =>
                                $equipo->codigo_inventario
                                . ' — '
                                . $descripcion
                                . (
                                    $responsable !== ''
                                        ? ' — ' . $responsable
                                        : ''
                                ),

                            /*
                             * Toda la asignación actual viaja en el mismo JSON.
                             */
                            'assignment' => [
                                'idAsignacion' =>
                                    (string) $equipo->id_asignacion,

                                'responsable' =>
                                    (string) $equipo->nombre_colaborador,

                                'numeroColaborador' =>
                                    (string) (
                                        $equipo->numero_colaborador
                                        ?? ''
                                    ),

                                'host' =>
                                    (string) (
                                        $equipo->host
                                        ?? ''
                                    ),

                                'idUnidad' =>
                                    (string) (
                                        $equipo->unidad_id
                                        ?? ''
                                    ),

                                'idDepartamento' =>
                                    (string) (
                                        $equipo->id_departamento
                                        ?? ''
                                    ),

                                'idUbicacion' =>
                                    (string) (
                                        $equipo->id_ubicacion
                                        ?? ''
                                    ),

                                'idTipoAsignacion' =>
                                    (string) $equipo->id_tipo_asignacion,

                                'unidadDepartamento' =>
                                    $this->buildAreaDepartmentLabel(
                                        $equipo->unidad_nombre
                                            ?? null,
                                        $equipo->departamento_nombre
                                            ?? null
                                    ),

                                'ubicacion' =>
                                    filled($equipo->ubicacion_nombre ?? null)
                                        ? (string) $equipo->ubicacion_nombre
                                        : 'Sin ubicación específica',

                                'fechaAsignacion' =>
                                    $equipo->fecha_asignacion
                                        ? Carbon::parse(
                                            $equipo->fecha_asignacion
                                        )->format('d/m/Y')
                                        : '',

                                'tipoAsignacion' =>
                                    (string) (
                                        $equipo->tipo_asignacion_nombre
                                        ?? 'Sin especificar'
                                    ),
                            ],
                        ];
                    }
                )
                ->values()
                ->all();

        $organizacion =
            app(\App\Services\UbicacionOrganizacionalService::class)
                ->datosFormulario();

        return [
            'equipos' =>
                $equipos,

            'unidades' =>
                $organizacion['unidades'],

            'departamentos' =>
                $organizacion['departamentos'],

            'ubicaciones' =>
                $organizacion['ubicaciones'],

            'defaults' => [
                'fechaReasignacion' =>
                    $this->fechaReasignacion,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Tipo de movimiento: Reasignación
    |--------------------------------------------------------------------------
    */

    private function resolveReassignmentMovementType(): ?int
    {
        /*
         * Primero buscamos el registro exacto que ya creamos:
         *
         * catalogo: tipo_movimiento
         * valor:    REASIGNACION
         */
        $value =
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
                ->whereRaw(
                    'LOWER(c.clave) = ?',
                    ['tipo_movimiento']
                )
                ->whereRaw(
                    'LOWER(cv.clave) = ?',
                    ['reasignacion']
                )
                ->value(
                    'cv.id_valor'
                );

        if ($value !== null) {
            return (int) $value;
        }

        /*
         * Fallback para conservar compatibilidad si alguna vez cambia
         * la clave pero continúa existiendo un valor Reasignación.
         */
        $value =
            DB::table('catalogo_valores')
                ->where(
                    'activo',
                    true
                )
                ->where(
                    function ($query) {
                        $query
                            ->whereRaw(
                                'LOWER(clave) LIKE ?',
                                ['%reasign%']
                            )
                            ->orWhereRaw(
                                'LOWER(nombre) LIKE ?',
                                ['%reasign%']
                            );
                    }
                )
                ->value(
                    'id_valor'
                );

        return $value !== null
            ? (int) $value
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Asignaciones vigentes con hotel, departamento y ubicación
    |--------------------------------------------------------------------------
    |
    | El hotel sale de asignaciones.id_unidad. Para registros anteriores
    | que no lo tienen se usa el hotel cuya área de origen coincide con
    | el área registrada (o la del departamento), igual que la migración
    | que creó id_unidad.
    |
    */

    private function asignacionesVigentesQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('asignaciones as a')
            ->join('equipos as e', 'e.id_equipo', '=', 'a.id_equipo')
            ->leftJoin('tipos_equipo as te', 'te.id_tipo_equipo', '=', 'e.id_tipo_equipo')
            ->leftJoin('marcas as ma', 'ma.id_marca', '=', 'e.id_marca')
            ->leftJoin('modelos as mo', 'mo.id_modelo', '=', 'e.id_modelo')
            ->leftJoin('departamentos as d', 'd.id_departamento', '=', 'a.id_departamento')
            ->leftJoin('unidades_organizativas as un', 'un.id_unidad', '=', 'a.id_unidad')
            ->leftJoin('unidades_organizativas as un_origen', function ($join) {
                $join->whereNull('a.id_unidad')
                    ->whereColumn(
                        'un_origen.id_area_origen',
                        DB::raw('COALESCE(a.id_area, d.id_area)')
                    );
            })
            ->leftJoin('ubicaciones as ubicacion', 'ubicacion.id_ubicacion', '=', 'a.id_ubicacion')
            ->leftJoin('catalogo_valores as tipo_asignacion', 'tipo_asignacion.id_valor', '=', 'a.id_tipo_asignacion')
            ->whereNull('a.fecha_fin')
            ->select([
                'a.id_asignacion',
                'a.id_equipo',
                'a.nombre_colaborador',
                'a.numero_colaborador',
                'a.id_departamento',
                'a.id_ubicacion',
                'a.id_tipo_asignacion',
                'a.fecha_asignacion',

                'e.codigo_inventario',
                'e.nombre_equipo',
                'e.host',

                'te.nombre as tipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',

                'd.nombre as departamento_nombre',
                'ubicacion.nombre as ubicacion_nombre',
                'tipo_asignacion.nombre as tipo_asignacion_nombre',
            ])
            ->selectRaw('COALESCE(un.id_unidad, un_origen.id_unidad) as unidad_id')
            ->selectRaw('COALESCE(un.nombre, un_origen.nombre) as unidad_nombre');
    }

    /*
    |--------------------------------------------------------------------------
    | Área / departamento
    |--------------------------------------------------------------------------
    */

    private function buildAreaDepartmentLabel(
        ?string $area,
        ?string $departamento
    ): string {
        /*
         * $area recibe ahora el nombre del hotel.
         */
        $area =
            trim(
                (string) $area
            );

        $departamento =
            trim(
                (string) $departamento
            );

        if (
            $area !== ''
            && $departamento !== ''
        ) {
            return
                $area
                . ' / '
                . $departamento;
        }

        if ($departamento !== '') {
            return $departamento;
        }

        if ($area !== '') {
            return $area;
        }

        return 'Sin hotel / departamento';
    }

    /*
    |--------------------------------------------------------------------------
    | Limpiar asignación actual
    |--------------------------------------------------------------------------
    */

    private function resetCurrentAssignment(): void
    {
        $this->reset([
            'idAsignacionActual',
            'responsableActual',
            'numeroColaboradorActual',
            'hostActual',
            'unidadDepartamentoActual',
            'ubicacionActual',
            'fechaAsignacionActual',
            'tipoAsignacionActual',
            'idUnidadActual',
            'idDepartamentoActual',
            'idUbicacionActual',
            'idTipoAsignacionActual',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Limpiar destino
    |--------------------------------------------------------------------------
    */

    private function resetNewDestination(): void
    {
        $this->reset([
            'idUnidadNueva',
            'idDepartamentoNuevo',
            'idUbicacionNueva',
        ]);
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
                'idEquipoReasignar' => '',
                'idAsignacionActual' => '',
                'motivoReasignacion' => '',
                'nombreNuevoColaborador' => '',
                'idUnidadNueva' => '',
                'idDepartamentoNuevo' => '',
                'idUbicacionNueva' => '',
                'fechaReasignacion' => '',
                'observacionesReasignacion' => '',
            ],
            $payload
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

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
            trim(
                $value
            );

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
            'livewire.placeholders.reasignacion-create'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(): View
    {
        return view(
            'livewire.reasignacion-create',
            [
                'reassignmentData' =>
                    $this->getReassignmentFrontendData(),
            ]
        );
    }
}
