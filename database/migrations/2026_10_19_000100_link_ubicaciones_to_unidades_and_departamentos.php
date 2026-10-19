<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Ubicaciones por hotel y por departamento
|--------------------------------------------------------------------------
|
| Antes las ubicaciones sólo se relacionaban con sede y área. El área
| ya no se usa: cada hotel (unidades_organizativas) conserva en
| id_area_origen el área de la que proviene.
|
| 1. ubicaciones.id_unidad
|    Hotel al que pertenece la ubicación. Se obtiene del área de origen
|    y, si no hay coincidencia, del hotel con el que más se ha usado en
|    asignaciones. NULL = disponible en cualquier hotel.
|
| 2. departamento_ubicacion
|    Departamentos que trabajan en cada ubicación. Se llena con las
|    combinaciones ya registradas en asignaciones (usando el
|    departamento principal tras la unificación de departamentos).
|    Una ubicación sin departamentos queda disponible para todos los
|    departamentos de su hotel.
|
| No se elimina ni modifica ninguna columna existente (id_sede e
| id_area se conservan por compatibilidad con reportes anteriores).
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ubicaciones', 'id_unidad')) {
            Schema::table('ubicaciones', function (Blueprint $table) {
                $table->bigInteger('id_unidad')->nullable();
                $table->index('id_unidad');
                $table->foreign('id_unidad')
                    ->references('id_unidad')
                    ->on('unidades_organizativas')
                    ->restrictOnDelete();
            });
        }

        if (! Schema::hasTable('departamento_ubicacion')) {
            Schema::create('departamento_ubicacion', function (Blueprint $table) {
                $table->bigInteger('id_departamento');
                $table->bigInteger('id_ubicacion');

                $table->primary(['id_departamento', 'id_ubicacion']);
                $table->index('id_ubicacion');

                $table->foreign('id_departamento')
                    ->references('id_departamento')
                    ->on('departamentos')
                    ->cascadeOnDelete();

                $table->foreign('id_ubicacion')
                    ->references('id_ubicacion')
                    ->on('ubicaciones')
                    ->cascadeOnDelete();
            });
        }

        /*
         * 1a. Hotel desde el área de origen.
         */
        DB::statement(<<<'SQL'
            UPDATE ubicaciones AS ub
            SET id_unidad = (
                SELECT u.id_unidad
                FROM unidades_organizativas AS u
                WHERE u.id_area_origen = ub.id_area
                ORDER BY u.id_unidad
                LIMIT 1
            )
            WHERE ub.id_unidad IS NULL
              AND ub.id_area IS NOT NULL
            SQL);

        /*
         * 1b. Hotel con el que más se ha usado la ubicación.
         */
        DB::statement(<<<'SQL'
            UPDATE ubicaciones AS ub
            SET id_unidad = (
                SELECT a.id_unidad
                FROM asignaciones AS a
                WHERE a.id_ubicacion = ub.id_ubicacion
                  AND a.id_unidad IS NOT NULL
                GROUP BY a.id_unidad
                ORDER BY COUNT(*) DESC, a.id_unidad
                LIMIT 1
            )
            WHERE ub.id_unidad IS NULL
            SQL);

        /*
         * 2. Departamentos según el historial de asignaciones.
         */
        DB::statement(<<<'SQL'
            INSERT INTO departamento_ubicacion (id_departamento, id_ubicacion)
            SELECT DISTINCT
                COALESCE(d.id_departamento_principal, d.id_departamento),
                a.id_ubicacion
            FROM asignaciones AS a
            JOIN departamentos AS d
                ON d.id_departamento = a.id_departamento
            JOIN ubicaciones AS ub
                ON ub.id_ubicacion = a.id_ubicacion
            WHERE a.id_ubicacion IS NOT NULL
            ON CONFLICT DO NOTHING
            SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('departamento_ubicacion');

        if (Schema::hasColumn('ubicaciones', 'id_unidad')) {
            Schema::table('ubicaciones', function (Blueprint $table) {
                $table->dropForeign(['id_unidad']);
                $table->dropIndex(['id_unidad']);
                $table->dropColumn('id_unidad');
            });
        }
    }
};
