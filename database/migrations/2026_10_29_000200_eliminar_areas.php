<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Eliminar «Áreas» de la base de datos
|--------------------------------------------------------------------------
|
| La organización ahora es Hotel (unidades_organizativas) → Departamento
| → Ubicación. La tabla «areas» y las columnas que apuntaban a ella ya no
| se usan en el código.
|
| Antes de borrar se guarda un respaldo en respaldo_transicion_hoteles:
|   - 'areas'            → todas las filas de la tabla
|   - '<tabla>.<columna>' → [{ id, valor }] de cada columna eliminada
| Así, si algo hiciera falta, los datos siguen disponibles.
|
| Todo corre en una transacción: si algo falla, no se borra nada.
|
*/

return new class extends Migration
{
    /*
     * tabla => [llave primaria, columna a eliminar]
     */
    private const COLUMNAS = [
        ['departamentos', 'id_departamento', 'id_area'],
        ['ubicaciones', 'id_ubicacion', 'id_area'],
        ['modelos', 'id_modelo', 'id_area_uso_comun'],
        ['usuarios', 'id_usuario', 'id_area'],
        ['equipos', 'id_equipo', 'id_area'],
        ['asignaciones', 'id_asignacion', 'id_area'],
        ['unidades_organizativas', 'id_unidad', 'id_area_origen'],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! Schema::hasTable('areas')) {
            return;
        }

        DB::transaction(function (): void {
            $respaldo = Schema::hasTable('respaldo_transicion_hoteles');

            // 1. Respaldo de la tabla y de cada columna.
            if ($respaldo) {
                DB::statement(<<<'SQL'
                    INSERT INTO respaldo_transicion_hoteles (tabla, datos, creado_en)
                    SELECT 'areas', COALESCE(jsonb_agg(to_jsonb(a)), '[]'::jsonb), now()
                    FROM areas a
                    ON CONFLICT (tabla) DO NOTHING
                SQL);
            }

            foreach (self::COLUMNAS as [$tabla, $llave, $columna]) {
                if (! Schema::hasTable($tabla) || ! Schema::hasColumn($tabla, $columna)) {
                    continue;
                }

                if ($respaldo) {
                    DB::statement(
                        'INSERT INTO respaldo_transicion_hoteles (tabla, datos, creado_en) '
                        .'SELECT ?, COALESCE(jsonb_agg(jsonb_build_object(\'id\', "'.$llave.'", \'valor\', "'.$columna.'")), \'[]\'::jsonb), now() '
                        .'FROM public."'.$tabla.'" WHERE "'.$columna.'" IS NOT NULL '
                        .'ON CONFLICT (tabla) DO NOTHING',
                        [$tabla.'.'.$columna]
                    );
                }
            }

            // 2. Llaves foráneas que apuntan a «areas» (en cualquier tabla).
            $llaves = DB::select(<<<'SQL'
                SELECT c.conname, t.relname AS tabla
                FROM pg_constraint c
                JOIN pg_class t ON t.oid = c.conrelid
                JOIN pg_namespace n ON n.oid = t.relnamespace
                WHERE c.contype = 'f'
                  AND c.confrelid = 'public.areas'::regclass
                  AND n.nspname = 'public'
            SQL);

            foreach ($llaves as $llave) {
                DB::statement('ALTER TABLE public."'.$llave->tabla.'" DROP CONSTRAINT IF EXISTS "'.$llave->conname.'"');
            }

            // 3. Columnas (sus índices y restricciones se van con ellas).
            foreach (self::COLUMNAS as [$tabla, , $columna]) {
                if (Schema::hasTable($tabla)) {
                    DB::statement('ALTER TABLE public."'.$tabla.'" DROP COLUMN IF EXISTS "'.$columna.'"');
                }
            }

            // 4. La tabla.
            DB::statement('DROP TABLE IF EXISTS public.areas');
        });
    }

    public function down(): void
    {
        // Sin vuelta atrás automática: los datos quedan en respaldo_transicion_hoteles.
    }
};
