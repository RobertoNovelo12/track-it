<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteGenerado extends Model
{
    protected $table = 'reportes_generados';

    protected $primaryKey = 'id_reporte';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'tipo_reporte',
        'formato',
        'fecha_desde',
        'fecha_hasta',
        'filtros',
        'total_registros',
        'nombre_archivo',
        'ruta_archivo',
    ];

    /*
     * El historial de Reportes se guarda en caché (módulo «reportes»):
     * cualquier alta, cambio o baja de un reporte la renueva.
     */
    protected static function booted(): void
    {
        $renovar = fn () => app(\App\Services\SystemCacheService::class)
            ->refreshModule('reportes');

        static::created($renovar);
        static::updated($renovar);
        static::deleted($renovar);
    }

    protected function casts(): array
    {
        return [
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
            'filtros' => 'array',
            'total_registros' => 'integer',
            'fecha_generacion' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id_usuario'
        );
    }
}