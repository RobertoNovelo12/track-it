<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SeguridadIndex extends Component
{
    use WithPagination;

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
    | Reiniciar paginación
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
    | Abrir aprobación
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
            ->where(
                'u.id_usuario',
                $userId
            )
            ->where(
                'eu.clave',
                'PENDIENTE'
            )
            ->select([
                'u.id_usuario',
                'u.nombres',
                'u.apellido_paterno',
                'u.apellido_materno',
                'u.correo',
            ])
            ->first();


        /*
        * Solamente permitimos abrir el modal
        * para usuarios que siguen pendientes.
        */

        if (! $usuario) {
            return;
        }


        $this->approvalUserId =
            (int) $usuario->id_usuario;

        $this->approvalRoleId = null;

        $this->approvalUserName = trim(
            implode(
                ' ',
                array_filter([
                    $usuario->nombres,
                    $usuario->apellido_paterno,
                    $usuario->apellido_materno,
                ])
            )
        );

        $this->approvalUserEmail =
            $usuario->correo;

        $this->approvalModalOpen = true;
    }


    /*
    |--------------------------------------------------------------------------
    | Cerrar aprobación
    |--------------------------------------------------------------------------
    */

    public function closeApprovalModal(): void
    {
        $this->resetValidation();

        $this->approvalModalOpen = false;

        $this->approvalUserId = null;

        $this->approvalRoleId = null;

        $this->approvalUserName = '';

        $this->approvalUserEmail = '';
    }


    /*
    |--------------------------------------------------------------------------
    | Aprobar usuario
    |--------------------------------------------------------------------------
    */

    public function approveUser(): void
    {
        /*
        * Validación básica.
        */

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


        /*
        * Comprobar que el rol existe y está activo.
        */

        $rol = DB::table('roles')
            ->where(
                'id_rol',
                $this->approvalRoleId
            )
            ->where(
                'activo',
                true
            )
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


        /*
        * Obtener el estado ACTIVO.
        */

        $idEstadoActivo = DB::table('estados_usuario')
            ->where(
                'clave',
                'ACTIVO'
            )
            ->where(
                'activo',
                true
            )
            ->value(
                'id_estado_usuario'
            );


        if (! $idEstadoActivo) {

            $this->addError(
                'approvalRoleId',
                'No se encontró el estado ACTIVO en el sistema.'
            );

            return;
        }


        /*
        * Transacción:
        *
        * 1. Volvemos a comprobar que siga PENDIENTE.
        * 2. Asignamos rol.
        * 3. Cambiamos a ACTIVO.
        * 4. Registramos la auditoría.
        */

        $aprobado = DB::transaction(
            function () use (
                $rol,
                $idEstadoActivo
            ) {

                $usuario = DB::table('usuarios as u')
                    ->join(
                        'estados_usuario as eu',
                        'eu.id_estado_usuario',
                        '=',
                        'u.id_estado_usuario'
                    )
                    ->where(
                        'u.id_usuario',
                        $this->approvalUserId
                    )
                    ->where(
                        'eu.clave',
                        'PENDIENTE'
                    )
                    ->select([
                        'u.id_usuario',
                        'u.nombres',
                        'u.apellido_paterno',
                        'u.correo',
                    ])
                    ->lockForUpdate()
                    ->first();


                /*
                * Podría haber sido aprobado por otro administrador
                * mientras este modal estaba abierto.
                */

                if (! $usuario) {
                    return false;
                }


                DB::table('usuarios')
                    ->where(
                        'id_usuario',
                        $usuario->id_usuario
                    )
                    ->update([
                        'id_rol' =>
                            $rol->id_rol,

                        'id_estado_usuario' =>
                            $idEstadoActivo,
                    ]);


                /*
                * Auditoría.
                */

                DB::table('bitacora_auditoria')
                    ->insert([
                        'id_usuario' =>
                            auth()->id(),

                        'accion' =>
                            'USUARIO_APROBADO',

                        'modulo' =>
                            'SEGURIDAD',

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

                        'fecha_hora' =>
                            now(),
                    ]);


                return true;
            }
        );


        if (! $aprobado) {

            $this->addError(
                'approvalRoleId',
                'Este usuario ya no se encuentra pendiente de aprobación.'
            );

            return;
        }


        /*
        * Refrescar listado.
        */

        $this->resetPage(
            'usuariosPage'
        );


        /*
        * Cerrar modal.
        */

        $this->closeApprovalModal();


        /*
        * Evento que utilizaremos para mostrar
        * una confirmación visual.
        */

        $this->dispatch(
            'usuario-aprobado',
            message: 'Usuario aprobado correctamente.'
        );
    }

    public function placeholder(): View
    {
    return view(
        'livewire.placeholders.seguridad-index'
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

            /*
            |--------------------------------------------------------------------------
            | Roles disponibles para asignación
            |--------------------------------------------------------------------------
            */

            $roles = DB::table('roles')
                ->where(
                    'activo',
                    true
                )
                ->orderBy(
                    'nombre'
                )
                ->get([
                    'id_rol',
                    'nombre',
                    'descripcion',
                ]);


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
        | Estados disponibles
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Buscar
        |--------------------------------------------------------------------------
        */

        $search = trim($this->search);

        if ($search !== '') {

            $like =
                '%' . mb_strtolower($search) . '%';

            $usuariosQuery->where(
                function ($query) use ($like) {

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

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Filtrar por estado
        |--------------------------------------------------------------------------
        */

        if ($this->estado !== '') {

            $usuariosQuery->where(
                'eu.clave',
                $this->estado
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Orden
        |--------------------------------------------------------------------------
        |
        | Pendientes primero para que las solicitudes nuevas
        | sean visibles inmediatamente.
        |
        */

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

        $inicioSemana =
            now()
                ->startOfDay()
                ->subDays(6);


        /*
         * Actividad registrada en bitácora.
         */

        $actividadRaw = DB::table('bitacora_auditoria')

            ->where(
                'fecha_hora',
                '>=',
                $inicioSemana
            )

            ->selectRaw(
                'DATE(fecha_hora) AS fecha, COUNT(*) AS total'
            )

            ->groupByRaw(
                'DATE(fecha_hora)'
            )

            ->pluck(
                'total',
                'fecha'
            );


        /*
         * Usuarios registrados.
         */

        $registrosRaw = DB::table('usuarios')

            ->where(
                'fecha_registro',
                '>=',
                $inicioSemana
            )

            ->selectRaw(
                'DATE(fecha_registro) AS fecha, COUNT(*) AS total'
            )

            ->groupByRaw(
                'DATE(fecha_registro)'
            )

            ->pluck(
                'total',
                'fecha'
            );


        /*
         * Construimos los siete días aunque alguno tenga cero.
         */

        $resumenSemanal = collect(
            range(6, 0)
        )->map(
            function (int $dias) use (
                $actividadRaw,
                $registrosRaw
            ) {

                $fecha =
                    now()
                        ->startOfDay()
                        ->subDays($dias);


                $key =
                    $fecha->toDateString();


                return [
                    'fecha' => $key,

                    'label' =>
                        $fecha->format('d/m'),

                    'actividad' =>
                        (int) (
                            $actividadRaw[$key]
                            ?? 0
                        ),

                    'registros' =>
                        (int) (
                            $registrosRaw[$key]
                            ?? 0
                        ),
                ];

            }
        );


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

            ->whereIn(
                'eu.clave',
                [
                    'PENDIENTE',
                    'ACTIVO',
                ]
            )

            ->count();


        /*
         * Usuarios activos que llevan más de 90 días
         * sin utilizar el sistema.
         *
         * Si nunca han iniciado sesión usamos fecha_registro.
         */

        $usuariosInactivos90 = DB::table('usuarios as u')

            ->join(
                'estados_usuario as eu',
                'eu.id_estado_usuario',
                '=',
                'u.id_estado_usuario'
            )

            ->where(
                'eu.clave',
                'ACTIVO'
            )

            ->whereRaw(
                "
                COALESCE(
                    u.ultimo_acceso,
                    u.fecha_registro
                ) < ?
                ",
                [
                    now()->subDays(90),
                ]
            )

            ->count();


        /*
         * Roles activos que todavía no tienen permisos.
         */

    $rolesSinPermisos = DB::table('roles as r')

    ->where(
        'r.activo',
        true
    )

    ->whereNotExists(
        function ($query) {

            $query
                ->selectRaw('1')

                ->from(
                    'roles_permisos as rp'
                )

                ->whereColumn(
                    'rp.id_rol',
                    'r.id_rol'
                );

        }
    )

    ->count();  


        $alertas = collect();


        if ($stats['pendientes'] > 0) {

            $alertas->push([
                'type' => 'warning',
                'title' =>
                    $stats['pendientes']
                    . ' usuario'
                    . (
                        $stats['pendientes'] === 1
                            ? ''
                            : 's'
                    )
                    . ' pendiente'
                    . (
                        $stats['pendientes'] === 1
                            ? ''
                            : 's'
                    )
                    . ' de aprobación',
            ]);

        }


        if ($usuariosSinRol > 0) {

            $alertas->push([
                'type' => 'warning',
                'title' =>
                    $usuariosSinRol
                    . ' usuario'
                    . (
                        $usuariosSinRol === 1
                            ? ''
                            : 's'
                    )
                    . ' sin rol asignado',
            ]);

        }


        if ($usuariosInactivos90 > 0) {

            $alertas->push([
                'type' => 'danger',
                'title' =>
                    $usuariosInactivos90
                    . ' usuario'
                    . (
                        $usuariosInactivos90 === 1
                            ? ''
                            : 's'
                    )
                    . ' sin actividad por más de 90 días',
            ]);

        }


        if ($rolesSinPermisos > 0) {

            $alertas->push([
                'type' => 'info',
                'title' =>
                    $rolesSinPermisos
                    . ' rol'
                    . (
                        $rolesSinPermisos === 1
                            ? ''
                            : 'es'
                    )
                    . ' sin permisos configurados',
            ]);

        }


        /*
         * Si no existe ninguna alerta real.
         */

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
                'auditoria' => $auditoria,
                'resumenSemanal' => $resumenSemanal,
                'alertas' => $alertas,
            ]
        );
    }
}