<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    protected $table = 'asignaciones';
    protected $primaryKey = 'id_asignacion';
    public $timestamps = false;

    protected $fillable = [
        'id_equipo', 'nombre_colaborador', 'numero_colaborador',
        'id_area', 'id_departamento', 'id_ubicacion', 'id_tipo_asignacion',
        'fecha_asignacion', 'fecha_fin', 'observaciones', 'asignado_por',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipo', 'id_equipo');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion', 'id_ubicacion');
    }

    public function tipoAsignacion()
    {
        return $this->belongsTo(CatalogoValor::class, 'id_tipo_asignacion', 'id_valor');
    }

    public function asignadoPor()
    {
        return $this->belongsTo(Usuario::class, 'asignado_por', 'id_usuario');
    }
}