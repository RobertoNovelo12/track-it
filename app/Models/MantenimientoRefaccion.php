<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MantenimientoRefaccion extends Model
{
    protected $table = 'mantenimiento_refacciones';
    protected $primaryKey = 'id_mantenimiento_refaccion';
    public $timestamps = false;

    protected $fillable = ['id_mantenimiento', 'nombre_refaccion', 'codigo_parte', 'cantidad', 'unidad'];

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class, 'id_mantenimiento', 'id_mantenimiento');
    }
}