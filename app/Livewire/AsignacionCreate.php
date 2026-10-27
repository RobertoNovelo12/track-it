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

    public string $idUnidadAsignar = '';
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

    public function updatedIdUnidadAsignar(): void
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
        // Permiso por rol (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('gestionar');

        $payloadOriginal = $payload;

        $payload =
            $this->normalizePayload($payload);

        /*
         * Colaborador: usuario elegido (id) o nombre escrito sin cuenta
         * («texto:…»). Sin cuenta la asignación se guarda igual y aparece
         * en Seguridad y roles › Colaboradores sin cuenta.
         */
        $colaboradores = app(\App\Services\ColaboradoresService::class);

        $colaborador = $colaboradores->resolver(
            (string) ($payload['colaborador'] ?? ''),
            (string) ($payload['nombreColaborador'] ?? ''),
            (string) ($payload['numeroColaborador'] ?? '')
        );

        $payload['nombreColaborador'] = $colaborador['nombre'];

        $validated =
            Validator::make(
                $payload,
                [
                    'idEquipoAsignar' => [
                        'required',
                        'integer',
                        'exists:equipos,id_equipo',
                    ],

                    // Host editable (vacío = sin host).
                    'hostEquipo' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    // «Nuevo nombre»: opcional; vacío = conservar el actual.
                    'nuevoNombreEquipo' => [
                        'nullable',
                        'string',
                        'max:255',
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

                    'idUnidadAsignar' => [
                        'required',
                        'integer',
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
                        'Busca un usuario o escribe el nombre del colaborador.',

                    'idUnidadAsignar.required' =>
                        'Selecciona un hotel.',

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
        | Hotel -> Departamento -> Ubicación
        |--------------------------------------------------------------------------
        */

        $organizacion =
            app(\App\Services\UbicacionOrganizacionalService::class)
                ->validar(
                    $validated['idUnidadAsignar'],
                    $validated['idDepartamentoAsignar'],
                    $payload['idUbicacionAsignar'],
                    [
                        'unidad' => 'idUnidadAsignar',
                        'departamento' => 'idDepartamentoAsignar',
                        'ubicacion' => 'idUbicacionAsignar',
                    ]
                );

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
                    $payloadOriginal,
                    $validated,
                    $organizacion,
                    $fechaAsignacion,
                    $colaboradores,
                    $colaborador
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

                                    // nombre, número y cuenta (id_usuario)
                                    ...$colaboradores->columnasAsignacion($colaborador),

                                    /*
                                     * El área ya no se captura: la
                                     * organización es hotel + departamento.
                                     */

                                    'id_unidad' =>
                                        $organizacion['id_unidad'],

                                    'id_departamento' =>
                                        $organizacion['id_departamento'],

                                    'id_ubicacion' =>
                                        $organizacion['id_ubicacion'],

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

                    /*
                    |--------------------------------------------------------------------------
                    | Nuevo nombre del equipo (opcional)
                    |--------------------------------------------------------------------------
                    */

                    /*
                     * Host: editable en el formulario; se guarda si cambió.
                     * Sólo si llegó en el envío (formularios abiertos antes
                     * de este cambio no lo mandan y no deben borrarlo).
                     */
                    if (array_key_exists('hostEquipo', $payloadOriginal)) {
                        $host = trim((string) ($validated['hostEquipo'] ?? ''));
                        $host = $host === '' ? null : $host;

                        if ($host !== ($equipo->host !== null && $equipo->host !== '' ? (string) $equipo->host : null)) {
                            DB::table('equipos')
                                ->where('id_equipo', $equipoId)
                                ->update(['host' => $host]);

                            $equipo->host = $host;
                        }
                    }

                    $nuevoNombre = trim((string) ($validated['nuevoNombreEquipo'] ?? ''));

                    if (
                        $nuevoNombre !== ''
                        && $nuevoNombre !== (string) $equipo->nombre_equipo
                    ) {
                        DB::table('equipos')
                            ->where('id_equipo', $equipoId)
                            ->update(['nombre_equipo' => $nuevoNombre]);

                        $equipo->nombre_equipo = $nuevoNombre;
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

        $notificaciones = app(\App\Services\NotificacionService::class);

        $notificaciones->notificar(
            'asignaciones',
            'Equipo asignado',
            $notificaciones->nombreActor()
                . ' asignó el equipo '
                . $notificaciones->etiquetaEquipo((int) $resultado['equipoId'])
                . '.',
            route('equipos.show', (int) $resultado['equipoId'], false)
        );

        $this->dispatch('notificaciones-actualizadas');

        return [
            'ok' => true,

            'message' =>
                $colaborador['sin_cuenta']
                    ? 'El equipo fue asignado. «' . $colaborador['nombre'] . '» no tiene cuenta: un administrador deberá crearla en Seguridad y roles.'
                    : 'El equipo fue asignado correctamente.',

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
    | Hotel -> Departamento
    | Hotel/Departamento -> Ubicación
    |
    | se resolverá en navegador sin requests Livewire.
    |
    */

    private function getAssignmentFrontendData(): array
    {
        $equipos =
            $this->getEquiposDisponibles();

        $organizacion =
            app(\App\Services\UbicacionOrganizacionalService::class)
                ->datosFormulario();

        $tiposAsignacion =
            $this->getTiposAsignacion();

        return [
            'equipos' =>
                $equipos,

            'unidades' =>
                $organizacion['unidades'],

            'departamentos' =>
                $organizacion['departamentos'],

            'ubicaciones' =>
                $organizacion['ubicaciones'],

            'tiposAsignacion' =>
                $tiposAsignacion,
            // Selector de colaborador (cuentas existentes).
            'usuarios' =>
                app(\App\Services\ColaboradoresService::class)->opcionesUsuarios(),
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

                    /*
                     * En la lista se muestra el nombre del equipo (hay
                     * muchos modelos iguales); sin nombre, tipo/marca/modelo.
                     */
                    $nombre = trim((string) ($equipo->nombre_equipo ?? ''));

                    if ($nombre !== '') {
                        $descripcion = $nombre;
                    } elseif ($descripcion === '') {
                        $descripcion = 'Equipo';
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

                'nuevoNombreEquipo' =>
                    '',

                'colaborador' =>
                    '',

                'nombreColaborador' =>
                    '',

                'numeroColaborador' =>
                    '',

                'idUnidadAsignar' =>
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
        return view(
            'livewire.asignacion-create',
            [
                'assignmentData' =>
                    $this->getAssignmentFrontendData(),
            ]
        );
    }
}
