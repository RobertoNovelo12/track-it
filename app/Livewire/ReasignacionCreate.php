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
    public string $areaDepartamentoActual = '';
    public string $sedeActual = '';
    public string $fechaAsignacionActual = '';
    public string $tipoAsignacionActual = '';

    public string $idAreaActual = '';
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

    public string $idSedeNueva = '';
    public string $idAreaNueva = '';
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

    public function updatedIdSedeNueva(): void
    {
        $this->idAreaNueva = '';
        $this->idDepartamentoNuevo = '';
        $this->idUbicacionNueva = '';
    }

    public function updatedIdAreaNueva(): void
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
        $actual = DB::table('asignaciones as a')
            ->join(
                'equipos as e',
                'e.id_equipo',
                '=',
                'a.id_equipo'
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
                'ubicaciones as ubicacion',
                'ubicacion.id_ubicacion',
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
                'ubicacion.id_sede'
            )
            ->leftJoin(
                'catalogo_valores as tipo_asignacion',
                'tipo_asignacion.id_valor',
                '=',
                'a.id_tipo_asignacion'
            )
            ->where(
                'a.id_equipo',
                $equipoId
            )
            ->whereNull(
                'a.fecha_fin'
            )
            ->orderByDesc(
                'a.fecha_asignacion'
            )
            ->first([
                'a.id_asignacion',
                'a.id_equipo',
                'a.nombre_colaborador',
                'a.numero_colaborador',
                'a.id_area',
                'a.id_departamento',
                'a.id_ubicacion',
                'a.id_tipo_asignacion',
                'a.fecha_asignacion',

                'e.host',

                'd.nombre as departamento_nombre',

                'area_directa.id_area as area_directa_id',
                'area_directa.nombre as area_directa_nombre',

                'area_departamento.id_area as area_departamento_id',
                'area_departamento.nombre as area_departamento_nombre',

                'sede_directa.nombre as sede_directa_nombre',
                'sede_departamento.nombre as sede_departamento_nombre',
                'sede_ubicacion.nombre as sede_ubicacion_nombre',

                'tipo_asignacion.nombre as tipo_asignacion_nombre',
            ]);

        if (! $actual) {
            $this->idEquipoReasignar = '';

            throw ValidationException::withMessages([
                'idEquipoReasignar' =>
                    'El equipo seleccionado no tiene una asignación activa.',
            ]);
        }

        $areaId =
            $actual->area_departamento_id
            ?? $actual->area_directa_id;

        $areaNombre =
            $actual->area_departamento_nombre
            ?? $actual->area_directa_nombre
            ?? '';

        $sedeNombre =
            $actual->sede_departamento_nombre
            ?? $actual->sede_directa_nombre
            ?? $actual->sede_ubicacion_nombre
            ?? '';

        $this->idAsignacionActual =
            (string) $actual->id_asignacion;

        $this->responsableActual =
            (string) $actual->nombre_colaborador;

        $this->numeroColaboradorActual =
            (string) ($actual->numero_colaborador ?? '');

        $this->hostActual =
            (string) ($actual->host ?? '');

        $this->idAreaActual =
            (string) ($areaId ?? '');

        $this->idDepartamentoActual =
            (string) ($actual->id_departamento ?? '');

        $this->idUbicacionActual =
            (string) ($actual->id_ubicacion ?? '');

        $this->idTipoAsignacionActual =
            (string) $actual->id_tipo_asignacion;

        $this->areaDepartamentoActual =
            $this->buildAreaDepartmentLabel(
                $areaNombre,
                $actual->departamento_nombre ?? null
            );

        $this->sedeActual =
            $sedeNombre !== ''
                ? $sedeNombre
                : 'Sin sede';

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

                    'idSedeNueva' => [
                        'required',
                        'integer',
                        'exists:sedes,id_sede',
                    ],

                    'idAreaNueva' => [
                        'required',
                        'integer',
                        'exists:areas,id_area',
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

                    'idSedeNueva.required' =>
                        'Selecciona la nueva sede.',

                    'idAreaNueva.required' =>
                        'Selecciona la nueva área.',

                    'idDepartamentoNuevo.required' =>
                        'Selecciona el nuevo departamento.',

                    'fechaReasignacion.required' =>
                        'Selecciona la fecha de reasignación.',
                ]
            )
                ->validate();

        /*
        |--------------------------------------------------------------------------
        | Validar Sede -> Área
        |--------------------------------------------------------------------------
        */

        $areaValida =
            DB::table('areas')
                ->where(
                    'id_area',
                    (int) $validated['idAreaNueva']
                )
                ->where(
                    'id_sede',
                    (int) $validated['idSedeNueva']
                )
                ->where(
                    'activo',
                    true
                )
                ->exists();

        if (! $areaValida) {
            throw ValidationException::withMessages([
                'idAreaNueva' =>
                    'El área seleccionada no pertenece a la sede indicada.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar Área -> Departamento
        |--------------------------------------------------------------------------
        */

        $departamentoValido =
            DB::table('departamentos')
                ->where(
                    'id_departamento',
                    (int) $validated['idDepartamentoNuevo']
                )
                ->where(
                    'id_area',
                    (int) $validated['idAreaNueva']
                )
                ->where(
                    'activo',
                    true
                )
                ->exists();

        if (! $departamentoValido) {
            throw ValidationException::withMessages([
                'idDepartamentoNuevo' =>
                    'El departamento seleccionado no pertenece al área indicada.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar ubicación
        |--------------------------------------------------------------------------
        */

        if ($payload['idUbicacionNueva'] !== '') {
            $ubicacionValida =
                DB::table('ubicaciones')
                    ->where(
                        'id_ubicacion',
                        (int) $payload['idUbicacionNueva']
                    )
                    ->where(
                        'id_sede',
                        (int) $validated['idSedeNueva']
                    )
                    ->where(
                        'activo',
                        true
                    )
                    ->where(
                        function ($query) use ($validated) {
                            $query
                                ->whereNull(
                                    'id_area'
                                )
                                ->orWhere(
                                    'id_area',
                                    (int) $validated['idAreaNueva']
                                );
                        }
                    )
                    ->exists();

            if (! $ubicacionValida) {
                throw ValidationException::withMessages([
                    'idUbicacionNueva' =>
                        'La ubicación seleccionada no corresponde a la sede o al área.',
                ]);
            }
        }

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
                    | Determinar área actual
                    |--------------------------------------------------------------------------
                    */

                    $areaActualId =
                        $asignacionActual->id_area;

                    if (
                        $areaActualId === null
                        && $asignacionActual->id_departamento !== null
                    ) {
                        $areaActualId =
                            DB::table('departamentos')
                                ->where(
                                    'id_departamento',
                                    $asignacionActual->id_departamento
                                )
                                ->value(
                                    'id_area'
                                );
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

                    $mismoArea =
                        (int) ($areaActualId ?? 0)
                        ===
                        (int) $validated['idAreaNueva'];

                    $mismoDepartamento =
                        (int) (
                            $asignacionActual->id_departamento
                            ?? 0
                        )
                        ===
                        (int) $validated['idDepartamentoNuevo'];

                    $mismaUbicacion =
                        (int) (
                            $asignacionActual->id_ubicacion
                            ?? 0
                        )
                        ===
                        (int) (
                            $this->nullableInt(
                                $payload['idUbicacionNueva']
                            )
                            ?? 0
                        );

                    if (
                        $mismoColaborador
                        && $mismoArea
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
                                     * Igual que en asignación:
                                     * al guardar departamento dejamos
                                     * id_area en NULL.
                                     */
                                    'id_area' =>
                                        null,

                                    'id_departamento' =>
                                        (int) $validated['idDepartamentoNuevo'],

                                    'id_ubicacion' =>
                                        $this->nullableInt(
                                            $payload['idUbicacionNueva']
                                        ),

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
    | Sede -> áreas
    | Área -> departamentos
    | Sede/Área -> ubicaciones
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
            DB::table('asignaciones as a')
                ->join(
                    'equipos as e',
                    'e.id_equipo',
                    '=',
                    'a.id_equipo'
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
                    'ubicaciones as ubicacion',
                    'ubicacion.id_ubicacion',
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
                    'ubicacion.id_sede'
                )
                ->leftJoin(
                    'catalogo_valores as tipo_asignacion',
                    'tipo_asignacion.id_valor',
                    '=',
                    'a.id_tipo_asignacion'
                )
                ->whereNull(
                    'a.fecha_fin'
                )
                ->orderBy(
                    'e.codigo_inventario'
                )
                ->orderByDesc(
                    'a.fecha_asignacion'
                )
                ->get([
                    'a.id_asignacion',
                    'a.id_equipo',
                    'a.nombre_colaborador',
                    'a.numero_colaborador',
                    'a.id_area',
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

                    'area_directa.id_area as area_directa_id',
                    'area_directa.nombre as area_directa_nombre',

                    'area_departamento.id_area as area_departamento_id',
                    'area_departamento.nombre as area_departamento_nombre',

                    'sede_directa.nombre as sede_directa_nombre',
                    'sede_departamento.nombre as sede_departamento_nombre',
                    'sede_ubicacion.nombre as sede_ubicacion_nombre',

                    'tipo_asignacion.nombre as tipo_asignacion_nombre',
                ])
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

                        $areaId =
                            $equipo->area_departamento_id
                            ?? $equipo->area_directa_id;

                        $areaNombre =
                            $equipo->area_departamento_nombre
                            ?? $equipo->area_directa_nombre
                            ?? '';

                        $sedeNombre =
                            $equipo->sede_departamento_nombre
                            ?? $equipo->sede_directa_nombre
                            ?? $equipo->sede_ubicacion_nombre
                            ?? '';

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

                                'idArea' =>
                                    (string) (
                                        $areaId
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

                                'areaDepartamento' =>
                                    $this->buildAreaDepartmentLabel(
                                        $areaNombre,
                                        $equipo->departamento_nombre
                                            ?? null
                                    ),

                                'sede' =>
                                    $sedeNombre !== ''
                                        ? $sedeNombre
                                        : 'Sin sede',

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

        /*
        |--------------------------------------------------------------------------
        | Sedes
        |--------------------------------------------------------------------------
        */

        $sedes =
            DB::table('sedes')
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

        /*
        |--------------------------------------------------------------------------
        | Todas las áreas
        |--------------------------------------------------------------------------
        |
        | Ya no filtramos en PHP según una propiedad Livewire.
        | Mandamos la relación idSede para que Alpine filtre localmente.
        |
        */

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

        /*
        |--------------------------------------------------------------------------
        | Todos los departamentos
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Todas las ubicaciones
        |--------------------------------------------------------------------------
        */

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

                        /*
                         * Una ubicación puede ser general de la sede,
                         * por eso id_area puede ser NULL.
                         */
                        'idArea' =>
                            $ubicacion->id_area !== null
                                ? (string) $ubicacion->id_area
                                : '',
                    ]
                )
                ->values()
                ->all();

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
    | Área / departamento
    |--------------------------------------------------------------------------
    */

    private function buildAreaDepartmentLabel(
        ?string $area,
        ?string $departamento
    ): string {
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

        return 'Sin área / departamento';
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
            'areaDepartamentoActual',
            'sedeActual',
            'fechaAsignacionActual',
            'tipoAsignacionActual',
            'idAreaActual',
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
            'idSedeNueva',
            'idAreaNueva',
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
                'idSedeNueva' => '',
                'idAreaNueva' => '',
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
        /*
         * Una sola preparación de datos por render.
         *
         * En el siguiente paso Alpine conservará todo esto en memoria
         * y los cambios de selects ya no provocarán nuevos renders.
         */
        $reassignmentData =
            $this->getReassignmentFrontendData();

        /*
         * Compatibilidad con la vista que tienes actualmente.
         * Cuando cambiemos el Blade estas variables dejarán de ser
         * necesarias, pero por ahora evitamos romper la pantalla.
         */
        $equiposAsignados =
            collect(
                $reassignmentData['equipos']
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

        $sedes =
            $reassignmentData['sedes'];

        $areas =
            $this->idSedeNueva === ''
                ? []
                : collect(
                    $reassignmentData['areas']
                )
                    ->filter(
                        fn (array $area): bool =>
                            (string) $area['idSede']
                            ===
                            (string) $this->idSedeNueva
                    )
                    ->values()
                    ->all();

        $departamentos =
            $this->idAreaNueva === ''
                ? []
                : collect(
                    $reassignmentData['departamentos']
                )
                    ->filter(
                        fn (array $departamento): bool =>
                            (string) $departamento['idArea']
                            ===
                            (string) $this->idAreaNueva
                    )
                    ->values()
                    ->all();

        $ubicaciones =
            $this->idSedeNueva === ''
                ? []
                : collect(
                    $reassignmentData['ubicaciones']
                )
                    ->filter(
                        function (array $ubicacion): bool {
                            if (
                                (string) $ubicacion['idSede']
                                !==
                                (string) $this->idSedeNueva
                            ) {
                                return false;
                            }

                            /*
                             * Si todavía no hay área seleccionada,
                             * mostramos las ubicaciones de la sede.
                             */
                            if ($this->idAreaNueva === '') {
                                return true;
                            }

                            /*
                             * Ubicaciones generales de la sede
                             * o específicas del área seleccionada.
                             */
                            return
                                $ubicacion['idArea'] === ''
                                ||
                                (string) $ubicacion['idArea']
                                ===
                                (string) $this->idAreaNueva;
                        }
                    )
                    ->values()
                    ->all();

        return view(
            'livewire.reasignacion-create',
            [
                /*
                 * Nuevo JSON para Alpine.
                 */
                'reassignmentData' =>
                    $reassignmentData,

                /*
                 * Compatibilidad temporal.
                 */
                'equiposAsignados' =>
                    $equiposAsignados,

                'sedes' =>
                    $sedes,

                'areas' =>
                    $areas,

                'departamentos' =>
                    $departamentos,

                'ubicaciones' =>
                    $ubicaciones,
            ]
        );
    }
}