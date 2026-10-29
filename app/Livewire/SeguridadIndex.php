<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SeguridadIndex extends Component
{
    // Registros por página de Ajustes › Preferencias.
    use \App\Livewire\Concerns\UsaRegistrosPorPagina;

    use WithPagination;

    /*
    |--------------------------------------------------------------------------
    | Filtros y paginación
    |--------------------------------------------------------------------------
    */

    #[Url(as: 'buscar')]
    public string $search = '';

    #[Url(as: 'estado')]
    public string $estado = '';

    public int $perPage = 10;

    /*
    |--------------------------------------------------------------------------
    | Aprobación de usuarios
    |--------------------------------------------------------------------------
    */

    public bool $approvalModalOpen = false;
    public ?int $approvalUserId = null;
    public ?int $approvalRoleId = null;
    public string $approvalUserName = '';
    public string $approvalUserEmail = '';

    /*
    |--------------------------------------------------------------------------
    | Solicitudes de cambio organizacional
    |--------------------------------------------------------------------------
    */

    public bool $organizationRequestModalOpen = false;
    public ?int $organizationRequestId = null;
    public string $organizationRequestUserName = '';
    public string $organizationRequestField = '';
    public string $organizationRequestFieldLabel = '';
    public ?string $organizationRequestCurrentValue = null;
    public ?string $organizationRequestRequestedValue = null;
    public ?string $organizationRequestReason = null;
    public ?int $organizationRequestCatalogId = null;
    public string $organizationRequestReviewComment = '';

    /*
    |--------------------------------------------------------------------------
    | Paginación
    |--------------------------------------------------------------------------
    */

    public function updatingSearch(): void
    {
        $this->resetPage('usuariosPage');
    }

    public function updatingEstado(): void
    {
        $this->resetPage('usuariosPage');
    }

    public function updatingPerPage(): void
    {
        $this->resetPage('usuariosPage');
    }

    public function buscar(): void
    {
        $this->resetPage('usuariosPage');
    }

    /*
    |--------------------------------------------------------------------------
    | Aprobación de usuarios
    |--------------------------------------------------------------------------
    */

    public function openApprovalModal(int $userId): void
    {
        // Permiso por rol (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        $this->resetValidation();

        $usuario = DB::table('usuarios as u')
            ->join(
                'estados_usuario as eu',
                'eu.id_estado_usuario',
                '=',
                'u.id_estado_usuario'
            )
            ->where('u.id_usuario', $userId)
            ->where('eu.clave', 'PENDIENTE')
            ->select([
                'u.id_usuario',
                'u.nombres',
                'u.apellido_paterno',
                'u.apellido_materno',
                'u.correo',
            ])
            ->first();

        if (! $usuario) {
            return;
        }

        $this->approvalUserId = (int) $usuario->id_usuario;
        $this->approvalRoleId = null;
        $this->approvalUserName = trim(
            implode(' ', array_filter([
                $usuario->nombres,
                $usuario->apellido_paterno,
                $usuario->apellido_materno,
            ]))
        );
        $this->approvalUserEmail = $usuario->correo;
        $this->approvalModalOpen = true;
    }

    public function closeApprovalModal(): void
    {
        $this->resetValidation();

        $this->approvalModalOpen = false;
        $this->approvalUserId = null;
        $this->approvalRoleId = null;
        $this->approvalUserName = '';
        $this->approvalUserEmail = '';
    }

    public function approveUser(): void
    {
        // Permiso por rol (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        $this->validate([
            'approvalUserId' => [
                'required',
                'integer',
            ],
            'approvalRoleId' => [
                'required',
                'integer',
            ],
        ], [
            'approvalRoleId.required' =>
                'Selecciona un rol antes de aprobar al usuario.',
        ]);

        $rol = DB::table('roles')
            ->where('id_rol', $this->approvalRoleId)
            ->where('activo', true)
            ->first([
                'id_rol',
                'nombre',
            ]);

        if (! $rol) {
            $this->addError(
                'approvalRoleId',
                'El rol seleccionado no está disponible.'
            );

            return;
        }

        $idEstadoActivo = DB::table('estados_usuario')
            ->where('clave', 'ACTIVO')
            ->where('activo', true)
            ->value('id_estado_usuario');

        if (! $idEstadoActivo) {
            $this->addError(
                'approvalRoleId',
                'No se encontró el estado ACTIVO en el sistema.'
            );

            return;
        }

        $aprobado = DB::transaction(function () use ($rol, $idEstadoActivo) {
            $usuario = DB::table('usuarios as u')
                ->join(
                    'estados_usuario as eu',
                    'eu.id_estado_usuario',
                    '=',
                    'u.id_estado_usuario'
                )
                ->where('u.id_usuario', $this->approvalUserId)
                ->where('eu.clave', 'PENDIENTE')
                ->select([
                    'u.id_usuario',
                    'u.nombres',
                    'u.apellido_paterno',
                    'u.correo',
                ])
                ->lockForUpdate()
                ->first();

            if (! $usuario) {
                return false;
            }

            DB::table('usuarios')
                ->where('id_usuario', $usuario->id_usuario)
                ->update([
                    'id_rol' => $rol->id_rol,
                    'id_estado_usuario' => $idEstadoActivo,
                ]);

            DB::table('bitacora_auditoria')
                ->insert([
                    'id_usuario' => auth()->id(),
                    'accion' => 'USUARIO_APROBADO',
                    'modulo' => 'SEGURIDAD',
                    'descripcion' =>
                        'Se aprobó al usuario '
                        . $usuario->nombres
                        . ' '
                        . $usuario->apellido_paterno
                        . ' ('
                        . $usuario->correo
                        . ') con el rol '
                        . $rol->nombre
                        . '.',
                    'fecha_hora' => now(),
                ]);

            return true;
        });

        if (! $aprobado) {
            $this->addError(
                'approvalRoleId',
                'Este usuario ya no se encuentra pendiente de aprobación.'
            );

            return;
        }

        app(\App\Services\NotificacionService::class)->notificar(
            'usuarios',
            'Tu cuenta fue aprobada',
            'Tu acceso a Track-IT fue aprobado. Ya puedes usar el sistema con tu rol asignado.',
            route('ajustes.index', [], false),
            [(int) $this->approvalUserId],
            incluirActor: true
        );

        $this->dispatch('notificaciones-actualizadas');

        $this->resetPage('usuariosPage');
        $this->closeApprovalModal();

        $this->dispatch(
            'usuario-aprobado',
            message: 'Usuario aprobado correctamente.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Solicitudes de cambio organizacional
    |--------------------------------------------------------------------------
    */

    public function openOrganizationRequestModal(int $requestId): void
    {
        // Permiso por rol (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        $this->resetValidation();

        $solicitud = DB::table('solicitudes_cambio_organizacion as s')
            ->join(
                'usuarios as u',
                'u.id_usuario',
                '=',
                's.id_usuario'
            )
            ->where('s.id_solicitud', $requestId)
            ->where('s.estado', 'pendiente')
            ->select([
                's.id_solicitud',
                's.campo',
                's.valor_actual',
                's.valor_solicitado',
                's.motivo',
                's.fecha_solicitud',
                'u.id_usuario',
                'u.nombres',
                'u.apellido_paterno',
                'u.apellido_materno',
                'u.correo',
            ])
            ->first();

        if (! $solicitud) {
            return;
        }

        $this->organizationRequestId = (int) $solicitud->id_solicitud;
        $this->organizationRequestUserName = trim(
            implode(' ', array_filter([
                $solicitud->nombres,
                $solicitud->apellido_paterno,
                $solicitud->apellido_materno,
            ]))
        );
        $this->organizationRequestField = $solicitud->campo;
        $this->organizationRequestFieldLabel =
            $this->organizationFieldLabel($solicitud->campo);
        $this->organizationRequestCurrentValue = $solicitud->valor_actual;
        $this->organizationRequestRequestedValue = $solicitud->valor_solicitado;
        $this->organizationRequestReason = $solicitud->motivo;
        $this->organizationRequestCatalogId =
            $this->resolveOrganizationCatalogId(
                $solicitud->campo,
                $solicitud->valor_solicitado
            );
        $this->organizationRequestReviewComment = '';
        $this->organizationRequestModalOpen = true;
    }

    public function closeOrganizationRequestModal(): void
    {
        $this->resetValidation();

        $this->organizationRequestModalOpen = false;
        $this->organizationRequestId = null;
        $this->organizationRequestUserName = '';
        $this->organizationRequestField = '';
        $this->organizationRequestFieldLabel = '';
        $this->organizationRequestCurrentValue = null;
        $this->organizationRequestRequestedValue = null;
        $this->organizationRequestReason = null;
        $this->organizationRequestCatalogId = null;
        $this->organizationRequestReviewComment = '';
    }

    public function approveOrganizationRequest(): void
    {
        // Permiso por rol (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        $this->validate([
            'organizationRequestId' => [
                'required',
                'integer',
            ],
            'organizationRequestReviewComment' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $resultado = DB::transaction(function () {
            $solicitud = DB::table('solicitudes_cambio_organizacion')
                ->where('id_solicitud', $this->organizationRequestId)
                ->where('estado', 'pendiente')
                ->lockForUpdate()
                ->first();

            if (! $solicitud) {
                return [
                    'ok' => false,
                    'message' =>
                        'La solicitud ya no se encuentra pendiente.',
                ];
            }

            $usuario = DB::table('usuarios')
                ->where('id_usuario', $solicitud->id_usuario)
                ->first([
                    'id_usuario',
                    'nombres',
                    'apellido_paterno',
                    'numero_colaborador',
                    'puesto',
                    'id_rol',
                    'id_unidad',
                    'id_departamento',
                ]);

            if (! $usuario) {
                return [
                    'ok' => false,
                    'message' =>
                        'El usuario asociado a la solicitud ya no existe.',
                ];
            }

            $valorAplicado = trim(
                (string) $solicitud->valor_solicitado
            );

            $datosActualizar = [];

            if ($solicitud->campo === 'numero_colaborador') {
                $duplicado = DB::table('usuarios')
                    ->where('numero_colaborador', $valorAplicado)
                    ->where('id_usuario', '<>', $usuario->id_usuario)
                    ->exists();

                if ($duplicado) {
                    return [
                        'ok' => false,
                        'message' =>
                            'El número de colaborador solicitado ya está asignado a otro usuario.',
                    ];
                }

                $datosActualizar = [
                    'numero_colaborador' => $valorAplicado,
                ];
            } elseif ($solicitud->campo === 'puesto') {
                $datosActualizar = [
                    'puesto' => $valorAplicado,
                ];
            } elseif ($solicitud->campo === 'rol') {
                if (! $this->organizationRequestCatalogId) {
                    return [
                        'ok' => false,
                        'message' =>
                            'Selecciona el rol que se asignará al usuario.',
                    ];
                }

                $rol = DB::table('roles')
                    ->where('id_rol', $this->organizationRequestCatalogId)
                    ->where('activo', true)
                    ->first([
                        'id_rol',
                        'nombre',
                    ]);

                if (! $rol) {
                    return [
                        'ok' => false,
                        'message' =>
                            'El rol seleccionado no está disponible.',
                    ];
                }

                $datosActualizar = [
                    'id_rol' => $rol->id_rol,
                ];
                $valorAplicado = $rol->nombre;
            } elseif ($solicitud->campo === 'hotel') {
                if (! $this->organizationRequestCatalogId) {
                    return [
                        'ok' => false,
                        'message' =>
                            'Selecciona el hotel que se asignará al usuario.',
                    ];
                }

                $unidad = DB::table('unidades_organizativas')
                    ->where('id_unidad', $this->organizationRequestCatalogId)
                    ->where('activo', true)
                    ->first([
                        'id_unidad',
                        'nombre',
                    ]);

                if (! $unidad) {
                    return [
                        'ok' => false,
                        'message' =>
                            'El hotel seleccionado no está disponible.',
                    ];
                }

                $datosActualizar = [
                    'id_unidad' => $unidad->id_unidad,
                ];

                // El departamento se conserva solo si atiende el nuevo hotel.
                if ($usuario->id_departamento) {
                    $mantenerDepartamento = \App\Models\Departamento::query()
                        ->disponiblesParaUnidad((int) $unidad->id_unidad)
                        ->where('departamentos.id_departamento', $usuario->id_departamento)
                        ->exists();

                    if (! $mantenerDepartamento) {
                        $datosActualizar['id_departamento'] = null;
                    }
                }

                $valorAplicado = $unidad->nombre;
            } elseif ($solicitud->campo === 'departamento') {
                if (! $this->organizationRequestCatalogId) {
                    return [
                        'ok' => false,
                        'message' =>
                            'Selecciona el departamento que se asignará al usuario.',
                    ];
                }

                $departamento = \App\Models\Departamento::disponiblesParaUnidad((int) $usuario->id_unidad)
                    ->where(
                        'id_departamento',
                        $this->organizationRequestCatalogId
                    )
                    ->where('activo', true)
                    ->first([
                        'id_departamento',
                        'nombre',
                    ]);

                if (! $departamento) {
                    return [
                        'ok' => false,
                        'message' =>
                            'El departamento seleccionado no está disponible.',
                    ];
                }

                $datosActualizar = [
                    'id_departamento' => $departamento->id_departamento,
                ];
                $valorAplicado = $departamento->nombre;
            } else {
                return [
                    'ok' => false,
                    'message' =>
                        'El campo solicitado no puede ser modificado.',
                ];
            }

            DB::table('usuarios')
                ->where('id_usuario', $usuario->id_usuario)
                ->update($datosActualizar);

            DB::table('solicitudes_cambio_organizacion')
                ->where('id_solicitud', $solicitud->id_solicitud)
                ->update([
                    'estado' => 'aprobada',
                    'revisado_por' => auth()->id(),
                    'comentario_revision' =>
                        filled($this->organizationRequestReviewComment)
                            ? trim($this->organizationRequestReviewComment)
                            : null,
                    'fecha_revision' => now(),
                ]);

            DB::table('bitacora_auditoria')
                ->insert([
                    'id_usuario' => auth()->id(),
                    'accion' =>
                        'SOLICITUD_CAMBIO_ORGANIZACION_APROBADA',
                    'modulo' => 'SEGURIDAD',
                    'descripcion' =>
                        'Se aprobó la solicitud de '
                        . $usuario->nombres
                        . ' '
                        . $usuario->apellido_paterno
                        . ' para actualizar '
                        . $solicitud->campo
                        . ' de "'
                        . ($solicitud->valor_actual ?: 'Sin asignar')
                        . '" a "'
                        . $valorAplicado
                        . '".',
                    'fecha_hora' => now(),
                ]);

            return [
                'ok' => true,
            ];
        });

        if (! $resultado['ok']) {
            $this->addError(
                'organizationRequestDecision',
                $resultado['message']
            );

            return;
        }

        app(\App\Services\NotificacionService::class)->notificar(
            'usuarios',
            'Solicitud aprobada',
            'Tu solicitud de cambio organizacional fue aprobada y tu información ya se actualizó.',
            route('ajustes.index', [], false),
            [(int) DB::table('solicitudes_cambio_organizacion')
                ->where('id_solicitud', $this->organizationRequestId)
                ->value('id_usuario')],
            incluirActor: true
        );

        $this->dispatch('notificaciones-actualizadas');

        $this->closeOrganizationRequestModal();

        $this->dispatch(
            'solicitud-organizacion-aprobada',
            message:
                'La solicitud fue aprobada y la información del usuario se actualizó correctamente.'
        );
    }

    public function rejectOrganizationRequest(): void
    {
        // Permiso por rol (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        $this->validate([
            'organizationRequestId' => [
                'required',
                'integer',
            ],
            'organizationRequestReviewComment' => [
                'required',
                'string',
                'max:1000',
            ],
        ], [
            'organizationRequestReviewComment.required' =>
                'Indica el motivo por el que se rechaza la solicitud.',
        ]);

        $rechazada = DB::transaction(function () {
            $solicitud = DB::table('solicitudes_cambio_organizacion')
                ->where('id_solicitud', $this->organizationRequestId)
                ->where('estado', 'pendiente')
                ->lockForUpdate()
                ->first();

            if (! $solicitud) {
                return false;
            }

            DB::table('solicitudes_cambio_organizacion')
                ->where('id_solicitud', $solicitud->id_solicitud)
                ->update([
                    'estado' => 'rechazada',
                    'revisado_por' => auth()->id(),
                    'comentario_revision' =>
                        trim($this->organizationRequestReviewComment),
                    'fecha_revision' => now(),
                ]);

            DB::table('bitacora_auditoria')
                ->insert([
                    'id_usuario' => auth()->id(),
                    'accion' =>
                        'SOLICITUD_CAMBIO_ORGANIZACION_RECHAZADA',
                    'modulo' => 'SEGURIDAD',
                    'descripcion' =>
                        'Se rechazó la solicitud de cambio organizacional #'
                        . $solicitud->id_solicitud
                        . '.',
                    'fecha_hora' => now(),
                ]);

            return true;
        });

        if (! $rechazada) {
            $this->addError(
                'organizationRequestDecision',
                'La solicitud ya no se encuentra pendiente.'
            );

            return;
        }

        app(\App\Services\NotificacionService::class)->notificar(
            'usuarios',
            'Solicitud rechazada',
            'Tu solicitud de cambio organizacional fue rechazada. Motivo: '
                . trim($this->organizationRequestReviewComment),
            route('ajustes.index', [], false),
            [(int) DB::table('solicitudes_cambio_organizacion')
                ->where('id_solicitud', $this->organizationRequestId)
                ->value('id_usuario')],
            incluirActor: true
        );

        $this->dispatch('notificaciones-actualizadas');

        $this->closeOrganizationRequestModal();

        $this->dispatch(
            'solicitud-organizacion-rechazada',
            message: 'La solicitud fue rechazada correctamente.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Placeholder
    |--------------------------------------------------------------------------
    */

    public function placeholder(): View
    {
        return view('livewire.placeholders.seguridad-index');
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    /**
     * Paginación con un solo viaje a la base de datos: el total viene en
     * cada fila (COUNT(*) OVER()). Si la página quedó vacía (p. ej. tras
     * filtrar), se usa el paginador normal, que corrige la página.
     */
    protected function paginarUsuarios($consulta): \Illuminate\Pagination\LengthAwarePaginator
    {
        $porPagina = max(1, (int) $this->perPage);
        $pagina = max(1, (int) \Illuminate\Pagination\Paginator::resolveCurrentPage('usuariosPage'));

        $filas = (clone $consulta)
            ->offset(($pagina - 1) * $porPagina)
            ->limit($porPagina)
            ->get();

        if ($filas->isEmpty() && $pagina > 1) {
            return $consulta->paginate($porPagina, ['*'], 'usuariosPage');
        }

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $filas,
            (int) ($filas->first()->total_resultados ?? 0),
            $porPagina,
            $pagina,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => 'usuariosPage',
            ]
        );
    }

    public function render(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Indicadores + alertas basadas en usuarios
        |--------------------------------------------------------------------------
        |
        | Antes se consultaba usuarios tres veces: indicadores, usuarios sin rol
        | e inactividad de 90 días. Ahora los tres cálculos salen de una sola
        | consulta agregada.
        |
        */

        $limiteInactividad = now()->subDays(90);

        /*
         * Rendimiento: la base de datos está lejos (cada viaje cuesta
         * ~150-300 ms), así que indicadores, solicitudes de cambio,
         * bitácora y resumen semanal salen de UNA sola consulta (antes
         * eran 5-6 viajes en cada render: buscar, paginar, abrir...).
         */
        $inicioSemana = now()->startOfDay()->subDays(6);

        $panel = DB::selectOne(
            <<<'SQL'
            SELECT
                (
                    SELECT row_to_json(st) FROM (
                        SELECT
                            COUNT(*) AS total,
                            COUNT(*) FILTER (WHERE eu.clave = 'ACTIVO') AS activos,
                            COUNT(*) FILTER (WHERE eu.clave = 'PENDIENTE') AS pendientes,
                            COUNT(*) FILTER (WHERE eu.clave IN ('INACTIVO', 'BAJA')) AS inactivos,
                            COUNT(*) FILTER (
                                WHERE DATE_TRUNC('month', u.fecha_registro) = DATE_TRUNC('month', CURRENT_DATE)
                            ) AS registrados_mes,
                            COUNT(*) FILTER (
                                WHERE u.id_rol IS NULL AND eu.clave IN ('PENDIENTE', 'ACTIVO')
                            ) AS usuarios_sin_rol,
                            COUNT(*) FILTER (
                                WHERE eu.clave = 'ACTIVO' AND COALESCE(u.ultimo_acceso, u.fecha_registro) < ?
                            ) AS usuarios_inactivos_90
                        FROM usuarios u
                        JOIN estados_usuario eu ON eu.id_estado_usuario = u.id_estado_usuario
                    ) st
                ) AS stats,
                (
                    SELECT COALESCE(json_agg(sc ORDER BY sc.orden), '[]'::json) FROM (
                        SELECT
                            s.id_solicitud, s.campo, s.valor_actual, s.valor_solicitado, s.motivo,
                            s.fecha_solicitud::text AS fecha_solicitud,
                            s.fecha_solicitud AS orden,
                            u.id_usuario, u.username, u.correo,
                            TRIM(CONCAT_WS(' ', u.nombres, u.apellido_paterno, u.apellido_materno)) AS usuario_nombre
                        FROM solicitudes_cambio_organizacion s
                        JOIN usuarios u ON u.id_usuario = s.id_usuario
                        WHERE s.estado = 'pendiente'
                    ) sc
                ) AS solicitudes,
                (
                    SELECT COALESCE(json_agg(au ORDER BY au.orden DESC), '[]'::json) FROM (
                        SELECT
                            b.id_bitacora, b.accion, b.modulo, b.descripcion,
                            b.fecha_hora::text AS fecha_hora,
                            b.fecha_hora AS orden,
                            u.id_usuario, u.username,
                            TRIM(CONCAT_WS(' ', u.nombres, u.apellido_paterno)) AS usuario_nombre
                        FROM bitacora_auditoria b
                        LEFT JOIN usuarios u ON u.id_usuario = b.id_usuario
                        ORDER BY b.fecha_hora DESC
                        LIMIT 8
                    ) au
                ) AS auditoria,
                (
                    SELECT COALESCE(json_object_agg(ac.fecha, ac.total), '{}'::json) FROM (
                        SELECT DATE(fecha_hora)::text AS fecha, COUNT(*) AS total
                        FROM bitacora_auditoria
                        WHERE fecha_hora >= ?
                        GROUP BY DATE(fecha_hora)
                    ) ac
                ) AS actividad_semana,
                (
                    SELECT COALESCE(json_object_agg(rg.fecha, rg.total), '{}'::json) FROM (
                        SELECT DATE(fecha_registro)::text AS fecha, COUNT(*) AS total
                        FROM usuarios
                        WHERE fecha_registro >= ?
                        GROUP BY DATE(fecha_registro)
                    ) rg
                ) AS registros_semana
            SQL,
            [$limiteInactividad, $inicioSemana, $inicioSemana]
        );

        $statsRow = (object) (json_decode((string) ($panel->stats ?? '{}'), true) ?: []);

        $aObjetos = fn (?string $json) => collect(json_decode((string) $json, true) ?: [])
            ->map(function (array $fila): object {
                unset($fila['orden']);

                return (object) $fila;
            })
            ->values();

        $solicitudesCambio = $aObjetos($panel->solicitudes ?? '[]');
        $auditoria = $aObjetos($panel->auditoria ?? '[]');

        $actividadRaw = json_decode((string) ($panel->actividad_semana ?? '{}'), true) ?: [];
        $registrosRaw = json_decode((string) ($panel->registros_semana ?? '{}'), true) ?: [];

        // La vista usa ->max() sobre el resumen.
        $resumenSemanal = collect(range(6, 0))
            ->map(function (int $dias) use ($actividadRaw, $registrosRaw): array {
                $fecha = now()->startOfDay()->subDays($dias);
                $key = $fecha->toDateString();

                return [
                    'fecha' => $key,
                    'label' => $fecha->format('d/m'),
                    'actividad' => (int) ($actividadRaw[$key] ?? 0),
                    'registros' => (int) ($registrosRaw[$key] ?? 0),
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Catálogos de apoyo
        |--------------------------------------------------------------------------
        |
        | Estos catálogos cambian con poca frecuencia, pero antes se consultaban
        | de nuevo en cada render de Livewire. Se mantienen 30 segundos en caché
        | para evitar cuatro consultas repetidas durante búsqueda/paginación.
        |
        */

        $catalogos = Cache::remember(
            'seguridad.catalogos-activos.v2',
            // Roles, áreas, departamentos y estados cambian poco: 5 minutos.
            now()->addMinutes(5),
            function (): array {
                /*
                 * IMPORTANTE:
                 * Guardamos únicamente arrays escalares en caché.
                 *
                 * No almacenamos instancias de Collection porque algunos
                 * drivers de caché serializan el objeto y pueden devolver
                 * __PHP_Incomplete_Class durante peticiones Livewire.
                 */
                $roles = DB::table('roles as r')
                    ->where('r.activo', true)
                    ->orderBy('r.nombre')
                    ->select([
                        'r.id_rol',
                        'r.nombre',
                        'r.descripcion',
                    ])
                    ->selectRaw(
                        "
                        CASE
                            WHEN NOT EXISTS (
                                SELECT 1
                                FROM roles_permisos AS rp
                                WHERE rp.id_rol = r.id_rol
                            ) THEN 1
                            ELSE 0
                        END AS sin_permisos
                        "
                    )
                    ->get()
                    ->map(fn ($row): array => (array) $row)
                    ->all();


                $departamentos = DB::table('departamentos')
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get([
                        'id_departamento',
                        'nombre',
                    ])
                    ->map(fn ($row): array => (array) $row)
                    ->all();

                $estados = DB::table('estados_usuario')
                    ->where('activo', true)
                    ->orderBy('orden')
                    ->orderBy('nombre')
                    ->get([
                        'id_estado_usuario',
                        'clave',
                        'nombre',
                    ])
                    ->map(fn ($row): array => (array) $row)
                    ->all();

                return [
                    'roles' => $roles,
                    'departamentos' => $departamentos,
                    'estados' => $estados,
                ];
            }
        );

        /*
         * La vista ya trabaja con objetos/Collections.
         * Los reconstruimos después de leer el caché.
         */
        $roles = collect($catalogos['roles'] ?? [])
            ->map(fn (array $row): object => (object) $row);


        $departamentos = collect($catalogos['departamentos'] ?? [])
            ->map(fn (array $row): object => (object) $row);

        $estados = collect($catalogos['estados'] ?? [])
            ->map(fn (array $row): object => (object) $row);

        $rolesConfigurados = $roles->count();

        $rolesSinPermisos = $roles
            ->filter(
                fn ($rol): bool =>
                    (int) ($rol->sin_permisos ?? 0) === 1
            )
            ->count();

        $stats = [
            'total' => (int) ($statsRow->total ?? 0),
            'activos' => (int) ($statsRow->activos ?? 0),
            'pendientes' => (int) ($statsRow->pendientes ?? 0),
            'inactivos' => (int) ($statsRow->inactivos ?? 0),
            'registrados_mes' => (int) ($statsRow->registrados_mes ?? 0),
            'roles' => (int) $rolesConfigurados,
        ];

        $usuariosSinRol =
            (int) ($statsRow->usuarios_sin_rol ?? 0);

        $usuariosInactivos90 =
            (int) ($statsRow->usuarios_inactivos_90 ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Usuarios
        |--------------------------------------------------------------------------
        */

        $usuariosQuery = DB::table('usuarios as u')
            ->join(
                'estados_usuario as eu',
                'eu.id_estado_usuario',
                '=',
                'u.id_estado_usuario'
            )
            ->leftJoin(
                'roles as r',
                'r.id_rol',
                '=',
                'u.id_rol'
            )
            ->leftJoin(
                'departamentos as d',
                'd.id_departamento',
                '=',
                'u.id_departamento'
            )
            ->leftJoin(
                'unidades_organizativas as uo',
                'uo.id_unidad',
                '=',
                'u.id_unidad'
            )
            ->select([
                'u.id_usuario',
                'u.nombres',
                'u.apellido_paterno',
                'u.apellido_materno',
                'u.username',
                'u.correo',
                'u.numero_colaborador',
                'u.puesto',
                'u.id_rol',
                'u.fecha_registro',
                'u.ultimo_acceso',
                'r.nombre as rol_nombre',
                'eu.clave as estado_clave',
                'eu.nombre as estado_nombre',
                'uo.nombre as hotel_nombre',
                'd.nombre as departamento_nombre',
                'uo.nombre as unidad_nombre',
            ])
            /*
             * En la misma consulta: total de resultados (sin un COUNT
             * aparte del paginador), solicitud vigente de restablecimiento
             * y equipos asignados (modal de detalles).
             */
            ->selectRaw('COUNT(*) OVER() AS total_resultados')
            ->selectRaw(
                app(\App\Services\ColaboradoresService::class)->disponible()
                    ? '(SELECT COUNT(DISTINCT ax.id_equipo) FROM asignaciones ax WHERE ax.id_usuario = u.id_usuario AND ax.fecha_fin IS NULL) AS equipos_asignados'
                    : '0 AS equipos_asignados'
            );

        $search = trim($this->search);

        if ($search !== '') {
            $like = '%' . mb_strtolower($search) . '%';

            $usuariosQuery->where(function ($query) use ($like) {
                $query
                    ->whereRaw(
                        'LOWER(u.nombres) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(u.apellido_paterno) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE(u.apellido_materno, \'\')) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(u.username) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(u.correo) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE(u.numero_colaborador, \'\')) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE(u.puesto, \'\')) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE(r.nombre, \'\')) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE(a.nombre, \'\')) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE(d.nombre, \'\')) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        "
                        LOWER(
                            CONCAT_WS(
                                ' ',
                                u.nombres,
                                u.apellido_paterno,
                                u.apellido_materno
                            )
                        ) LIKE ?
                        ",
                        [$like]
                    );
            });
        }

        if ($this->estado !== '') {
            $usuariosQuery->where('eu.clave', $this->estado);
        }

        $usuariosQuery
            ->orderByRaw(
                "
                CASE eu.clave
                    WHEN 'PENDIENTE' THEN 0
                    WHEN 'ACTIVO' THEN 1
                    WHEN 'INACTIVO' THEN 2
                    WHEN 'BAJA' THEN 3
                    ELSE 4
                END
                "
            )
            ->orderByDesc('u.fecha_registro');

        $usuarios = $this->paginarUsuarios($usuariosQuery);

        /*
        |--------------------------------------------------------------------------
        | Solicitudes de cambio organizacional pendientes
        |--------------------------------------------------------------------------
        */

        // Solicitudes de cambio: vienen en la consulta combinada ($panel).

        $solicitudesCambioPendientes =
            $solicitudesCambio->count();

        /*
        |--------------------------------------------------------------------------
        | Bitácora de auditoría
        |--------------------------------------------------------------------------
        */

        // Bitácora: viene en la consulta combinada ($panel).

        /*
        |--------------------------------------------------------------------------
        | Resumen de los últimos 7 días
        |--------------------------------------------------------------------------
        |
        | El resumen no necesita recalcularse en cada pulsación de búsqueda.
        | Se conserva brevemente en caché para reducir dos agregaciones por render.
        |
        */

        // Resumen semanal: viene en la consulta combinada ($panel).

        /*
        |--------------------------------------------------------------------------
        | Alertas de seguridad
        |--------------------------------------------------------------------------
        */

        $alertas = collect();

        if ($solicitudesCambioPendientes > 0) {
            $alertas->push([
                'type' => 'warning',
                'title' =>
                    $solicitudesCambioPendientes
                    . ' solicitud'
                    . ($solicitudesCambioPendientes === 1 ? '' : 'es')
                    . ' de actualización pendiente'
                    . ($solicitudesCambioPendientes === 1 ? '' : 's'),
            ]);
        }

        if ($stats['pendientes'] > 0) {
            $alertas->push([
                'type' => 'warning',
                'title' =>
                    $stats['pendientes']
                    . ' usuario'
                    . ($stats['pendientes'] === 1 ? '' : 's')
                    . ' pendiente'
                    . ($stats['pendientes'] === 1 ? '' : 's')
                    . ' de aprobación',
            ]);
        }

        if ($usuariosSinRol > 0) {
            $alertas->push([
                'type' => 'warning',
                'title' =>
                    $usuariosSinRol
                    . ' usuario'
                    . ($usuariosSinRol === 1 ? '' : 's')
                    . ' sin rol asignado',
            ]);
        }

        if ($usuariosInactivos90 > 0) {
            $alertas->push([
                'type' => 'danger',
                'title' =>
                    $usuariosInactivos90
                    . ' usuario'
                    . ($usuariosInactivos90 === 1 ? '' : 's')
                    . ' sin actividad por más de 90 días',
            ]);
        }

        if ($rolesSinPermisos > 0) {
            $alertas->push([
                'type' => 'info',
                'title' =>
                    $rolesSinPermisos
                    . ' rol'
                    . ($rolesSinPermisos === 1 ? '' : 'es')
                    . ' sin permisos configurados',
            ]);
        }

        if ($alertas->isEmpty()) {
            $alertas->push([
                'type' => 'success',
                'title' =>
                    'No hay alertas administrativas pendientes',
            ]);
        }

        $colaboradores = app(\App\Services\ColaboradoresService::class);

        return view(
            'livewire.seguridad-index',
            [
                'stats' => $stats,
                'usuarios' => $usuarios,
                'estados' => $estados,
                'roles' => $roles,
                'departamentos' => $departamentos,
                'solicitudesCambio' => $solicitudesCambio,
                'solicitudesCambioPendientes' =>
                    $solicitudesCambioPendientes,
                'auditoria' => $auditoria,
                'resumenSemanal' => $resumenSemanal,
                'alertas' => $alertas,

                // Equipos asignados a colaboradores sin cuenta.
                'colaboradoresSinCuenta' => $colaboradores->pendientes(),
                'opcionesCuentas' => $colaboradores->opcionesUsuarios(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Colaboradores sin cuenta: vincular con una cuenta existente
    |--------------------------------------------------------------------------
    |
    | Para cuando el colaborador ya tiene cuenta con otro nombre (p. ej.
    | «Beto Novelo» → Roberto Novelo Poot). Sus asignaciones pasan a la
    | cuenta y toman su nombre y número.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Restablecer contraseña (contraseña temporal)
    |--------------------------------------------------------------------------
    |
    | Solo con una solicitud vigente: el usuario respondió sus preguntas de
    | seguridad en «¿Olvidaste tu contraseña?». Devuelve la contraseña
    | temporal para mostrarla UNA vez al administrador; al entrar con ella
    | el usuario debe elegir una nueva.
    |
    */

    public function restablecerContrasena(mixed $idUsuario): array
    {
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        $idUsuario = is_numeric($idUsuario) ? (int) $idUsuario : 0;
        $servicio = app(\App\Services\RestablecimientoContrasenaService::class);

        if ($idUsuario <= 0 || $idUsuario === (int) auth()->id()) {
            return [
                'ok' => false,
                'mensaje' => 'Para cambiar tu propia contraseña usa Ajustes › Seguridad.',
            ];
        }

        if (! $servicio->solicitudVigente($idUsuario)) {
            return [
                'ok' => false,
                'mensaje' => 'El usuario no tiene una solicitud vigente. Debe responder sus preguntas de seguridad en «¿Olvidaste tu contraseña?».',
            ];
        }

        $usuario = DB::table('usuarios')
            ->where('id_usuario', $idUsuario)
            ->first(['nombres', 'apellido_paterno', 'username']);

        if (! $usuario) {
            return ['ok' => false, 'mensaje' => 'No se encontró el usuario.'];
        }

        $temporal = $servicio->restablecer($idUsuario, (int) auth()->id());

        DB::table('bitacora_auditoria')->insert([
            'id_usuario' => auth()->id(),
            'accion' => 'CONTRASENA_RESTABLECIDA',
            'modulo' => 'SEGURIDAD',
            'descripcion' => 'Se asignó una contraseña temporal a '
                . trim($usuario->nombres . ' ' . $usuario->apellido_paterno)
                . ' (' . $usuario->username . ') tras verificar sus preguntas de seguridad.',
            'fecha_hora' => now(),
        ]);

        return [
            'ok' => true,
            'temporal' => $temporal,
            'usuario' => $usuario->username,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Dar de baja / reactivar una cuenta
    |--------------------------------------------------------------------------
    |
    | Al dar de baja, los equipos asignados a esa cuenta se devuelven
    | automáticamente (motivo «Baja del colaborador») y quedan
    | disponibles; también se cierran sus sesiones abiertas.
    |
    */

    public function darDeBajaUsuario(mixed $idUsuario): array
    {
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        $id = (int) $idUsuario;

        if ($id <= 0 || $id === (int) auth()->id()) {
            return ['ok' => false, 'mensaje' => 'No puedes dar de baja tu propia cuenta.'];
        }

        $idBaja = DB::table('estados_usuario')->where('clave', 'BAJA')->value('id_estado_usuario');

        if (! $idBaja) {
            return ['ok' => false, 'mensaje' => 'No existe el estado BAJA en el catálogo de estados de usuario.'];
        }

        $usuario = DB::table('usuarios')->where('id_usuario', $id)->first(['nombres', 'apellido_paterno', 'correo']);

        if (! $usuario) {
            return ['ok' => false, 'mensaje' => 'La cuenta ya no existe.'];
        }

        $nombre = trim($usuario->nombres . ' ' . $usuario->apellido_paterno);

        DB::table('usuarios')->where('id_usuario', $id)->update(['id_estado_usuario' => $idBaja]);

        $devueltos = app(\App\Services\DevolucionService::class)->devolverTodosDeUsuario(
            $id,
            \App\Services\DevolucionService::MOTIVOS['baja_colaborador'],
            'Devolución automática al dar de baja la cuenta de ' . $nombre . '.'
        );

        // Cerrar sesiones abiertas de esa cuenta (sesiones en base de datos).
        if (\App\Support\Esquema::tieneTabla('sessions')) {
            DB::table('sessions')->where('user_id', $id)->delete();
        }

        DB::table('bitacora_auditoria')->insert([
            'id_usuario' => auth()->id(),
            'accion' => 'USUARIO_DADO_DE_BAJA',
            'modulo' => 'SEGURIDAD',
            'descripcion' => 'Se dio de baja la cuenta de ' . $nombre . ' (' . $usuario->correo . ').'
                . ($devueltos !== [] ? ' Equipos devueltos: ' . implode(', ', $devueltos) . '.' : ''),
            'fecha_hora' => now(),
        ]);

        if ($devueltos !== []) {
            $notificaciones = app(\App\Services\NotificacionService::class);
            $notificaciones->notificar(
                'asignaciones',
                'Equipos devueltos por baja',
                $notificaciones->nombreActor() . ' dio de baja la cuenta de ' . $nombre . '; se devolvieron ' . count($devueltos) . ' equipo(s): ' . implode(', ', $devueltos) . '.',
                route('asignaciones.index', [], false)
            );
            $this->dispatch('notificaciones-actualizadas');
        }

        $this->dispatch(
            'toast',
            type: 'success',
            title: 'Cuenta dada de baja',
            message: $devueltos === []
                ? $nombre . ' ya no puede entrar al sistema. No tenía equipos asignados.'
                : $nombre . ' ya no puede entrar al sistema. Se devolvieron ' . count($devueltos) . ' equipo(s): ' . implode(', ', $devueltos) . '.',
        );

        return ['ok' => true, 'devueltos' => $devueltos];
    }

    public function reactivarUsuario(mixed $idUsuario): array
    {
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        $id = (int) $idUsuario;
        $idActivo = DB::table('estados_usuario')->where('clave', 'ACTIVO')->value('id_estado_usuario');

        if ($id <= 0 || ! $idActivo) {
            return ['ok' => false, 'mensaje' => 'No se pudo reactivar la cuenta.'];
        }

        $usuario = DB::table('usuarios')->where('id_usuario', $id)->first(['nombres', 'apellido_paterno', 'correo']);

        if (! $usuario) {
            return ['ok' => false, 'mensaje' => 'La cuenta ya no existe.'];
        }

        DB::table('usuarios')->where('id_usuario', $id)->update(['id_estado_usuario' => $idActivo]);

        DB::table('bitacora_auditoria')->insert([
            'id_usuario' => auth()->id(),
            'accion' => 'USUARIO_REACTIVADO',
            'modulo' => 'SEGURIDAD',
            'descripcion' => 'Se reactivó la cuenta de ' . trim($usuario->nombres . ' ' . $usuario->apellido_paterno) . ' (' . $usuario->correo . ').',
            'fecha_hora' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'La cuenta se reactivó. Sus equipos devueltos no se reasignan solos.');

        return ['ok' => true];
    }

    public function vincularColaborador(string $clave, mixed $idUsuario): void
    {
        \Illuminate\Support\Facades\Gate::authorize('administrar');

        $idUsuario = is_numeric($idUsuario) ? (int) $idUsuario : 0;

        if ($idUsuario <= 0) {
            $this->dispatch('toast', type: 'warning', message: 'Elige la cuenta con la que se vinculará el colaborador.');

            return;
        }

        $nombreCuenta = DB::table('usuarios')
            ->where('id_usuario', $idUsuario)
            ->selectRaw("TRIM(CONCAT_WS(' ', nombres, apellido_paterno, apellido_materno)) as nombre")
            ->value('nombre');

        $vinculadas = app(\App\Services\ColaboradoresService::class)->vincular($clave, $idUsuario);

        if ($vinculadas === 0) {
            $this->dispatch('toast', type: 'warning', message: 'No había asignaciones pendientes para ese colaborador.');

            return;
        }

        DB::table('bitacora_auditoria')->insert([
            'id_usuario' => auth()->id(),
            'accion' => 'COLABORADOR_VINCULADO',
            'modulo' => 'SEGURIDAD',
            'descripcion' => 'Se vincularon ' . $vinculadas . ' asignación(es) del colaborador «' . $clave . '» con la cuenta de ' . $nombreCuenta . '.',
            'fecha_hora' => now(),
        ]);

        $this->dispatch(
            'toast',
            type: 'success',
            message: 'Se vincularon ' . $vinculadas . ($vinculadas === 1 ? ' asignación' : ' asignaciones') . ' con la cuenta de ' . $nombreCuenta . '.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers privados
    |--------------------------------------------------------------------------
    */

    private function organizationFieldLabel(string $field): string
    {
        return match ($field) {
            'numero_colaborador' => 'Número de colaborador',
            'puesto' => 'Puesto',
            'rol' => 'Rol',
            'hotel' => app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'],
            'departamento' => 'Departamento',
            default => 'Información organizacional',
        };
    }

    private function resolveOrganizationCatalogId(
        string $field,
        ?string $requestedValue
    ): ?int {
        $requestedValue = trim((string) $requestedValue);

        if ($requestedValue === '') {
            return null;
        }

        $value = match ($field) {
            'rol' => DB::table('roles')
                ->where('activo', true)
                ->whereRaw(
                    'LOWER(nombre) = LOWER(?)',
                    [$requestedValue]
                )
                ->value('id_rol'),


            'hotel' => DB::table('unidades_organizativas')
                ->where('activo', true)
                ->whereRaw(
                    'LOWER(nombre) = LOWER(?)',
                    [$requestedValue]
                )
                ->value('id_unidad'),

            'departamento' => DB::table('departamentos')
                ->where('activo', true)
                ->whereRaw(
                    'LOWER(nombre) = LOWER(?)',
                    [$requestedValue]
                )
                ->value('id_departamento'),

            default => null,
        };

        return $value !== null
            ? (int) $value
            : null;
    }
}