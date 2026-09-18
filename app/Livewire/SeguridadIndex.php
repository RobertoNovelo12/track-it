<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SeguridadIndex extends Component
{
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
        Log::error('[DEBUG MODAL] 1. Método iniciado', [
            'request_id' => $requestId,
            'user_id' => auth()->id(),
        ]);

        try {
            $this->resetValidation();

            Log::error('[DEBUG MODAL] 2. Antes de consultar solicitud', [
                'request_id' => $requestId,
            ]);

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

            Log::error('[DEBUG MODAL] 3. Consulta terminada', [
                'request_id' => $requestId,
                'encontrada' => (bool) $solicitud,
                'campo' => $solicitud?->campo,
                'estado_buscado' => 'pendiente',
            ]);

            if (! $solicitud) {
                Log::error('[DEBUG MODAL] 4. Solicitud no encontrada o no pendiente', [
                    'request_id' => $requestId,
                ]);

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

            Log::error('[DEBUG MODAL] 5. Datos básicos cargados', [
                'organizationRequestId' => $this->organizationRequestId,
                'organizationRequestField' => $this->organizationRequestField,
                'organizationRequestUserName' => $this->organizationRequestUserName,
            ]);

            Log::error('[DEBUG MODAL] 6. Antes de resolver catálogo', [
                'campo' => $solicitud->campo,
                'valor_solicitado' => $solicitud->valor_solicitado,
            ]);

            $this->organizationRequestCatalogId =
                $this->resolveOrganizationCatalogId(
                    $solicitud->campo,
                    $solicitud->valor_solicitado
                );

            Log::error('[DEBUG MODAL] 7. Catálogo resuelto', [
                'catalog_id' => $this->organizationRequestCatalogId,
            ]);

            $this->organizationRequestReviewComment = '';
            $this->organizationRequestModalOpen = true;

            Log::error('[DEBUG MODAL] 8. Modal marcada como abierta', [
                'organizationRequestModalOpen' => $this->organizationRequestModalOpen,
                'organizationRequestId' => $this->organizationRequestId,
            ]);
        } catch (\Throwable $e) {
            Log::error('[DEBUG MODAL] EXCEPCIÓN AL ABRIR', [
                'request_id' => $requestId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
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
                    'id_area',
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
            } elseif ($solicitud->campo === 'area') {
                if (! $this->organizationRequestCatalogId) {
                    return [
                        'ok' => false,
                        'message' =>
                            'Selecciona el área que se asignará al usuario.',
                    ];
                }

                $area = DB::table('areas')
                    ->where('id_area', $this->organizationRequestCatalogId)
                    ->where('activo', true)
                    ->first([
                        'id_area',
                        'nombre',
                    ]);

                if (! $area) {
                    return [
                        'ok' => false,
                        'message' =>
                            'El área seleccionada no está disponible.',
                    ];
                }

                $mantenerDepartamento = false;

                if ($usuario->id_departamento) {
                    $mantenerDepartamento = DB::table('departamentos')
                        ->where('id_departamento', $usuario->id_departamento)
                        ->where('id_area', $area->id_area)
                        ->exists();
                }

                $datosActualizar = [
                    'id_area' => $area->id_area,
                ];

                if (! $mantenerDepartamento) {
                    $datosActualizar['id_departamento'] = null;
                }

                $valorAplicado = $area->nombre;
            } elseif ($solicitud->campo === 'departamento') {
                if (! $this->organizationRequestCatalogId) {
                    return [
                        'ok' => false,
                        'message' =>
                            'Selecciona el departamento que se asignará al usuario.',
                    ];
                }

                $departamento = DB::table('departamentos')
                    ->where(
                        'id_departamento',
                        $this->organizationRequestCatalogId
                    )
                    ->where('activo', true)
                    ->first([
                        'id_departamento',
                        'id_area',
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
                    'id_area' => $departamento->id_area,
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

        $this->closeOrganizationRequestModal();

        $this->dispatch(
            'solicitud-organizacion-aprobada',
            message:
                'La solicitud fue aprobada y la información del usuario se actualizó correctamente.'
        );
    }

    public function rejectOrganizationRequest(): void
    {
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

    public function render(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Indicadores
        |--------------------------------------------------------------------------
        */

        $statsRow = DB::table('usuarios as u')
            ->join(
                'estados_usuario as eu',
                'eu.id_estado_usuario',
                '=',
                'u.id_estado_usuario'
            )
            ->selectRaw("
                COUNT(*) AS total,

                COUNT(*) FILTER (
                    WHERE eu.clave = 'ACTIVO'
                ) AS activos,

                COUNT(*) FILTER (
                    WHERE eu.clave = 'PENDIENTE'
                ) AS pendientes,

                COUNT(*) FILTER (
                    WHERE eu.clave IN ('INACTIVO', 'BAJA')
                ) AS inactivos,

                COUNT(*) FILTER (
                    WHERE DATE_TRUNC('month', u.fecha_registro)
                        = DATE_TRUNC('month', CURRENT_DATE)
                ) AS registrados_mes
            ")
            ->first();

        $rolesConfigurados = DB::table('roles')
            ->where('activo', true)
            ->count();

        $stats = [
            'total' => (int) ($statsRow->total ?? 0),
            'activos' => (int) ($statsRow->activos ?? 0),
            'pendientes' => (int) ($statsRow->pendientes ?? 0),
            'inactivos' => (int) ($statsRow->inactivos ?? 0),
            'registrados_mes' => (int) ($statsRow->registrados_mes ?? 0),
            'roles' => (int) $rolesConfigurados,
        ];

        /*
        |--------------------------------------------------------------------------
        | Catálogos disponibles
        |--------------------------------------------------------------------------
        */

        $roles = DB::table('roles')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id_rol',
                'nombre',
                'descripcion',
            ]);

        $areas = DB::table('areas')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id_area',
                'nombre',
            ]);

        $departamentos = DB::table('departamentos')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id_departamento',
                'id_area',
                'nombre',
            ]);

        $estados = DB::table('estados_usuario')
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get([
                'id_estado_usuario',
                'clave',
                'nombre',
            ]);

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
                'areas as a',
                'a.id_area',
                '=',
                'u.id_area'
            )
            ->leftJoin(
                'departamentos as d',
                'd.id_departamento',
                '=',
                'u.id_departamento'
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
                'a.nombre as area_nombre',
                'd.nombre as departamento_nombre',
            ]);

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
            ->orderByRaw("
                CASE eu.clave
                    WHEN 'PENDIENTE' THEN 0
                    WHEN 'ACTIVO' THEN 1
                    WHEN 'INACTIVO' THEN 2
                    WHEN 'BAJA' THEN 3
                    ELSE 4
                END
            ")
            ->orderByDesc('u.fecha_registro');

        $usuarios = $usuariosQuery->paginate(
            $this->perPage,
            ['*'],
            'usuariosPage'
        );

        /*
        |--------------------------------------------------------------------------
        | Solicitudes de cambio organizacional pendientes
        |--------------------------------------------------------------------------
        */

        $solicitudesCambio = DB::table('solicitudes_cambio_organizacion as s')
            ->join(
                'usuarios as u',
                'u.id_usuario',
                '=',
                's.id_usuario'
            )
            ->where('s.estado', 'pendiente')
            ->select([
                's.id_solicitud',
                's.campo',
                's.valor_actual',
                's.valor_solicitado',
                's.motivo',
                's.fecha_solicitud',
                'u.id_usuario',
                'u.username',
                'u.correo',
            ])
            ->selectRaw("
                TRIM(
                    CONCAT_WS(
                        ' ',
                        u.nombres,
                        u.apellido_paterno,
                        u.apellido_materno
                    )
                ) AS usuario_nombre
            ")
            ->orderBy('s.fecha_solicitud')
            ->get();

        $solicitudesCambioPendientes = $solicitudesCambio->count();

        /*
        |--------------------------------------------------------------------------
        | Bitácora de auditoría
        |--------------------------------------------------------------------------
        */

        $auditoria = DB::table('bitacora_auditoria as b')
            ->leftJoin(
                'usuarios as u',
                'u.id_usuario',
                '=',
                'b.id_usuario'
            )
            ->select([
                'b.id_bitacora',
                'b.accion',
                'b.modulo',
                'b.descripcion',
                'b.fecha_hora',
                'u.id_usuario',
                'u.username',
            ])
            ->selectRaw("
                TRIM(
                    CONCAT_WS(
                        ' ',
                        u.nombres,
                        u.apellido_paterno
                    )
                ) AS usuario_nombre
            ")
            ->orderByDesc('b.fecha_hora')
            ->limit(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Resumen de los últimos 7 días
        |--------------------------------------------------------------------------
        */

        $inicioSemana = now()
            ->startOfDay()
            ->subDays(6);

        $actividadRaw = DB::table('bitacora_auditoria')
            ->where('fecha_hora', '>=', $inicioSemana)
            ->selectRaw(
                'DATE(fecha_hora) AS fecha, COUNT(*) AS total'
            )
            ->groupByRaw('DATE(fecha_hora)')
            ->pluck('total', 'fecha');

        $registrosRaw = DB::table('usuarios')
            ->where('fecha_registro', '>=', $inicioSemana)
            ->selectRaw(
                'DATE(fecha_registro) AS fecha, COUNT(*) AS total'
            )
            ->groupByRaw('DATE(fecha_registro)')
            ->pluck('total', 'fecha');

        $resumenSemanal = collect(range(6, 0))
            ->map(function (int $dias) use ($actividadRaw, $registrosRaw) {
                $fecha = now()
                    ->startOfDay()
                    ->subDays($dias);

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
        | Alertas de seguridad
        |--------------------------------------------------------------------------
        */

        $usuariosSinRol = DB::table('usuarios as u')
            ->join(
                'estados_usuario as eu',
                'eu.id_estado_usuario',
                '=',
                'u.id_estado_usuario'
            )
            ->whereNull('u.id_rol')
            ->whereIn('eu.clave', [
                'PENDIENTE',
                'ACTIVO',
            ])
            ->count();

        $usuariosInactivos90 = DB::table('usuarios as u')
            ->join(
                'estados_usuario as eu',
                'eu.id_estado_usuario',
                '=',
                'u.id_estado_usuario'
            )
            ->where('eu.clave', 'ACTIVO')
            ->whereRaw(
                "
                COALESCE(
                    u.ultimo_acceso,
                    u.fecha_registro
                ) < ?
                ",
                [now()->subDays(90)]
            )
            ->count();

        $rolesSinPermisos = DB::table('roles as r')
            ->where('r.activo', true)
            ->whereNotExists(function ($query) {
                $query
                    ->selectRaw('1')
                    ->from('roles_permisos as rp')
                    ->whereColumn('rp.id_rol', 'r.id_rol');
            })
            ->count();

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

        return view(
            'livewire.seguridad-index',
            [
                'stats' => $stats,
                'usuarios' => $usuarios,
                'estados' => $estados,
                'roles' => $roles,
                'areas' => $areas,
                'departamentos' => $departamentos,
                'solicitudesCambio' => $solicitudesCambio,
                'solicitudesCambioPendientes' =>
                    $solicitudesCambioPendientes,
                'auditoria' => $auditoria,
                'resumenSemanal' => $resumenSemanal,
                'alertas' => $alertas,
            ]
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
            'area' => 'Área',
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

            'area' => DB::table('areas')
                ->where('activo', true)
                ->whereRaw(
                    'LOWER(nombre) = LOWER(?)',
                    [$requestedValue]
                )
                ->value('id_area'),

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