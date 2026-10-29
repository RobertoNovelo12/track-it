<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Índices para las consultas más frecuentes
|--------------------------------------------------------------------------
|
| En Postgres una llave foránea NO crea índice. Las pantallas filtran
| a cada rato por estas columnas (historial de un equipo, bandeja de
| recibidos, notificaciones, filtros del inventario…); sin índice, la
| base recorre la tabla completa y se vuelve lenta al crecer.
|
| Solo se crea un índice si la tabla y las columnas existen y si no hay
| ya otro índice que empiece por las mismas columnas. No cambia datos.
|
*/

return new class extends Migration
{
    private const INDICES = [
        'asignaciones' => [['id_equipo', 'fecha_fin'], ['id_usuario'], ['id_unidad'], ['id_departamento'], ['id_ubicacion']],
        'movimientos' => [['id_equipo', 'fecha_hora'], ['id_asignacion_anterior'], ['id_asignacion_nueva']],
        'atributos_equipo' => [['id_equipo'], ['id_campo']],
        'equipos' => [['id_tipo_equipo'], ['id_estado_activo'], ['id_unidad'], ['id_departamento'], ['id_ubicacion'], ['id_marca'], ['id_modelo'], ['id_proveedor']],
        'equipos_recibidos' => [['id_usuario', 'atendido_en'], ['id_equipo']],
        'notificaciones' => [['id_usuario', 'leida']],
        'mantenimientos' => [['id_equipo'], ['id_tecnico'], ['fecha_intervencion']],
        'mantenimiento_refacciones' => [['id_mantenimiento']],
        'bajas' => [['id_equipo']],
        'modelos' => [['id_marca'], ['id_tipo_equipo']],
        'usuarios' => [['id_rol'], ['id_estado_usuario'], ['id_unidad'], ['id_departamento']],
        'departamentos' => [['id_departamento_principal']],
        'departamento_unidad' => [['id_unidad']],
        'departamento_ubicacion' => [['id_ubicacion']],
        'ubicaciones' => [['id_unidad']],
        'equipo_programa' => [['id_programa']],
        'tipo_equipo_programa' => [['id_programa']],
        'tipo_equipo_campos' => [['id_tipo_equipo'], ['id_campo']],
        'tipo_equipo_campo_opciones' => [['id_tipo_campo']],
        'tipo_equipo_secciones' => [['id_tipo_equipo']],
        'catalogo_valores' => [['id_catalogo']],
        'bitacora_auditoria' => [['id_usuario', 'fecha_hora']],
        'reportes_generados' => [['id_usuario']],
        'suscripciones_push' => [['id_usuario']],
        'solicitudes_cambio_organizacion' => [['id_usuario']],
        'solicitudes_restablecimiento' => [['id_usuario']],
        'evidencias' => [['id_baja'], ['id_mantenimiento']],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::INDICES as $tabla => $grupos) {
            if (! Schema::hasTable($tabla)) {
                continue;
            }

            foreach ($grupos as $columnas) {
                foreach ($columnas as $columna) {
                    if (! Schema::hasColumn($tabla, $columna)) {
                        continue 2;
                    }
                }

                if ($this->yaTieneIndice($tabla, $columnas)) {
                    continue;
                }

                $nombre = substr('idx_'.$tabla.'_'.implode('_', $columnas), 0, 63);
                $lista = implode(', ', array_map(fn ($c) => '"'.$c.'"', $columnas));

                DB::statement('CREATE INDEX IF NOT EXISTS "'.$nombre.'" ON public."'.$tabla.'" ('.$lista.')');
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDICES as $tabla => $grupos) {
            foreach ($grupos as $columnas) {
                $nombre = substr('idx_'.$tabla.'_'.implode('_', $columnas), 0, 63);
                DB::statement('DROP INDEX IF EXISTS public."'.$nombre.'"');
            }
        }
    }

    /*
     * ¿Hay un índice (o llave primaria/única) cuyas primeras columnas
     * sean exactamente estas, en el mismo orden?
     */
    private function yaTieneIndice(string $tabla, array $columnas): bool
    {
        $indices = DB::select(<<<'SQL'
            SELECT array_agg(a.attname ORDER BY k.orden) AS columnas
            FROM pg_index i
            JOIN pg_class t ON t.oid = i.indrelid
            JOIN pg_namespace n ON n.oid = t.relnamespace
            CROSS JOIN LATERAL unnest(i.indkey) WITH ORDINALITY AS k(attnum, orden)
            JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = k.attnum
            WHERE n.nspname = 'public' AND t.relname = ?
            GROUP BY i.indexrelid
        SQL, [$tabla]);

        foreach ($indices as $indice) {
            $existentes = array_map('trim', explode(',', trim((string) $indice->columnas, '{}')));

            if (array_slice($existentes, 0, count($columnas)) === $columnas) {
                return true;
            }
        }

        return false;
    }
};
