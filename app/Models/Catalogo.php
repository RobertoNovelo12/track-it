<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    protected $table = 'catalogos';
    protected $primaryKey = 'id_catalogo';
    public $timestamps = false;

    protected $fillable = ['clave', 'nombre', 'descripcion'];

    public function valores()
    {
        return $this->hasMany(CatalogoValor::class, 'id_catalogo', 'id_catalogo');
    }
}