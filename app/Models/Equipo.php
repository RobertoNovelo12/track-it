<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    protected $table = 'equipos';
    protected $primaryKey = 'id_equipo';

    // La tabla no usa created_at/updated_at: tiene fecha_registro (creación)
    // y fecha_actualizacion (último guardado desde «Editar equipo»).
    public $timestamps = false;

    protected $fillable = [
        'id_unidad',
        'codigo_inventario',
        'nombre_equipo',
        'id_tipo_equipo',
        'id_marca',
        'id_modelo',
        'numero_serie',
        'service_tag',
        'host',
        'direccion_mac',
        'direccion_ip',
        'id_estado_activo',
        'id_condicion_activo',
        'id_departamento',
        'id_ubicacion',
        'id_proveedor',
        'fecha_compra',
        'numero_factura',
        'fecha_fin_garantia',
        'comentarios',
        'registrado_por',
    ];

    protected $casts = [
        'fecha_compra' => 'date',
        'fecha_fin_garantia' => 'date',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    // ------------------------------------------------------------
    // Relaciones
    // ------------------------------------------------------------

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'id_modelo', 'id_modelo');
    }

    public function estadoActivo()
    {
        return $this->belongsTo(CatalogoValor::class, 'id_estado_activo', 'id_valor');
    }

    public function condicionActivo()
    {
        return $this->belongsTo(CatalogoValor::class, 'id_condicion_activo', 'id_valor');
    }


    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion', 'id_ubicacion');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }

    public function registradoPor()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por', 'id_usuario');
    }

    public function atributos()
    {
        return $this->hasMany(AtributoEquipo::class, 'id_equipo', 'id_equipo');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'id_equipo', 'id_equipo');
    }

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'id_equipo', 'id_equipo');
    }

    // ------------------------------------------------------------
    // Accesores de conveniencia
    // ------------------------------------------------------------

    /** Hotel y departamento actuales, sin depender de la organización anterior. */
    public function getUbicacionOrganizacionalAttribute(): string
    {
        $hotel = $this->attributes['unidad_nombre'] ?? $this->attributes['hotel_nombre'] ?? $this->unidad?->nombre;
        $departamento = $this->attributes['departamento_nombre'] ?? $this->departamento?->nombre;

        return collect([$hotel, $departamento])->filter()->implode(' / ') ?: '—';
    }
    public function unidad(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UnidadOrganizativa::class, 'id_unidad', 'id_unidad');
    }
}