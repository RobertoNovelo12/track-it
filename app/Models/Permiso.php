<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';
    protected $primaryKey = 'id_permiso';
    public $timestamps = false;

    protected $fillable = ['clave', 'nombre', 'modulo', 'descripcion', 'activo'];

    public function roles()
    {
        return $this->belongsToMany(
            Rol::class,
            'roles_permisos',
            'id_permiso',
            'id_rol'
        );
    }
}