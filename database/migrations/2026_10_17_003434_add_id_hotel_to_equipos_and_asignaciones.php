<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['equipos', 'asignaciones'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->bigInteger('id_hotel')->nullable();

                $table->index('id_hotel');

                $table->foreign('id_hotel')
                    ->references('id_hotel')
                    ->on('hoteles')
                    ->restrictOnDelete();
            });

            /*
             * Recuperar el hotel de los registros existentes:
             *
             * 1. Utilizar el área directamente registrada.
             * 2. Si está vacía, utilizar el área del departamento.
             * 3. Buscar el hotel correspondiente a esa área.
             *
             * Servicios Compartidos no corresponde a un hotel:
             * esos registros conservarán id_hotel = null.
             *
             * Los nombres de tabla proceden únicamente de la
             * lista fija de tablas indicada en este foreach.
             */
            DB::statement(<<<SQL
                UPDATE {$tableName} AS registro
                SET id_hotel = (
                    SELECT hotel.id_hotel
                    FROM hoteles AS hotel
                    WHERE hotel.id_area_origen = COALESCE(
                        registro.id_area,
                        (
                            SELECT departamento.id_area
                            FROM departamentos AS departamento
                            WHERE departamento.id_departamento =
                                registro.id_departamento
                        )
                    )
                )
                WHERE registro.id_hotel IS NULL
                SQL);
        }
    }

    public function down(): void
    {
        foreach (['asignaciones', 'equipos'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['id_hotel']);
                $table->dropIndex(['id_hotel']);
                $table->dropColumn('id_hotel');
            });
        }
    }
};