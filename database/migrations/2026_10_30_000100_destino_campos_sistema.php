<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Destino propio de los campos del sistema
|--------------------------------------------------------------------------
|
| Al agregar un campo de «Campos disponibles» a un tipo de equipo, su
| destino (columna de equipos, fuente de opciones) se copiaba de otro
| tipo que ya lo usara. Sin ningún tipo (catálogo reiniciado), el campo
| se guardaba como atributo suelto y dejaba de llenar su columna.
|
| campos_equipo.destino guarda ese destino en el propio campo:
| 1. Se copia de los tipos actuales (antes de reiniciar el catálogo).
| 2. Si ningún tipo lo usa, se toma de la lista conocida de abajo.
|
| TipoEquipoFormBuilderService::loadReferenceMetadata() lo usa cuando
| ningún tipo tiene el campo.
|
*/

return new class extends Migration
{
    /*
     * Campos del sistema que van a una columna de equipos y no dependen
     * de un catálogo editable. Solo se usan si ningún tipo los tiene.
     */
    private const CONOCIDOS = [
        'nombre_equipo' => [null, 'general'],
        'numero_serie' => [null, 'general'],
        'numero_factura' => [null, 'general'],
        'host' => [null, 'general'],
        'direccion_mac' => [null, 'general'],
        'fecha_compra' => [null, 'adicional'],
        'fecha_fin_garantia' => [null, 'adicional'],
        'comentarios' => [null, 'adicional'],
        'id_estado_activo' => ['estados_equipo', 'general'],
        'id_marca' => ['marcas', 'general'],
        'id_modelo' => ['modelos', 'general'],
        'id_proveedor' => ['proveedores', 'adicional'],
        'id_unidad' => ['unidades_organizativas', 'adicional'],
        'id_departamento' => ['departamentos', 'adicional'],
        'id_ubicacion' => ['ubicaciones', 'adicional'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('campos_equipo')) {
            return;
        }

        if (! Schema::hasColumn('campos_equipo', 'destino')) {
            Schema::table('campos_equipo', function (Blueprint $tabla): void {
                $tabla->jsonb('destino')->nullable();
            });
        }

        $campos = DB::table('campos_equipo')
            ->where('es_sistema', true)
            ->get(['id_campo', 'clave']);

        $relaciones = Schema::hasTable('tipo_equipo_campos')
            ? DB::table('tipo_equipo_campos')
                ->whereIn('id_campo', $campos->pluck('id_campo'))
                // Solo destinos que no dependen de un tipo concreto.
                ->whereIn('origen_dato', ['equipos', 'asignacion'])
                ->get(['id_campo', 'origen_dato', 'tabla_destino', 'columna_destino', 'fuente_opciones', 'id_catalogo', 'seccion'])
                ->groupBy('id_campo')
            : collect();

        foreach ($campos as $campo) {
            $destino = null;

            // 1. El que ya usan los tipos (prioridad: columna de equipos).
            $fila = ($relaciones->get($campo->id_campo) ?? collect())
                ->sortBy(fn ($r) => $r->origen_dato === 'equipos' ? 0 : 1)
                ->first();

            if ($fila) {
                $destino = [
                    'origen_dato' => $fila->origen_dato,
                    'tabla_destino' => $fila->tabla_destino,
                    'columna_destino' => $fila->columna_destino,
                    'fuente_opciones' => $fila->fuente_opciones,
                    'id_catalogo' => $fila->id_catalogo,
                    'seccion' => $fila->seccion ?: 'adicional',
                ];
            } elseif (isset(self::CONOCIDOS[$campo->clave])) {
                // 2. Lista conocida.
                [$fuente, $seccion] = self::CONOCIDOS[$campo->clave];

                $destino = [
                    'origen_dato' => 'equipos',
                    'tabla_destino' => 'equipos',
                    'columna_destino' => $campo->clave,
                    'fuente_opciones' => $fuente,
                    'id_catalogo' => null,
                    'seccion' => $seccion,
                ];
            }

            if ($destino !== null) {
                DB::table('campos_equipo')
                    ->where('id_campo', $campo->id_campo)
                    ->update(['destino' => json_encode($destino, JSON_UNESCAPED_UNICODE)]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('campos_equipo') && Schema::hasColumn('campos_equipo', 'destino')) {
            Schema::table('campos_equipo', function (Blueprint $tabla): void {
                $tabla->dropColumn('destino');
            });
        }
    }
};
