<?php

namespace App\Livewire;

use Livewire\Attributes\Url;
use App\Services\TwoFactorAuthService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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