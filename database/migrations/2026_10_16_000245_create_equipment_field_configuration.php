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
        | CATÁLOGO MAESTRO DE CAMPOS DE EQUIPO
        |--------------------------------------------------------------------------
        |
        | Aquí existirán una sola vez los campos disponibles para construir
        | formularios:
        |
        | - nombre_equipo
        | - numero_serie
        | - host
        | - direccion_ip
        | - service_tag
        | - fecha_compra
        | - numero_factura
        | - comentarios
        | - ram_gb
        | - procesador
        | - imei_1
        | - etc.
        |
        | Que un campo exista aquí NO significa que todos los tipos de equipo
        | tengan que utilizarlo.
        |
        */

        Schema::create('campos_equipo', function (Blueprint $table) {
            $table->bigIncrements('id_campo');

            $table->string(
                'clave',
                120
            )->unique();

            $table->string(
                'nombre',
                150
            );

            $table->string(
                'tipo_control',
                30
            );

            $table->string(
                'descripcion',
                255
            )->nullable();

            $table->string(
                'placeholder',
                150
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | es_sistema
            |--------------------------------------------------------------------------
            |
            | true:
            | Campo propio del sistema y conocido por el código.
            |
            | false:
            | Campo personalizado creado posteriormente desde Administración.
            |
            */

            $table->boolean(
                'es_sistema'
            )->default(false);

            $table->boolean(
                'activo'
            )->default(true);


            $table->index(
                [
                    'activo',
                    'nombre',
                ],
                'campos_equipo_activo_nombre_index'
            );
        });


        /*
        |--------------------------------------------------------------------------
        | CONFIGURACIÓN DE CAMPOS POR TIPO DE EQUIPO
        |--------------------------------------------------------------------------
        |
        | Esta tabla es la que permitirá que:
        |
        | Monitor:
        | - Nombre
        | - SN obligatorio
        | - Host opcional
        |
        | Escáner:
        | - Nombre
        | - Host
        | - SIN número de serie
        |
        | Laptop:
        | - Nombre
        | - SN
        | - Service Tag
        |
        | Quitar una asociación de esta tabla solamente afecta al tipo de
        | equipo correspondiente.
        |
        */

        Schema::create('tipo_equipo_campos', function (Blueprint $table) {
            $table->bigIncrements(
                'id_tipo_campo'
            );


            /*
            |--------------------------------------------------------------------------
            | Tipo de equipo
            |--------------------------------------------------------------------------
            */

            $table->smallInteger(
                'id_tipo_equipo'
            );


            /*
            |--------------------------------------------------------------------------
            | Campo maestro
            |--------------------------------------------------------------------------
            */

            $table->bigInteger(
                'id_campo'
            );


            /*
            |--------------------------------------------------------------------------
            | ORIGEN DEL DATO
            |--------------------------------------------------------------------------
            |
            | Valores que utilizaremos:
            |
            | equipos
            | tabla_tipo
            | atributo
            | asignacion
            |
            */

            $table->string(
                'origen_dato',
                30
            );


            /*
            |--------------------------------------------------------------------------
            | DESTINO FÍSICO
            |--------------------------------------------------------------------------
            |
            | Ejemplos:
            |
            | equipos / numero_serie
            | equipos / host
            | laptops / ram_gb
            | monitores / tamano_pulgadas
            |
            | Para campos dinámicos almacenados en atributos_equipo pueden
            | permanecer NULL.
            |
            */

            $table->string(
                'tabla_destino',
                100
            )->nullable();

            $table->string(
                'columna_destino',
                120
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | FUENTE DE OPCIONES
            |--------------------------------------------------------------------------
            |
            | Sólo aplica cuando el campo sea tipo select.
            |
            | Ejemplos futuros:
            |
            | estados_equipo
            | marcas
            | modelos
            | areas
            | departamentos
            | proveedores
            | catalogo
            |
            */

            $table->string(
                'fuente_opciones',
                50
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | Catálogo asociado
            |--------------------------------------------------------------------------
            |
            | Se utiliza cuando fuente_opciones = catalogo.
            |
            */

            $table->smallInteger(
                'id_catalogo'
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | Sección visual
            |--------------------------------------------------------------------------
            |
            | Permitirá ordenar posteriormente campos en grupos como:
            |
            | general
            | ubicacion
            | compra
            | tecnico
            | adicional
            |
            */

            $table->string(
                'seccion',
                50
            )->default('general');


            /*
            |--------------------------------------------------------------------------
            | Configuración del formulario
            |--------------------------------------------------------------------------
            */

            $table->boolean(
                'visible'
            )->default(true);

            $table->boolean(
                'obligatorio'
            )->default(false);

            $table->smallInteger(
                'orden'
            )->default(0);


            /*
            |--------------------------------------------------------------------------
            | Restricciones
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'id_tipo_equipo',
                    'id_campo',
                ],
                'tipo_equipo_campos_tipo_campo_unique'
            );


            /*
            |--------------------------------------------------------------------------
            | Índice de lectura
            |--------------------------------------------------------------------------
            |
            | Será el acceso habitual al construir el formulario:
            |
            | WHERE id_tipo_equipo = ?
            | AND visible = true
            | ORDER BY orden
            |
            */

            $table->index(
                [
                    'id_tipo_equipo',
                    'visible',
                    'orden',
                ],
                'tipo_equipo_campos_tipo_visible_orden_index'
            );


            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            $table->foreign(
                'id_tipo_equipo',
                'tipo_equipo_campos_tipo_foreign'
            )
                ->references(
                    'id_tipo_equipo'
                )
                ->on(
                    'tipos_equipo'
                )
                ->onUpdate('cascade')
                ->onDelete('cascade');


            $table->foreign(
                'id_campo',
                'tipo_equipo_campos_campo_foreign'
            )
                ->references(
                    'id_campo'
                )
                ->on(
                    'campos_equipo'
                )
                ->onUpdate('cascade')
                ->onDelete('restrict');


            $table->foreign(
                'id_catalogo',
                'tipo_equipo_campos_catalogo_foreign'
            )
                ->references(
                    'id_catalogo'
                )
                ->on(
                    'catalogos'
                )
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });


        /*
        |--------------------------------------------------------------------------
        | VINCULAR ATRIBUTOS DINÁMICOS CON SU DEFINICIÓN
        |--------------------------------------------------------------------------
        |
        | atributos_equipo actualmente guarda:
        |
        | id_equipo
        | nombre
        | valor
        |
        | Conservamos "nombre" para compatibilidad con datos existentes, pero
        | agregamos id_campo para que los nuevos atributos tengan una identidad
        | estable.
        |
        */

        Schema::table('atributos_equipo', function (Blueprint $table) {
            $table->bigInteger(
                'id_campo'
            )->nullable();


            $table->foreign(
                'id_campo',
                'atributos_equipo_campo_foreign'
            )
                ->references(
                    'id_campo'
                )
                ->on(
                    'campos_equipo'
                )
                ->onUpdate('cascade')
                ->onDelete('restrict');


            /*
            |--------------------------------------------------------------------------
            | Un mismo campo dinámico sólo puede existir una vez por equipo.
            |--------------------------------------------------------------------------
            |
            | PostgreSQL permite múltiples NULL en un UNIQUE, por lo que los
            | registros existentes sin id_campo no generan conflicto.
            |
            */

            $table->unique(
                [
                    'id_equipo',
                    'id_campo',
                ],
                'atributos_equipo_equipo_campo_unique'
            );
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Primero quitamos la relación agregada a atributos_equipo.
        |--------------------------------------------------------------------------
        */

        Schema::table('atributos_equipo', function (Blueprint $table) {
            $table->dropUnique(
                'atributos_equipo_equipo_campo_unique'
            );

            $table->dropForeign(
                'atributos_equipo_campo_foreign'
            );

            $table->dropColumn(
                'id_campo'
            );
        });


        /*
        |--------------------------------------------------------------------------
        | Después eliminamos las tablas nuevas en orden inverso.
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists(
            'tipo_equipo_campos'
        );

        Schema::dropIfExists(
            'campos_equipo'
        );
    }
};