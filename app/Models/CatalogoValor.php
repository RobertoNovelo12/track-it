<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoValor extends Model
{
    protected $table = 'catalogo_valores';
    protected $primaryKey = 'id_valor';
    public $timestamps = false;

    protected $fillable = ['id_catalogo', 'clave', 'nombre', 'descripcion', 'orden', 'activo'];

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class, 'id_catalogo', 'id_catalogo');
    }

    /**
     * Scope para traer los valores activos de un catálogo por su "clave"
     * (por ejemplo: CatalogoValor::deCatalogo('estado_activo')->get()).
     *
     * Ajusta las claves usadas aquí a las que realmente tengas
     * sembradas en tu tabla `catalogos` (columna `clave`).
     */
    public function scopeDeCatalogo($query, string $claveCatalogo)
    {
        return $query->whereHas('catalogo', function ($q) use ($claveCatalogo) {
            $q->where('clave', $claveCatalogo);
        })->where('activo', true)->orderBy('orden');
    }
}