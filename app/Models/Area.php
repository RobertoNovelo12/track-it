<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';
    protected $primaryKey = 'id_area';
    public $timestamps = false;

    protected $fillable = [
        'id_sede', 'codigo', 'nombre', 'responsable',
        'correo', 'extension_telefono', 'descripcion', 'activo',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede', 'id_sede');
    }

    public function departamentos()
    {
        return $this->hasMany(Departamento::class, 'id_area', 'id_area');
    }
}