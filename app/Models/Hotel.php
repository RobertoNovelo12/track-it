<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Hotel extends Model
{
    protected $table = 'hoteles';

    protected $primaryKey = 'id_hotel';

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
            'id_hotel' => 'integer',
            'id_sede' => 'integer',
            'id_area_origen' => 'integer',
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
            'departamento_hotel',
            'id_hotel',
            'id_departamento',
            'id_hotel',
            'id_departamento'
        );
    }
}