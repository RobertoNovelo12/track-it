<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClasificacionBienService
{
    public const OPCIONES = [
        'global' => 'Bienes muebles y equipos',
        'intangibles' => 'Bienes intangibles',
        'inmuebles' => 'Bienes inmuebles',
        'vehiculos' => 'Vehículos',
    ];

    public function porTipo(): array
    {
        return DB::table('tipos_equipo')->pluck('clasificacion_bien', 'id_tipo_equipo')->all();
    }

    public function validar(string $clasificacion, int $tipoId): void
    {
        if (! isset(self::OPCIONES[$clasificacion]) || ! DB::table('tipos_equipo')
            ->where('id_tipo_equipo', $tipoId)->where('clasificacion_bien', $clasificacion)->exists()) {
            throw ValidationException::withMessages(['clasificacionBien' => 'Selecciona una clasificación y un tipo de bien que corresponda a ella.']);
        }
    }

    public function requeridosBase(int $tipoId): array
    {
        return DB::table('tipo_equipo_campos as tc')->join('campos_equipo as c', 'c.id_campo', '=', 'tc.id_campo')
            ->where('tc.id_tipo_equipo', $tipoId)->whereIn('c.clave', ['id_marca', 'numero_serie', 'numero_factura'])
            ->get(['c.clave', 'tc.obligatorio', 'tc.visible'])->mapWithKeys(fn ($campo) => [$campo->clave => (bool) $campo->visible && (bool) $campo->obligatorio])->all();
    }

    /** Agrega solo lo que falta al constructor existente; nunca reemplaza su configuración. */
    public function completarCampos(int $tipoId, string $clasificacion, array $secciones, bool $ajustarVisibilidad = false): void
    {
        $comunes = [
            'inventario_institucional' => ['Número de inventario institucional', 'text'],
            'costo_unitario' => ['Costo unitario', 'number'],
            'iva' => ['IVA (importe)', 'number'],
            'total' => ['Total', 'number'],
            'partida' => ['Partida', 'text'],
            'procedimiento_adquisicion' => ['Procedimiento de adquisición', 'text'],
            'fuente_financiamiento' => ['Fuente de financiamiento', 'text'],
            'resguardatario' => ['Resguardatario', 'text'],
        ];
        $campos = match ($clasificacion) {
            'global', 'intangibles' => $comunes,
            'vehiculos' => [
                'placas' => ['Placas de circulación', 'text'],
                'anio_vehiculo' => ['Año', 'number'],
                'uso_destino' => ['Uso y destino', 'text'],
                'resguardatario' => ['Resguardatario', 'text'],
            ],
            'inmuebles' => [
                'institucion_responsable' => ['Institución a cargo del inmueble', 'text'],
                'domicilio_inmueble' => ['Domicilio del inmueble', 'textarea'],
                'tipo_posesion' => ['Tipo de posesión', 'text'],
                'titulo_propiedad' => ['Título de propiedad o posesión', 'text'],
                'uso_destino' => ['Uso del inmueble', 'text'],
            ],
            default => [],
        };
        $orden = (int) DB::table('tipo_equipo_campos')->where('id_tipo_equipo', $tipoId)->max('orden');
        $ids = [];
        foreach ($campos as $clave => [$nombre, $control]) {
            // Reutilizar también los identificadores institucionales instalados anteriormente.
            $campo = DB::table('campos_equipo')->whereIn('clave', [$clave, 'institucion_'.$clave])->orderBy('id_campo')->first();
            $id = $campo?->id_campo ?? DB::table('campos_equipo')->insertGetId([
                'clave' => 'institucion_'.$clave, 'nombre' => $nombre, 'tipo_control' => $control,
                'es_sistema' => true, 'activo' => true,
            ], 'id_campo');
            $ids[] = $id;
            $this->agregarRelacion($tipoId, (int) $id, [
                'etiqueta' => $nombre, 'origen_dato' => 'atributo',
                'tabla_destino' => null, 'columna_destino' => null,
                'id_seccion' => $secciones['informacion_tipo'], 'seccion' => 'especifico',
                'orden' => $orden += 10,
            ]);
        }

        if ($ajustarVisibilidad) {
            $clavesReporte = array_unique(array_merge(array_keys($comunes), ['placas', 'anio_vehiculo', 'uso_destino', 'institucion_responsable', 'domicilio_inmueble', 'tipo_posesion', 'titulo_propiedad']));
            $todos = DB::table('campos_equipo')->whereIn('clave', array_merge($clavesReporte, array_map(fn ($clave) => 'institucion_'.$clave, $clavesReporte)))->pluck('id_campo');
            DB::table('tipo_equipo_campos')->where('id_tipo_equipo', $tipoId)->whereIn('id_campo', $todos)->whereNotIn('id_campo', $ids)->update(['visible' => false]);
            DB::table('tipo_equipo_campos')->where('id_tipo_equipo', $tipoId)->whereIn('id_campo', $ids)->update(['visible' => true]);
            $opcionales = match ($clasificacion) {
                'inmuebles', 'intangibles' => ['id_marca', 'numero_serie', 'numero_factura'],
                'vehiculos' => ['numero_factura'],
                default => [],
            };
            DB::table('tipo_equipo_campos')->where('id_tipo_equipo', $tipoId)
                ->whereIn('id_campo', DB::table('campos_equipo')->whereIn('clave', $opcionales)->select('id_campo'))
                ->update(['obligatorio' => false]);
        }

        // Estas columnas ya existen en equipos: no duplicarlas como atributos.
        $base = match ($clasificacion) {
            'global', 'intangibles' => ['id_modelo', 'fecha_compra', 'id_proveedor', 'id_departamento'],
            'vehiculos' => ['id_modelo', 'id_departamento'],
            default => [],
        };
        foreach (DB::table('campos_equipo')->whereIn('clave', $base)->get() as $campo) {
            $this->agregarRelacion($tipoId, $campo->id_campo, [
                'etiqueta' => $campo->nombre, 'origen_dato' => 'equipos',
                'tabla_destino' => 'equipos', 'columna_destino' => $campo->clave,
                'id_seccion' => $secciones['general'], 'seccion' => 'general',
                'orden' => $orden += 10,
                'fuente_opciones' => match ($campo->clave) {
                    'id_modelo' => 'modelos', 'id_proveedor' => 'proveedores',
                    'id_departamento' => 'departamentos', default => null,
                },
            ]);
        }
    }

    private function agregarRelacion(int $tipoId, int $campoId, array $datos): void
    {
        if (! DB::table('tipo_equipo_campos')->where('id_tipo_equipo', $tipoId)->where('id_campo', $campoId)->exists()) {
            DB::table('tipo_equipo_campos')->insert($datos + [
                'id_tipo_equipo' => $tipoId, 'id_campo' => $campoId,
                'visible' => true, 'obligatorio' => false,
            ]);
        }
    }
}
