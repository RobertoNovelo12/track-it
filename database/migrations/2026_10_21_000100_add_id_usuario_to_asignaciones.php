<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Cuenta del colaborador en cada asignación
|--------------------------------------------------------------------------
|
| Hasta ahora el colaborador se guardaba solo como texto
| (nombre_colaborador / numero_colaborador). Con id_usuario cada
| asignación puede apuntar a una cuenta del sistema.
|
| - NULL = colaborador sin cuenta: aparece en Seguridad y roles ›
|   «Colaboradores sin cuenta» para que un administrador lo vincule
|   con un usuario existente o le cree una cuenta.
| - La asignación nunca se bloquea por no tener cuenta.
|
| Vinculación inicial (solo coincidencias seguras, con UN único usuario):
|   1. Número de colaborador igual (sin distinguir mayúsculas/espacios).
|   2. Nombre completo igual: «nombres apellido_paterno apellido_materno»
|      o «nombres apellido_paterno».
| Lo que no coincide queda pendiente.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Nombres propios: la base ya tiene «fk_asignaciones_usuario»
         * (asignado_por → usuarios). Cada paso revisa si ya existe, así
         * la migración puede repetirse sin errores.
         */
        if (! Schema::hasColumn('asignaciones', 'id_usuario')) {
            Schema::table('asignaciones', function (Blueprint $table) {
                $table->bigInteger('id_usuario')->nullable();
            });
        }

        $existeRestriccion = DB::selectOne(
            "SELECT 1 AS existe FROM pg_constraint WHERE conname = 'fk_asignaciones_id_usuario_colaborador'"
        );

        if (! $existeRestriccion) {
            Schema::table('asignaciones', function (Blueprint $table) {
                $table->foreign('id_usuario', 'fk_asignaciones_id_usuario_colaborador')
                    ->references('id_usuario')
                    ->on('usuarios')
                    ->nullOnDelete();
            });
        }

        DB::statement(
            'CREATE INDEX IF NOT EXISTS idx_asignaciones_id_usuario_colaborador ON asignaciones (id_usuario)'
        );

        // 1. Por número de colaborador (único).
        DB::statement(<<<'SQL'
            UPDATE asignaciones a
            SET id_usuario = u.id_usuario
            FROM usuarios u
            WHERE a.id_usuario IS NULL
              AND NULLIF(TRIM(a.numero_colaborador), '') IS NOT NULL
              AND LOWER(TRIM(u.numero_colaborador)) = LOWER(TRIM(a.numero_colaborador))
              AND (
                  SELECT COUNT(*)
                  FROM usuarios u2
                  WHERE LOWER(TRIM(u2.numero_colaborador)) = LOWER(TRIM(a.numero_colaborador))
              ) = 1
        SQL);

        // 2. Por nombre completo (único).
        DB::statement(<<<'SQL'
            WITH claves AS (
                SELECT id_usuario,
                       LOWER(REGEXP_REPLACE(TRIM(CONCAT_WS(' ', nombres, apellido_paterno, apellido_materno)), '\s+', ' ', 'g')) AS clave
                FROM usuarios
                UNION
                SELECT id_usuario,
                       LOWER(REGEXP_REPLACE(TRIM(CONCAT_WS(' ', nombres, apellido_paterno)), '\s+', ' ', 'g')) AS clave
                FROM usuarios
            ),
            unicas AS (
                SELECT clave, MIN(id_usuario) AS id_usuario
                FROM claves
                WHERE clave <> ''
                GROUP BY clave
                HAVING COUNT(DISTINCT id_usuario) = 1
            )
            UPDATE asignaciones a
            SET id_usuario = un.id_usuario
            FROM unicas un
            WHERE a.id_usuario IS NULL
              AND un.clave = LOWER(REGEXP_REPLACE(TRIM(a.nombre_colaborador), '\s+', ' ', 'g'))
        SQL);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('asignaciones', 'id_usuario')) {
            return;
        }

        DB::statement('ALTER TABLE asignaciones DROP CONSTRAINT IF EXISTS fk_asignaciones_id_usuario_colaborador');
        DB::statement('DROP INDEX IF EXISTS idx_asignaciones_id_usuario_colaborador');

        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropColumn('id_usuario');
        });
    }
};
