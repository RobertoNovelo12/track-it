<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Tu tabla real de usuarios (no la 'users' que crea Laravel por defecto).
     */
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    /**
     * 'usuarios' usa fecha_registro, no created_at/updated_at.
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
    ];

    /**
     * Laravel busca "password" por defecto; tu columna se llama password_hash.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * El broker de reseteo de contraseña busca "email"; tu columna se llama correo.
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }

    /**
     * A dónde se envían las notificaciones por correo (reset de contraseña, etc.).
     */
    public function routeNotificationForMail($notification = null): string
    {
        return $this->correo;
    }
}