<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    protected $table = 'sedes';
    protected $primaryKey = 'id_sede';
    public $timestamps = false;

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'activo'];

    public function areas()
    {
        return $this->hasMany(Area::class, 'id_sede', 'id_sede');
    }
}