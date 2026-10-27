<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UnidadOrganizativa extends Model
{
    protected $table = 'unidades_organizativas';

    protected $primaryKey = 'id_unidad';

    protected $fillable = [
        'id_sede',
        'codigo',
        'nombre',
        'responsable',
        'correo',
        'extension_telefono',
        'descripcion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'id_unidad' => 'integer',
            'id_sede' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(
            Sede::class,
            'id_sede',
            'id_sede'
        );
    }

    /**
     * Departamentos vinculados específicamente a este hotel.
     */
    public function departamentos(): BelongsToMany
    {
        return $this->belongsToMany(
            Departamento::class,
            'departamento_unidad',
            'id_unidad',
            'id_departamento',
            'id_unidad',
            'id_departamento'
        );
    }
}