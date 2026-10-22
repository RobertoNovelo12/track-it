<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Tipos de ubicación + alta de ubicaciones y departamentos
|--------------------------------------------------------------------------
|
| - tipos_ubicacion: lista editable (Cocina, Club, Cuarto de
|   comunicaciones…). Un club es una ubicación de tipo «Club»; su
|   personal sigue en su departamento.
| - ubicaciones.id_tipo_ubicacion (opcional).
| - Se permite NULL en ubicaciones.id_sede / id_area (columnas antiguas,
|   se llenan desde el hotel cuando lo hay) y en departamentos.codigo
|   (el sistema lo genera al crear), para poder dar de alta registros
|   desde Administración.
|
*/

return new class extends Migration
{
    private const TIPOS = [
        'Cocina',
        'Oficina',
        'Club',
        'Restaurante',
        'Bar',
        'Cuarto de comunicaciones',
        'Recepción',
        'Bodega',
        'Área pública',
        'Otro',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('tipos_ubicacion')) {
            Schema::create('tipos_ubicacion', function (Blueprint $table) {
                $table->bigIncrements('id_tipo_ubicacion');
                $table->string('nombre', 100)->unique('uq_tipos_ubicacion_nombre');
                $table->integer('orden')->default(0);
                $table->boolean('activo')->default(true);
            });

            foreach (self::TIPOS as $indice => $nombre) {
                DB::table('tipos_ubicacion')->insert([
                    'nombre' => $nombre,
                    'orden' => ($indice + 1) * 10,
                    'activo' => true,
                ]);
            }
        }

        if (! Schema::hasColumn('ubicaciones', 'id_tipo_ubicacion')) {
            Schema::table('ubicaciones', function (Blueprint $table) {
                $table->bigInteger('id_tipo_ubicacion')->nullable();
                $table->index('id_tipo_ubicacion', 'idx_ubicaciones_tipo');
                $table->foreign('id_tipo_ubicacion', 'fk_ubicaciones_tipo_ubicacion')
                    ->references('id_tipo_ubicacion')
                    ->on('tipos_ubicacion')
                    ->nullOnDelete();
            });
        }

        foreach (['id_sede', 'id_area'] as $columna) {
            if (Schema::hasColumn('ubicaciones', $columna)) {
                DB::statement("ALTER TABLE ubicaciones ALTER COLUMN {$columna} DROP NOT NULL");
            }
        }

        if (Schema::hasColumn('departamentos', 'codigo')) {
            DB::statement('ALTER TABLE departamentos ALTER COLUMN codigo DROP NOT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ubicaciones', 'id_tipo_ubicacion')) {
            Schema::table('ubicaciones', function (Blueprint $table) {
                $table->dropForeign('fk_ubicaciones_tipo_ubicacion');
                $table->dropIndex('idx_ubicaciones_tipo');
                $table->dropColumn('id_tipo_ubicacion');
            });
        }

        Schema::dropIfExists('tipos_ubicacion');
    }
};
