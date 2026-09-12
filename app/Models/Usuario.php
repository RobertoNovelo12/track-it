<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'id_rol', 'id_estado_usuario', 'nombres', 'apellido_paterno',
        'apellido_materno', 'username', 'correo', 'password_hash',
        'telefono', 'numero_colaborador', 'puesto', 'id_area',
        'id_departamento',
    ];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'fecha_registro' => 'datetime',
        'ultimo_acceso' => 'datetime',
    ];

    /**
     * Laravel espera el hash en el campo "password" por defecto;
     * aquí le decimos que use "password_hash" en su lugar.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function estado()
    {
        return $this->belongsTo(CatalogoValor::class, 'id_estado_usuario', 'id_valor');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }
}