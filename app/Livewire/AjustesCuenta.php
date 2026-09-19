<?php

namespace App\Livewire;

use App\Services\TwoFactorAuthService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Url;
use Livewire\Component;

class AjustesCuenta extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Secciones disponibles
    |--------------------------------------------------------------------------
    */

    private const SECTIONS = [
        'cuenta',
        'seguridad',
        'notificaciones',
        'preferencias',
    ];


    /*
    |--------------------------------------------------------------------------
    | Claves de sesión
    |--------------------------------------------------------------------------
    */

    private const TWO_FACTOR_SETUP_SESSION_KEY =
        'two_factor.setup_secret';


    /*
    |--------------------------------------------------------------------------
    | Sección activa
    |--------------------------------------------------------------------------
    */

    #[Url(
        as: 'section',
        history: true,
        keep: true
    )]
    public string $section = 'cuenta';


    /*
    |--------------------------------------------------------------------------
    | Edición de perfil
    |--------------------------------------------------------------------------
    */

    public string $editNombres = '';

    public string $editApellidoPaterno = '';

    public string $editApellidoMaterno = '';

    public string $editTelefono = '';


    /*
    |--------------------------------------------------------------------------
    | Solicitud de cambio organizacional
    |--------------------------------------------------------------------------
    */

    public string $organizationChangeField = '';

    public string $organizationRequestedValue = '';

    public string $organizationChangeReason = '';

    public ?string $organizationCurrentValue = null;


    /*
    |--------------------------------------------------------------------------
    | Seguridad - Cambio de contraseña
    |--------------------------------------------------------------------------
    */

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';


    /*
    |--------------------------------------------------------------------------
    | Seguridad - Autenticación en dos pasos
    |--------------------------------------------------------------------------
    */

    public bool $twoFactorEnabled = false;

    public string $twoFactorCode = '';

    public string $twoFactorManualKey = '';

    public string $twoFactorQrCode = '';

    public array $twoFactorRecoveryCodes = [];

    public string $twoFactorManagementPassword = '';

    /*
    |--------------------------------------------------------------------------
    | Seguridad - Sesiones
    |--------------------------------------------------------------------------
    */

    public array $activeSessions = [];

    public string $currentSessionDevice = 'Dispositivo actual';


    /*
    |--------------------------------------------------------------------------
    | Seguridad - Alertas
    |--------------------------------------------------------------------------
    */

    public bool $alertaInicioSesion = true;

    public bool $alertaActividadSospechosa = true;


    /*
    |--------------------------------------------------------------------------
    | Notificaciones - Preferencias
    |--------------------------------------------------------------------------
    */

    public bool $notificarAsignacionesMovimientos = true;

    public bool $notificarMantenimientos = true;

    public bool $notificarCambiosEquipos = true;

    public bool $notificarUsuariosAccesos = true;

    public bool $notificarReportes = false;

    public bool $soloNoLeidas = false;

    public bool $mantenerHistorial = true;


    /*
    |--------------------------------------------------------------------------
    | Montaje
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return;
        }


        $this->editNombres =
            $usuario->nombres ?? '';

        $this->editApellidoPaterno =
            $usuario->apellido_paterno ?? '';

        $this->editApellidoMaterno =
            $usuario->apellido_materno ?? '';

        $this->editTelefono =
            $usuario->telefono ?? '';


        $this->twoFactorEnabled =
            $usuario->hasTwoFactorEnabled();


        /*
         * Identificar el navegador y sistema
         * operativo de la sesión actual.
         */
        $this->currentSessionDevice =
            $this->formatDevice(
                request()->userAgent()
            );


        /*
         * Cargar preferencias de alertas de seguridad.
         *
         * Si el usuario todavía no tiene una fila en la tabla,
         * conservamos los valores predeterminados en true.
         */
        $preferenciasSeguridad =
            DB::table('preferencias_seguridad')
                ->where(
                    'id_usuario',
                    $usuario->id_usuario
                )
                ->first();


        if ($preferenciasSeguridad) {
            $this->alertaInicioSesion =
                (bool) $preferenciasSeguridad->alerta_inicio_sesion;

            $this->alertaActividadSospechosa =
                (bool) $preferenciasSeguridad->alerta_actividad_sospechosa;
        }


        /*
         * Cargar preferencias del centro de notificaciones.
         *
         * Si el usuario todavía no tiene una fila, conservamos
         * los valores predeterminados definidos en las propiedades
         * y en la migración.
         */
        $preferenciasNotificaciones =
            DB::table('preferencias_notificaciones')
                ->where(
                    'id_usuario',
                    $usuario->id_usuario
                )
                ->first();


        if ($preferenciasNotificaciones) {
            $this->notificarAsignacionesMovimientos =
                (bool) $preferenciasNotificaciones->notificar_asignaciones_movimientos;

            $this->notificarMantenimientos =
                (bool) $preferenciasNotificaciones->notificar_mantenimientos;

            $this->notificarCambiosEquipos =
                (bool) $preferenciasNotificaciones->notificar_cambios_equipos;

            $this->notificarUsuariosAccesos =
                (bool) $preferenciasNotificaciones->notificar_usuarios_accesos;

            $this->notificarReportes =
                (bool) $preferenciasNotificaciones->notificar_reportes;

            $this->soloNoLeidas =
                (bool) $preferenciasNotificaciones->solo_no_leidas;

            $this->mantenerHistorial =
                (bool) $preferenciasNotificaciones->mantener_historial;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Navegación entre secciones
    |--------------------------------------------------------------------------
    */

    public function setSection(string $section): void
    {
        if (
            ! in_array(
                $section,
                self::SECTIONS,
                true
            )
        ) {
            return;
        }


        /*
         * Evitar que errores de una sección
         * permanezcan al cambiar a otra.
         */
        $this->resetValidation();


        $this->section = $section;
    }


    /*
    |--------------------------------------------------------------------------
    | Cerrar edición de perfil
    |--------------------------------------------------------------------------
    */

    public function closeEditProfileModal(): void
    {
        $this->resetValidation();
    }


    /*
    |--------------------------------------------------------------------------
    | Cerrar solicitud de cambio organizacional
    |--------------------------------------------------------------------------
    */

    public function closeOrganizationChangeRequestModal(): void
    {
        $this->resetOrganizationChangeRequest();
    }


    /*
    |--------------------------------------------------------------------------
    | Limpiar solicitud de cambio organizacional
    |--------------------------------------------------------------------------
    */

    private function resetOrganizationChangeRequest(): void
    {
        $this->organizationChangeField = '';

        $this->organizationRequestedValue = '';

        $this->organizationChangeReason = '';

        $this->organizationCurrentValue = null;

        $this->resetValidation([
            'organizationChangeField',
            'organizationRequestedValue',
            'organizationChangeReason',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Obtener valor actual del dato organizacional
    |--------------------------------------------------------------------------
    */

    public function updatedOrganizationChangeField(
        string $field
    ): void {
        $this->organizationRequestedValue = '';

        $this->resetValidation([
            'organizationChangeField',
            'organizationRequestedValue',
        ]);


        if ($field === '') {
            $this->organizationCurrentValue = null;

            return;
        }


        $usuario = DB::table('usuarios as u')
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
            ->where(
                'u.id_usuario',
                auth()->id()
            )
            ->select([
                'u.numero_colaborador',
                'u.puesto',
                'r.nombre as rol_nombre',
                'a.nombre as area_nombre',
                'd.nombre as departamento_nombre',
            ])
            ->first();


        if (! $usuario) {
            $this->organizationCurrentValue = null;

            return;
        }


        $this->organizationCurrentValue = match ($field) {
            'numero_colaborador' =>
                $usuario->numero_colaborador,

            'puesto' =>
                $usuario->puesto,

            'rol' =>
                $usuario->rol_nombre,

            'area' =>
                $usuario->area_nombre,

            'departamento' =>
                $usuario->departamento_nombre,

            default =>
                null,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Registrar solicitud de cambio organizacional
    |--------------------------------------------------------------------------
    */

    public function requestOrganizationChange(): void
    {
        $validated = $this->validate([
            'organizationChangeField' => [
                'required',
                'string',
                'in:numero_colaborador,puesto,rol,area,departamento',
            ],

            'organizationRequestedValue' => [
                'required',
                'string',
                'max:255',
            ],

            'organizationChangeReason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'organizationChangeField.required' =>
                'Selecciona la información que deseas actualizar.',

            'organizationChangeField.in' =>
                'La información seleccionada no es válida.',

            'organizationRequestedValue.required' =>
                'Ingresa el valor que deseas solicitar.',

            'organizationRequestedValue.max' =>
                'El valor solicitado no puede superar los 255 caracteres.',

            'organizationChangeReason.max' =>
                'El comentario no puede superar los 1000 caracteres.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Obtener información actual directamente desde BD
        |--------------------------------------------------------------------------
        */

        $usuario = DB::table('usuarios as u')
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
            ->where(
                'u.id_usuario',
                auth()->id()
            )
            ->select([
                'u.id_usuario',
                'u.numero_colaborador',
                'u.puesto',
                'r.nombre as rol_nombre',
                'a.nombre as area_nombre',
                'd.nombre as departamento_nombre',
            ])
            ->first();


        if (! $usuario) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Determinar valor actual
        |--------------------------------------------------------------------------
        */

        $valorActual = match (
            $validated['organizationChangeField']
        ) {
            'numero_colaborador' =>
                $usuario->numero_colaborador,

            'puesto' =>
                $usuario->puesto,

            'rol' =>
                $usuario->rol_nombre,

            'area' =>
                $usuario->area_nombre,

            'departamento' =>
                $usuario->departamento_nombre,

            default =>
                null,
        };


        $valorSolicitado = trim(
            $validated['organizationRequestedValue']
        );


        /*
        |--------------------------------------------------------------------------
        | Evitar solicitar exactamente el mismo valor
        |--------------------------------------------------------------------------
        */

        if (
            mb_strtolower(trim((string) $valorActual)) ===
            mb_strtolower($valorSolicitado)
        ) {
            $this->addError(
                'organizationRequestedValue',
                'El valor solicitado es igual al que tienes actualmente.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Evitar solicitudes pendientes duplicadas
        |--------------------------------------------------------------------------
        */

        $solicitudPendiente = DB::table(
            'solicitudes_cambio_organizacion'
        )
            ->where(
                'id_usuario',
                $usuario->id_usuario
            )
            ->where(
                'campo',
                $validated['organizationChangeField']
            )
            ->where(
                'estado',
                'pendiente'
            )
            ->exists();


        if ($solicitudPendiente) {
            $this->addError(
                'organizationChangeField',
                'Ya tienes una solicitud pendiente para esta información.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Registrar solicitud y auditoría
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $usuario,
            $validated,
            $valorActual,
            $valorSolicitado
        ) {

            DB::table(
                'solicitudes_cambio_organizacion'
            )
                ->insert([
                    'id_usuario' =>
                        $usuario->id_usuario,

                    'campo' =>
                        $validated['organizationChangeField'],

                    'valor_actual' =>
                        filled($valorActual)
                            ? trim((string) $valorActual)
                            : null,

                    'valor_solicitado' =>
                        $valorSolicitado,

                    'motivo' =>
                        filled(
                            $validated['organizationChangeReason']
                        )
                            ? trim(
                                $validated['organizationChangeReason']
                            )
                            : null,

                    'estado' =>
                        'pendiente',

                    'fecha_solicitud' =>
                        now(),
                ]);


            /*
            |--------------------------------------------------------------------------
            | Bitácora
            |--------------------------------------------------------------------------
            */

            DB::table('bitacora_auditoria')
                ->insert([
                    'id_usuario' =>
                        $usuario->id_usuario,

                    'accion' =>
                        'SOLICITUD_CAMBIO_ORGANIZACION',

                    'modulo' =>
                        'AJUSTES',

                    'descripcion' =>
                        'El usuario solicitó la actualización del campo organizacional: '
                        . $validated['organizationChangeField']
                        . '.',

                    'fecha_hora' =>
                        now(),
                ]);
        });


        /*
        |--------------------------------------------------------------------------
        | Limpiar formulario
        |--------------------------------------------------------------------------
        */

        $this->resetOrganizationChangeRequest();


        /*
        |--------------------------------------------------------------------------
        | Cerrar modal
        |--------------------------------------------------------------------------
        */

        $this->dispatch(
            'solicitud-organizacion-enviada',
            message:
                'Tu solicitud fue enviada correctamente y está pendiente de revisión.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar perfil
    |--------------------------------------------------------------------------
    */

    public function updateProfile(): void
    {
        $validated = $this->validate([
            'editNombres' => [
                'required',
                'string',
                'max:255',
            ],

            'editApellidoPaterno' => [
                'required',
                'string',
                'max:255',
            ],

            'editApellidoMaterno' => [
                'nullable',
                'string',
                'max:255',
            ],

            'editTelefono' => [
                'nullable',
                'string',
                'max:50',
            ],
        ], [
            'editNombres.required' =>
                'El nombre es obligatorio.',

            'editApellidoPaterno.required' =>
                'El apellido paterno es obligatorio.',
        ]);


        DB::transaction(function () use ($validated) {

            /*
             * Actualizar datos personales.
             */
            DB::table('usuarios')
                ->where(
                    'id_usuario',
                    auth()->id()
                )
                ->update([
                    'nombres' =>
                        trim(
                            $validated['editNombres']
                        ),

                    'apellido_paterno' =>
                        trim(
                            $validated['editApellidoPaterno']
                        ),

                    'apellido_materno' =>
                        filled(
                            $validated['editApellidoMaterno']
                        )
                            ? trim(
                                $validated['editApellidoMaterno']
                            )
                            : null,

                    'telefono' =>
                        filled(
                            $validated['editTelefono']
                        )
                            ? trim(
                                $validated['editTelefono']
                            )
                            : null,
                ]);


            /*
             * Registrar en bitácora.
             */
            DB::table('bitacora_auditoria')
                ->insert([
                    'id_usuario' =>
                        auth()->id(),

                    'accion' =>
                        'PERFIL_ACTUALIZADO',

                    'modulo' =>
                        'AJUSTES',

                    'descripcion' =>
                        'El usuario actualizó la información personal de su perfil.',

                    'fecha_hora' =>
                        now(),
                ]);
        });


        /*
         * Limpiar posibles errores anteriores.
         */
        $this->resetValidation();


        /*
         * Cerrar modal y mostrar toast.
         */
        $this->dispatch(
            'perfil-actualizado',
            message:
                'Tu información se actualizó correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Limpiar formulario de contraseña
    |--------------------------------------------------------------------------
    */

    public function resetPasswordForm(): void
    {
        $this->resetValidation();


        $this->currentPassword = '';

        $this->newPassword = '';

        $this->newPasswordConfirmation = '';
    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar contraseña
    |--------------------------------------------------------------------------
    */

    public function updatePassword(): void
    {
        $validated = $this->validate([
            'currentPassword' => [
                'required',
                'string',
            ],

            'newPassword' => [
                'required',
                'string',
                'min:8',
            ],

            'newPasswordConfirmation' => [
                'required',
                'string',
                'same:newPassword',
            ],
        ], [
            'currentPassword.required' =>
                'Ingresa tu contraseña actual.',

            'newPassword.required' =>
                'Ingresa una nueva contraseña.',

            'newPassword.min' =>
                'La nueva contraseña debe tener al menos 8 caracteres.',

            'newPasswordConfirmation.required' =>
                'Confirma la nueva contraseña.',

            'newPasswordConfirmation.same' =>
                'La confirmación de contraseña no coincide.',
        ]);


        $usuario = auth()->user();

        if (! $usuario) {
            return;
        }


        /*
         * Comprobar contraseña actual.
         */
        if (
            ! Hash::check(
                $validated['currentPassword'],
                $usuario->getAuthPassword()
            )
        ) {
            $this->addError(
                'currentPassword',
                'La contraseña actual no es correcta.'
            );

            return;
        }


        /*
         * Evitar reutilizar la contraseña actual.
         */
        if (
            Hash::check(
                $validated['newPassword'],
                $usuario->getAuthPassword()
            )
        ) {
            $this->addError(
                'newPassword',
                'La nueva contraseña debe ser diferente a la actual.'
            );

            return;
        }


        DB::transaction(function () use (
            $usuario,
            $validated
        ) {

            /*
             * Actualizar contraseña.
             */
            DB::table('usuarios')
                ->where(
                    'id_usuario',
                    $usuario->id_usuario
                )
                ->update([
                    'password_hash' =>
                        Hash::make(
                            $validated['newPassword']
                        ),
                ]);


            /*
             * Registrar en bitácora.
             */
            DB::table('bitacora_auditoria')
                ->insert([
                    'id_usuario' =>
                        $usuario->id_usuario,

                    'accion' =>
                        'CONTRASENA_ACTUALIZADA',

                    'modulo' =>
                        'AJUSTES',

                    'descripcion' =>
                        'El usuario actualizó la contraseña de su cuenta.',

                    'fecha_hora' =>
                        now(),
                ]);
        });


        /*
         * Limpiar campos sensibles.
         */
        $this->currentPassword = '';

        $this->newPassword = '';

        $this->newPasswordConfirmation = '';


        $this->resetValidation();


        /*
         * La vista escuchará este evento para
         * cerrar el modal y mostrar el toast.
         */
        $this->dispatch(
            'password-updated',
            message:
                'Tu contraseña se actualizó correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Iniciar configuración 2FA
    |--------------------------------------------------------------------------
    */

    public function startTwoFactorSetup(): void
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return;
        }


        /*
         * Si 2FA ya está activo no debemos
         * iniciar una nueva configuración.
         */
        if ($usuario->hasTwoFactorEnabled()) {
            $this->twoFactorEnabled = true;

            return;
        }


        /*
         * Limpiar estado anterior.
         */
        $this->resetValidation();


        $this->twoFactorCode = '';

        $this->twoFactorManualKey = '';

        $this->twoFactorQrCode = '';

        $this->twoFactorRecoveryCodes = [];


        $twoFactor = app(
            TwoFactorAuthService::class
        );


        /*
         * Generar un nuevo secreto.
         *
         * Todavía NO se guarda en la base
         * de datos porque el usuario primero
         * debe demostrar que configuró
         * correctamente su autenticador.
         */
        $secret =
            $twoFactor->generateSecret();


        /*
         * Guardar temporalmente en sesión.
         */
        session()->put(
            self::TWO_FACTOR_SETUP_SESSION_KEY,
            $secret
        );


        /*
         * Clave manual.
         */
        $this->twoFactorManualKey =
            $twoFactor->formatSecret(
                $secret
            );


        /*
         * Código QR.
         */
        $this->twoFactorQrCode =
            $twoFactor->generateQrCode(
                $usuario,
                $secret
            );


        /*
         * Avisar a Alpine que puede abrir
         * el modal de configuración.
         */
        $this->dispatch(
            'two-factor-setup-started'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancelar configuración 2FA
    |--------------------------------------------------------------------------
    */

    public function cancelTwoFactorSetup(): void
    {
        /*
         * Eliminar secreto temporal.
         */
        session()->forget(
            self::TWO_FACTOR_SETUP_SESSION_KEY
        );


        /*
         * Limpiar estado sensible.
         */
        $this->twoFactorCode = '';

        $this->twoFactorManualKey = '';

        $this->twoFactorQrCode = '';

        $this->twoFactorRecoveryCodes = [];


        $this->resetValidation(
            'twoFactorCode'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Confirmar configuración 2FA
    |--------------------------------------------------------------------------
    */

    public function confirmTwoFactorSetup(): void
    {
        $validated = $this->validate([
            'twoFactorCode' => [
                'required',
                'digits:6',
            ],
        ], [
            'twoFactorCode.required' =>
                'Ingresa el código de verificación.',

            'twoFactorCode.digits' =>
                'El código debe contener 6 dígitos.',
        ]);


        $usuario = auth()->user();

        if (! $usuario) {
            return;
        }


        /*
         * Evitar volver a confirmar si 2FA
         * ya estaba habilitado.
         */
        if ($usuario->hasTwoFactorEnabled()) {
            $this->twoFactorEnabled = true;

            session()->forget(
                self::TWO_FACTOR_SETUP_SESSION_KEY
            );

            return;
        }


        /*
         * Recuperar el secreto temporal.
         */
        $secret = session(
            self::TWO_FACTOR_SETUP_SESSION_KEY
        );


        if (! $secret) {
            $this->addError(
                'twoFactorCode',
                'La configuración expiró. Vuelve a iniciar el proceso.'
            );

            return;
        }


        $twoFactor = app(
            TwoFactorAuthService::class
        );


        /*
         * Comprobar código generado por
         * la aplicación autenticadora.
         */
        if (
            ! $twoFactor->verifyCode(
                $secret,
                $validated['twoFactorCode']
            )
        ) {
            $this->addError(
                'twoFactorCode',
                'El código de verificación no es válido.'
            );

            return;
        }


        /*
         * Generar códigos de recuperación.
         *
         * Los códigos originales solamente
         * se mostrarán una vez.
         */
        $recoveryCodes =
            $twoFactor->generateRecoveryCodes();


        /*
         * Guardaremos únicamente sus hashes.
         */
        $hashedRecoveryCodes =
            $twoFactor->hashRecoveryCodes(
                $recoveryCodes
            );


        DB::transaction(
            function () use (
                $usuario,
                $secret,
                $hashedRecoveryCodes
            ) {

                /*
                 * El modelo User tiene el cast:
                 *
                 * two_factor_secret => encrypted
                 *
                 * por lo que Laravel cifra el
                 * secreto antes de almacenarlo.
                 */
                $usuario->two_factor_secret =
                    $secret;


                /*
                 * Este campo determina que
                 * 2FA quedó completamente activo.
                 */
                $usuario->two_factor_confirmed_at =
                    now();


                /*
                 * Son hashes, no códigos
                 * recuperables en texto plano.
                 */
                $usuario->two_factor_recovery_codes =
                    $hashedRecoveryCodes;


                $usuario->save();


                /*
                 * Registrar activación.
                 */
                DB::table('bitacora_auditoria')
                    ->insert([
                        'id_usuario' =>
                            $usuario->id_usuario,

                        'accion' =>
                            'DOS_FACTORES_ACTIVADO',

                        'modulo' =>
                            'AJUSTES',

                        'descripcion' =>
                            'El usuario activó la autenticación en dos pasos.',

                        'fecha_hora' =>
                            now(),
                    ]);
            }
        );


        /*
         * Ya no necesitamos conservar
         * el secreto temporal.
         */
        session()->forget(
            self::TWO_FACTOR_SETUP_SESSION_KEY
        );


        /*
         * Estos son los códigos originales.
         *
         * Permanecen temporalmente en el
         * estado de Livewire para mostrarlos
         * al usuario una sola vez.
         */
        $this->twoFactorRecoveryCodes =
            $recoveryCodes;


        /*
         * Reflejar nuevo estado.
         */
        $this->twoFactorEnabled = true;


        /*
         * Limpiar los demás datos sensibles
         * de la configuración.
         */
        $this->twoFactorCode = '';

        $this->twoFactorManualKey = '';

        $this->twoFactorQrCode = '';


        $this->resetValidation();


        /*
         * El modal podrá pasar de la pantalla
         * de QR a códigos de recuperación.
         */
        $this->dispatch(
            'two-factor-enabled',
            message:
                'La autenticación en dos pasos se activó correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Finalizar configuración 2FA
    |--------------------------------------------------------------------------
    */

    public function finishTwoFactorSetup(): void
    {
        /*
         * A partir de este momento los
         * recovery codes originales dejan
         * de existir en el estado de Livewire.
         */
        $this->twoFactorRecoveryCodes = [];


        $this->twoFactorCode = '';

        $this->twoFactorManualKey = '';

        $this->twoFactorQrCode = '';


        /*
         * Por seguridad eliminamos también
         * cualquier secreto temporal que
         * pudiera permanecer en sesión.
         */
        session()->forget(
            self::TWO_FACTOR_SETUP_SESSION_KEY
        );


        $this->resetValidation();
    }


    /*
    |--------------------------------------------------------------------------
    | Limpiar administración 2FA
    |--------------------------------------------------------------------------
    */

    public function resetTwoFactorManagement(): void
    {
        $this->twoFactorManagementPassword = '';

        /*
         * Los códigos originales nunca deben permanecer
         * en el estado del componente más de lo necesario.
         */
        $this->twoFactorRecoveryCodes = [];

        $this->resetValidation();
    }


    /*
    |--------------------------------------------------------------------------
    | Regenerar códigos de recuperación 2FA
    |--------------------------------------------------------------------------
    */

    public function regenerateTwoFactorRecoveryCodes(): void
    {
        $validated = $this->validate([
            'twoFactorManagementPassword' => [
                'required',
                'string',
            ],
        ], [
            'twoFactorManagementPassword.required' =>
                'Ingresa tu contraseña para continuar.',
        ]);


        $usuario = auth()->user();

        if (! $usuario) {
            return;
        }


        /*
         * La cuenta debe tener 2FA activo.
         */
        if (! $usuario->hasTwoFactorEnabled()) {
            $this->twoFactorEnabled = false;

            $this->addError(
                'twoFactorManagementPassword',
                'La autenticación en dos pasos ya no está activa.'
            );

            return;
        }


        /*
         * Reautenticación antes de una acción sensible.
         */
        if (
            ! Hash::check(
                $validated['twoFactorManagementPassword'],
                $usuario->getAuthPassword()
            )
        ) {
            $this->addError(
                'twoFactorManagementPassword',
                'La contraseña no es correcta.'
            );

            return;
        }


        $twoFactor = app(
            TwoFactorAuthService::class
        );


        /*
         * Generamos códigos nuevos.
         *
         * Los anteriores dejarán de funcionar.
         */
        $recoveryCodes =
            $twoFactor->generateRecoveryCodes();


        $hashedRecoveryCodes =
            $twoFactor->hashRecoveryCodes(
                $recoveryCodes
            );


        DB::transaction(
            function () use (
                $usuario,
                $hashedRecoveryCodes
            ) {
                $usuario->two_factor_recovery_codes =
                    $hashedRecoveryCodes;

                $usuario->save();


                DB::table('bitacora_auditoria')
                    ->insert([
                        'id_usuario' =>
                            $usuario->id_usuario,

                        'accion' =>
                            'CODIGOS_RECUPERACION_REGENERADOS',

                        'modulo' =>
                            'AJUSTES',

                        'descripcion' =>
                            'El usuario regeneró sus códigos de recuperación de autenticación en dos pasos.',

                        'fecha_hora' =>
                            now(),
                    ]);
            }
        );


        /*
         * Los originales solamente estarán disponibles
         * temporalmente para mostrarlos una vez.
         */
        $this->twoFactorRecoveryCodes =
            $recoveryCodes;


        $this->twoFactorManagementPassword = '';

        $this->resetValidation();


        $this->dispatch(
            'two-factor-recovery-codes-regenerated',
            message:
                'Se generaron nuevos códigos de recuperación.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Desactivar autenticación en dos pasos
    |--------------------------------------------------------------------------
    */

    public function disableTwoFactor(): void
    {
        $validated = $this->validate([
            'twoFactorManagementPassword' => [
                'required',
                'string',
            ],
        ], [
            'twoFactorManagementPassword.required' =>
                'Ingresa tu contraseña para continuar.',
        ]);


        $usuario = auth()->user();

        if (! $usuario) {
            return;
        }


        /*
         * Si ya estaba desactivado simplemente
         * sincronizamos la interfaz.
         */
        if (! $usuario->hasTwoFactorEnabled()) {
            $this->twoFactorEnabled = false;

            $this->resetTwoFactorManagement();

            return;
        }


        /*
         * Confirmar identidad.
         */
        if (
            ! Hash::check(
                $validated['twoFactorManagementPassword'],
                $usuario->getAuthPassword()
            )
        ) {
            $this->addError(
                'twoFactorManagementPassword',
                'La contraseña no es correcta.'
            );

            return;
        }


        DB::transaction(
            function () use ($usuario) {

                /*
                 * Eliminar completamente la configuración 2FA.
                 */
                $usuario->two_factor_secret = null;

                $usuario->two_factor_confirmed_at = null;

                $usuario->two_factor_recovery_codes = null;

                $usuario->save();


                /*
                 * Auditoría.
                 */
                DB::table('bitacora_auditoria')
                    ->insert([
                        'id_usuario' =>
                            $usuario->id_usuario,

                        'accion' =>
                            'DOS_FACTORES_DESACTIVADO',

                        'modulo' =>
                            'AJUSTES',

                        'descripcion' =>
                            'El usuario desactivó la autenticación en dos pasos.',

                        'fecha_hora' =>
                            now(),
                    ]);
            }
        );


        /*
         * Limpiar cualquier configuración pendiente.
         */
        session()->forget(
            self::TWO_FACTOR_SETUP_SESSION_KEY
        );


        $this->twoFactorEnabled = false;

        $this->twoFactorManagementPassword = '';

        $this->twoFactorRecoveryCodes = [];

        $this->twoFactorCode = '';

        $this->twoFactorManualKey = '';

        $this->twoFactorQrCode = '';

        $this->resetValidation();


        $this->dispatch(
            'two-factor-disabled',
            message:
                'La autenticación en dos pasos se desactivó correctamente.'
        );
    }




    /*
    |--------------------------------------------------------------------------
    | Seguridad - Obtener sesiones activas
    |--------------------------------------------------------------------------
    */

    public function loadActiveSessions(): void
    {
        $usuario = auth()->user();

        if (! $usuario) {
            $this->activeSessions = [];

            return;
        }


        $currentSessionId =
            session()->getId();


        $sessions = DB::table('sessions')
            ->where(
                'user_id',
                $usuario->id_usuario
            )
            ->orderByDesc(
                'last_activity'
            )
            ->get();


        $this->activeSessions =
            $sessions
                ->map(
                    function ($session) use (
                        $currentSessionId
                    ): array {

                        $isCurrent =
                            hash_equals(
                                (string) $currentSessionId,
                                (string) $session->id
                            );


                        return [
                            'device' =>
                                $this->formatDevice(
                                    $session->user_agent
                                ),

                            'ip_address' =>
                                filled($session->ip_address)
                                    ? $session->ip_address
                                    : 'IP no disponible',

                            'is_current' =>
                                $isCurrent,

                            'last_activity' =>
                                $isCurrent
                                    ? 'Activa ahora'
                                    : Carbon::createFromTimestamp(
                                        (int) $session->last_activity
                                    )
                                        ->locale('es')
                                        ->diffForHumans(),
                        ];
                    }
                )
                ->values()
                ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Seguridad - Cerrar otras sesiones
    |--------------------------------------------------------------------------
    */

    public function logoutOtherSessions(): void
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return;
        }


        $currentSessionId =
            session()->getId();


        $closedSessions = DB::transaction(
            function () use (
                $usuario,
                $currentSessionId
            ): int {

                /*
                 * Eliminamos todas las sesiones del usuario
                 * excepto la que está realizando esta petición.
                 */
                $deleted = DB::table('sessions')
                    ->where(
                        'user_id',
                        $usuario->id_usuario
                    )
                    ->where(
                        'id',
                        '!=',
                        $currentSessionId
                    )
                    ->delete();


                /*
                 * Registrar solamente si realmente
                 * se cerró alguna sesión.
                 */
                if ($deleted > 0) {
                    DB::table('bitacora_auditoria')
                        ->insert([
                            'id_usuario' =>
                                $usuario->id_usuario,

                            'accion' =>
                                'OTRAS_SESIONES_CERRADAS',

                            'modulo' =>
                                'AJUSTES',

                            'descripcion' =>
                                "El usuario cerró {$deleted} sesión(es) en otros dispositivos.",

                            'fecha_hora' =>
                                now(),
                        ]);
                }


                return $deleted;
            }
        );


        /*
         * Si el modal está abierto, reflejar
         * inmediatamente las sesiones restantes.
         */
        $this->loadActiveSessions();


        $message =
            $closedSessions > 0
                ? (
                    $closedSessions === 1
                        ? 'Se cerró la otra sesión correctamente.'
                        : "Se cerraron {$closedSessions} sesiones correctamente."
                )
                : 'No había otras sesiones abiertas.';


        $this->dispatch(
            'other-sessions-closed',
            message: $message
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seguridad - Guardar preferencias de alertas
    |--------------------------------------------------------------------------
    */

    public function saveSecurityPreferences(): void
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return;
        }


        DB::transaction(
            function () use ($usuario) {

                /*
                 * Crear la configuración si todavía no existe
                 * o actualizarla si el usuario ya la guardó antes.
                 */
                DB::table('preferencias_seguridad')
                    ->updateOrInsert(
                        [
                            'id_usuario' =>
                                $usuario->id_usuario,
                        ],
                        [
                            'alerta_inicio_sesion' =>
                                $this->alertaInicioSesion,

                            'alerta_actividad_sospechosa' =>
                                $this->alertaActividadSospechosa,

                            'fecha_actualizacion' =>
                                now(),
                        ]
                    );


                /*
                 * Registrar el cambio en bitácora.
                 */
                DB::table('bitacora_auditoria')
                    ->insert([
                        'id_usuario' =>
                            $usuario->id_usuario,

                        'accion' =>
                            'PREFERENCIAS_SEGURIDAD_ACTUALIZADAS',

                        'modulo' =>
                            'AJUSTES',

                        'descripcion' =>
                            'El usuario actualizó sus preferencias de alertas de seguridad.',

                        'fecha_hora' =>
                            now(),
                    ]);
            }
        );


        $this->dispatch(
            'security-preferences-updated',
            message:
                'Tus alertas de seguridad se actualizaron correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Notificaciones - Guardar preferencias
    |--------------------------------------------------------------------------
    */

    public function saveNotificationPreferences(): void
    {
        $usuario = auth()->user();

        if (! $usuario) {
            return;
        }


        DB::transaction(
            function () use ($usuario) {

                /*
                 * Crear la configuración si todavía no existe
                 * o actualizarla si el usuario ya la guardó antes.
                 */
                DB::table('preferencias_notificaciones')
                    ->updateOrInsert(
                        [
                            'id_usuario' =>
                                $usuario->id_usuario,
                        ],
                        [
                            'notificar_asignaciones_movimientos' =>
                                $this->notificarAsignacionesMovimientos,

                            'notificar_mantenimientos' =>
                                $this->notificarMantenimientos,

                            'notificar_cambios_equipos' =>
                                $this->notificarCambiosEquipos,

                            'notificar_usuarios_accesos' =>
                                $this->notificarUsuariosAccesos,

                            'notificar_reportes' =>
                                $this->notificarReportes,

                            'solo_no_leidas' =>
                                $this->soloNoLeidas,

                            'mantener_historial' =>
                                $this->mantenerHistorial,

                            'fecha_actualizacion' =>
                                now(),
                        ]
                    );


                /*
                 * Registrar el cambio en bitácora.
                 */
                DB::table('bitacora_auditoria')
                    ->insert([
                        'id_usuario' =>
                            $usuario->id_usuario,

                        'accion' =>
                            'PREFERENCIAS_NOTIFICACIONES_ACTUALIZADAS',

                        'modulo' =>
                            'AJUSTES',

                        'descripcion' =>
                            'El usuario actualizó sus preferencias de notificaciones.',

                        'fecha_hora' =>
                            now(),
                    ]);
            }
        );


        $this->dispatch(
            'notification-preferences-updated',
            message:
                'Tus preferencias de notificaciones se actualizaron correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Seguridad - Nombre legible del dispositivo
    |--------------------------------------------------------------------------
    */

    private function formatDevice(
        ?string $userAgent
    ): string {
        if (! filled($userAgent)) {
            return 'Dispositivo desconocido';
        }


        $userAgent =
            mb_strtolower(
                $userAgent
            );


        /*
         * Sistema operativo.
         */
        $operatingSystem = match (true) {
            str_contains(
                $userAgent,
                'windows'
            ) =>
                'Windows',

            str_contains(
                $userAgent,
                'android'
            ) =>
                'Android',

            str_contains(
                $userAgent,
                'iphone'
            ),
            str_contains(
                $userAgent,
                'ipad'
            ),
            str_contains(
                $userAgent,
                'ipod'
            ) =>
                'iOS',

            str_contains(
                $userAgent,
                'macintosh'
            ),
            str_contains(
                $userAgent,
                'mac os x'
            ) =>
                'macOS',

            str_contains(
                $userAgent,
                'linux'
            ) =>
                'Linux',

            default =>
                'Dispositivo',
        };


        /*
         * Navegador.
         *
         * El orden importa porque Edge, Opera y
         * otros navegadores pueden contener también
         * la palabra Chrome en su User-Agent.
         */
        $browser = match (true) {
            str_contains(
                $userAgent,
                'edg/'
            ),
            str_contains(
                $userAgent,
                'edge/'
            ) =>
                'Edge',

            str_contains(
                $userAgent,
                'opr/'
            ),
            str_contains(
                $userAgent,
                'opera'
            ) =>
                'Opera',

            str_contains(
                $userAgent,
                'samsungbrowser'
            ) =>
                'Samsung Internet',

            str_contains(
                $userAgent,
                'chrome/'
            ),
            str_contains(
                $userAgent,
                'crios/'
            ) =>
                'Chrome',

            str_contains(
                $userAgent,
                'firefox/'
            ),
            str_contains(
                $userAgent,
                'fxios/'
            ) =>
                'Firefox',

            str_contains(
                $userAgent,
                'safari/'
            ) =>
                'Safari',

            default =>
                'Navegador',
        };


        return "{$operatingSystem} · {$browser}";
    }


    /*
    |--------------------------------------------------------------------------
    | Placeholder
    |--------------------------------------------------------------------------
    */

    public function placeholder(): View
    {
        $section = request()->query(
            'section',
            'cuenta'
        );


        if (
            ! is_string($section) ||
            ! in_array(
                $section,
                self::SECTIONS,
                true
            )
        ) {
            $section = 'cuenta';
        }


        return match ($section) {

            'seguridad' =>
                view(
                    'livewire.placeholders.ajustes-seguridad'
                ),

            'notificaciones' =>
                view(
                    'livewire.placeholders.ajustes-notificaciones'
                ),

            default =>
                view(
                    'livewire.placeholders.ajustes-cuenta'
                ),
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(): View
    {
        $usuario = DB::table('usuarios as u')
            ->leftJoin(
                'roles as r',
                'r.id_rol',
                '=',
                'u.id_rol'
            )
            ->leftJoin(
                'estados_usuario as eu',
                'eu.id_estado_usuario',
                '=',
                'u.id_estado_usuario'
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
            ->leftJoin(
                'areas as da',
                'da.id_area',
                '=',
                'd.id_area'
            )
            ->leftJoin(
                'sedes as s',
                's.id_sede',
                '=',
                DB::raw(
                    'COALESCE(a.id_sede, da.id_sede)'
                )
            )
            ->where(
                'u.id_usuario',
                auth()->id()
            )
            ->select([
                'u.id_usuario',
                'u.nombres',
                'u.apellido_paterno',
                'u.apellido_materno',
                'u.username',
                'u.correo',
                'u.telefono',
                'u.numero_colaborador',
                'u.puesto',
                'u.fecha_registro',
                'u.ultimo_acceso',

                'r.nombre as rol_nombre',

                'eu.clave as estado_clave',
                'eu.nombre as estado_nombre',

                'a.nombre as area_nombre',

                'd.nombre as departamento_nombre',

                's.nombre as sede_nombre',
            ])
            ->first();


        abort_if(
            ! $usuario,
            404,
            'No se encontró la cuenta del usuario.'
        );


        /*
         * Nombre completo para la vista.
         */
        $nombreCompleto = trim(
            implode(
                ' ',
                array_filter([
                    $usuario->nombres,
                    $usuario->apellido_paterno,
                    $usuario->apellido_materno,
                ])
            )
        );


        return view(
            'livewire.ajustes-cuenta',
            [
                'usuario' =>
                    $usuario,

                'nombreCompleto' =>
                    $nombreCompleto,
            ]
        );
    }
}