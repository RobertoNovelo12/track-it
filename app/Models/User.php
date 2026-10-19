<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Tabla real de usuarios.
     */
    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    /**
     * usuarios usa fecha_registro y no
     * created_at / updated_at.
     */
    public $timestamps = false;


    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'username',
        'correo',
        'password_hash',
        'telefono',
        'numero_colaborador',
        'puesto',
        'id_rol',
        'id_estado_usuario',
        'id_area',
        'id_unidad',
        'id_departamento',
    ];


    protected $hidden = [
        'password_hash',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];


    /**
     * Casts del modelo.
     */
    protected function casts(): array
    {
        return [
            'two_factor_secret' => 'encrypted',

            'two_factor_recovery_codes' => 'array',

            'two_factor_confirmed_at' => 'datetime',
        ];
    }


    /**
     * Laravel busca "password" por defecto.
     * Nuestra columna se llama password_hash.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }


    /**
     * El broker de recuperación busca email.
     * Nuestra columna se llama correo.
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }


    /**
     * Dirección para notificaciones por correo.
     */
    public function routeNotificationForMail(
        $notification = null
    ): string {
        return $this->correo;
    }


    /**
     * Determinar si el usuario tiene 2FA
     * completamente configurado.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return filled($this->two_factor_secret)
            && $this->two_factor_confirmed_at !== null;
    }


    /*
    |--------------------------------------------------------------------------
    | Nivel de acceso del rol
    |--------------------------------------------------------------------------
    |
    | admin | estandar | lectura (roles.nivel).
    |
    | - Sin rol o rol sin nivel: lectura (mínimo privilegio).
    | - Mientras no exista la columna roles.nivel (migración pendiente)
    |   se conserva el comportamiento anterior: acceso completo.
    |
    | Se resuelve una sola vez por petición.
    |
    */

    public const NIVEL_ADMIN = 'admin';
    public const NIVEL_ESTANDAR = 'estandar';
    public const NIVEL_LECTURA = 'lectura';

    private ?string $nivelAccesoResuelto = null;

    public function nivelAcceso(): string
    {
        if ($this->nivelAccesoResuelto !== null) {
            return $this->nivelAccesoResuelto;
        }

        static $columnaNivel = null;

        $columnaNivel ??= \Illuminate\Support\Facades\Schema::hasColumn('roles', 'nivel');

        if (! $columnaNivel) {
            return $this->nivelAccesoResuelto = self::NIVEL_ADMIN;
        }

        $nivel = $this->id_rol
            ? \Illuminate\Support\Facades\DB::table('roles')
                ->where('id_rol', $this->id_rol)
                ->where('activo', true)
                ->value('nivel')
            : null;

        return $this->nivelAccesoResuelto = in_array(
            $nivel,
            [self::NIVEL_ADMIN, self::NIVEL_ESTANDAR, self::NIVEL_LECTURA],
            true
        )
            ? $nivel
            : self::NIVEL_LECTURA;
    }

    public function esAdministrador(): bool
    {
        return $this->nivelAcceso() === self::NIVEL_ADMIN;
    }

    /*
     * Puede registrar y modificar la operación (equipos, asignaciones,
     * mantenimientos, reportes y catálogos de equipo).
     */
    public function puedeGestionar(): bool
    {
        return in_array(
            $this->nivelAcceso(),
            [self::NIVEL_ADMIN, self::NIVEL_ESTANDAR],
            true
        );
    }

    public function soloLectura(): bool
    {
        return $this->nivelAcceso() === self::NIVEL_LECTURA;
    }
}