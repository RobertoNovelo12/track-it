<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    protected $table = 'departamentos';

    protected $primaryKey = 'id_departamento';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'responsable',
        'correo',
        'extension_telefono',
        'descripcion',
        'activo',
        'atiende_todas_unidades',
    ];

    protected function casts(): array
    {
        return [
            'id_departamento' => 'integer',
            'id_departamento_principal' => 'integer',
            'activo' => 'boolean',
            'atiende_todas_unidades' => 'boolean',
        ];
    }


    /*
     * Permite reconocer el departamento unificado cuando un equipo
     * todavía está relacionado con uno de los registros anteriores.
     */
    public function principal(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'id_departamento_principal',
            'id_departamento'
        );
    }

    public function referenciasAnteriores(): HasMany
    {
        return $this->hasMany(
            self::class,
            'id_departamento_principal',
            'id_departamento'
        );
    }

    /*
     * Hoteles seleccionados explícitamente para este departamento.
     */
    public function hoteles(): BelongsToMany
    {
        return $this->belongsToMany(
            Hotel::class,
            'departamento_unidad',
            'id_departamento',
            'id_unidad',
            'id_departamento',
            'id_unidad'
        );
    }

    /*
     * Excluir los registros anteriores que ya tienen un principal.
     */
    public function scopePrincipales(Builder $query): Builder
    {
        return $query->whereNull(
            'departamentos.id_departamento_principal'
        );
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where(
            'departamentos.activo',
            true
        );
    }

    /*
     * Departamentos activos que atienden:
     * - todos los hoteles, o
     * - específicamente el hotel seleccionado.
     */
    public function scopeDisponiblesParaHotel(
        Builder $query,
        int $hotelId
    ): Builder {
        return $query
            ->principales()
            ->activos()
            ->where(function (Builder $query) use ($hotelId) {
                $query
                    ->where(
                        'departamentos.atiende_todas_unidades',
                        true
                    )
                    ->orWhereHas(
                        'hoteles',
                        function (Builder $hoteles) use ($hotelId) {
                            $hoteles->where(
                                'unidades_organizativas.id_unidad',
                                $hotelId
                            );
                        }
                    );
            });
    }
    public function unidades(): BelongsToMany
    {
        return $this->belongsToMany(UnidadOrganizativa::class, 'departamento_unidad', 'id_departamento', 'id_unidad', 'id_departamento', 'id_unidad');
    }

    public function scopeDisponiblesParaUnidad(Builder $query, int $unidadId): Builder
    {
        return $this->scopeDisponiblesParaHotel($query, $unidadId);
    }
}