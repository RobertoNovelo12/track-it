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
}