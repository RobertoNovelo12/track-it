<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    protected $table = 'mantenimientos';
    protected $primaryKey = 'id_mantenimiento';
    public $timestamps = false;

    protected $fillable = [
        'folio', 'id_equipo', 'id_tipo_mantenimiento', 'id_tipo_intervencion',
        'id_estado_mantenimiento', 'id_prioridad', 'id_tecnico',
        'fecha_intervencion', 'hora_inicio', 'hora_fin', 'falla_reportada',
        'diagnostico', 'intervencion_realizada', 'observaciones',
        'fecha_proximo_mantenimiento',
    ];

    protected $casts = [
        'fecha_intervencion' => 'date',
        'fecha_proximo_mantenimiento' => 'date',
        'fecha_registro' => 'datetime',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipo', 'id_equipo');
    }

    public function tipoMantenimiento()
    {
        return $this->belongsTo(CatalogoValor::class, 'id_tipo_mantenimiento', 'id_valor');
    }

    public function tipoIntervencion()
    {
        return $this->belongsTo(CatalogoValor::class, 'id_tipo_intervencion', 'id_valor');
    }

    public function estadoMantenimiento()
    {
        return $this->belongsTo(CatalogoValor::class, 'id_estado_mantenimiento', 'id_valor');
    }

    public function prioridad()
    {
        return $this->belongsTo(CatalogoValor::class, 'id_prioridad', 'id_valor');
    }

    public function tecnico()
    {
        return $this->belongsTo(Usuario::class, 'id_tecnico', 'id_usuario');
    }

    public function refacciones()
    {
        return $this->hasMany(MantenimientoRefaccion::class, 'id_mantenimiento', 'id_mantenimiento');
    }
}