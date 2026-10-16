<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ETIQUETA PERSONALIZADA POR TIPO DE EQUIPO
        |--------------------------------------------------------------------------
        |
        | El nombre maestro del campo seguirá viviendo en campos_equipo.
        |
        | Esta columna permite cambiar solamente el texto mostrado dentro
        | de un tipo de equipo.
        |
        | Ejemplo:
        |
        | campos_equipo:
        |   numero_serie => "Número de serie"
        |
        | Monitor:
        |   etiqueta => "SN"
        |
        | Laptop:
        |   etiqueta => NULL
        |
        | Resultado:
        |
        | Monitor muestra "SN".
        | Laptop continúa mostrando "Número de serie".
        |
        */

        Schema::table(
            'tipo_equipo_campos',
            function (Blueprint $table): void {
                $table->string(
                    'etiqueta',
                    150
                )->nullable();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | OPCIONES DE LISTAS PERSONALIZADAS
        |--------------------------------------------------------------------------
        |
        | Cada opción pertenece directamente a una configuración concreta
        | de campo dentro de un tipo de equipo.
        |
        | Por lo tanto:
        |
        | Access Point
        |   Zona:
        |       Interior
        |       Exterior
        |
        | Monitor
        |   Ubicación:
        |       Lobby
        |       Oficina
        |
        | son completamente independientes.
        |
        */

        Schema::create(
            'tipo_equipo_campo_opciones',
            function (Blueprint $table): void {

                /*
                |--------------------------------------------------------------------------
                | Identificador
                |--------------------------------------------------------------------------
                */

                $table->bigIncrements(
                    'id_opcion'
                );


                /*
                |--------------------------------------------------------------------------
                | Campo configurado
                |--------------------------------------------------------------------------
                */

                $table->unsignedBigInteger(
                    'id_tipo_campo'
                );


                /*
                |--------------------------------------------------------------------------
                | Texto visible de la opción
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'nombre',
                    150
                );


                /*
                |--------------------------------------------------------------------------
                | Orden dentro de la lista
                |--------------------------------------------------------------------------
                |
                | También podremos reordenar estas opciones posteriormente.
                |
                */

                $table->smallInteger(
                    'orden'
                )->default(0);


                /*
                |--------------------------------------------------------------------------
                | Estado
                |--------------------------------------------------------------------------
                |
                | No necesitaremos borrar físicamente una opción para dejar
                | de utilizarla.
                |
                */

                $table->boolean(
                    'activo'
                )->default(true);


                /*
                |--------------------------------------------------------------------------
                | Evitar duplicados dentro de la misma lista
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'id_tipo_campo',
                        'nombre',
                    ],
                    'tipo_equipo_campo_opciones_campo_nombre_unique'
                );


                /*
                |--------------------------------------------------------------------------
                | Índice principal de lectura
                |--------------------------------------------------------------------------
                |
                | La consulta habitual será:
                |
                | WHERE id_tipo_campo = ?
                | AND activo = true
                | ORDER BY orden
                |
                */

                $table->index(
                    [
                        'id_tipo_campo',
                        'activo',
                        'orden',
                    ],
                    'tipo_equipo_campo_opciones_campo_activo_orden_index'
                );


                /*
                |--------------------------------------------------------------------------
                | Relación con el campo del formulario
                |--------------------------------------------------------------------------
                |
                | Si alguna vez se elimina completamente la configuración del
                | campo para ese tipo, sus opciones personalizadas también
                | deben desaparecer.
                |
                */

                $table->foreign(
                    'id_tipo_campo',
                    'tipo_equipo_campo_opciones_tipo_campo_foreign'
                )
                    ->references(
                        'id_tipo_campo'
                    )
                    ->on(
                        'tipo_equipo_campos'
                    )
                    ->onUpdate(
                        'cascade'
                    )
                    ->onDelete(
                        'cascade'
                    );
            }
        );
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Primero quitamos la tabla dependiente
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists(
            'tipo_equipo_campo_opciones'
        );


        /*
        |--------------------------------------------------------------------------
        | Después retiramos la etiqueta personalizada
        |--------------------------------------------------------------------------
        */

        Schema::table(
            'tipo_equipo_campos',
            function (Blueprint $table): void {
                $table->dropColumn(
                    'etiqueta'
                );
            }
        );
    }
};