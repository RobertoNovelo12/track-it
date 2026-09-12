<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEquipo extends Model
{
    protected $table = 'tipos_equipo';
    protected $primaryKey = 'id_tipo_equipo';
    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    public function modelos()
    {
        return $this->hasMany(Modelo::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }
}