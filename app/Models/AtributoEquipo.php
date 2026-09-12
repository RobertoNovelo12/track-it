<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtributoEquipo extends Model
{
    protected $table = 'atributos_equipo';
    protected $primaryKey = 'id_atributo';
    public $timestamps = false;

    protected $fillable = ['id_equipo', 'nombre', 'valor'];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipo', 'id_equipo');
    }
}