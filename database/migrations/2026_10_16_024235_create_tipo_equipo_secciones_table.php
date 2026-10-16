<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_equipo_secciones', function (Blueprint $table) {
            $table->bigIncrements('id_seccion');

            $table->smallInteger('id_tipo_equipo');

            /*
            |--------------------------------------------------------------------------
            | Clave interna
            |--------------------------------------------------------------------------
            |
            | Nos permitirá identificar secciones sin depender del nombre visible.
            |
            | Ejemplos:
            |
            | general
            | informacion_tipo
            | adicional
            | custom_xxxxx
            |
            */

            $table->string('clave', 120);

            /*
            |--------------------------------------------------------------------------
            | Nombre visible
            |--------------------------------------------------------------------------
            |
            | Ejemplos:
            |
            | Información general
            | Información de Laptop
            | Información adicional
            | Conectividad
            | Accesorios
            |
            */

            $table->string('nombre', 150);

            /*
            |--------------------------------------------------------------------------
            | Orden
            |--------------------------------------------------------------------------
            |
            | 10, 20, 30...
            |
            | Dejamos espacios para poder reordenar fácilmente.
            |
            */

            $table->smallInteger('orden')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Sección base
            |--------------------------------------------------------------------------
            |
            | Las tres secciones creadas inicialmente para cada tipo de equipo
            | podrán identificarse aunque después cambie su nombre visible.
            |
            */

            $table->boolean('es_base')->default(false);

            $table
                ->foreign('id_tipo_equipo')
                ->references('id_tipo_equipo')
                ->on('tipos_equipo')
                ->cascadeOnDelete();

            $table->unique(
                [
                    'id_tipo_equipo',
                    'clave',
                ],
                'uq_tipo_equipo_seccion_clave'
            );

            $table->index(
                [
                    'id_tipo_equipo',
                    'orden',
                ],
                'idx_tipo_equipo_seccion_orden'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'tipo_equipo_secciones'
        );
    }
};