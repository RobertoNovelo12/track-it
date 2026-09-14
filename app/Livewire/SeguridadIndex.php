<?php

namespace App\Livewire;

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
    | Sección activa
    |--------------------------------------------------------------------------
    */

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
    }


    /*
    |--------------------------------------------------------------------------
    | Navegación entre secciones
    |--------------------------------------------------------------------------
    */

    public function setSection(string $section): void
    {
        if (! in_array($section, self::SECTIONS, true)) {
            return;
        }

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


        $this->resetValidation();


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
    | Placeholder
    |--------------------------------------------------------------------------
    */

    public function placeholder(): View
    {
        return view(
            'livewire.placeholders.ajustes-cuenta'
        );
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