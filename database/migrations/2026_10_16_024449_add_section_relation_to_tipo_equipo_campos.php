<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Agregar relación con la nueva tabla de secciones
        |--------------------------------------------------------------------------
        |
        | La dejamos nullable durante esta migración para poder:
        |
        | 1. Crear las secciones base.
        | 2. Relacionar los campos existentes.
        | 3. Conservar temporalmente la columna legacy "seccion".
        |
        */

        Schema::table('tipo_equipo_campos', function (Blueprint $table) {
            $table
                ->unsignedBigInteger('id_seccion')
                ->nullable();

            $table
                ->foreign('id_seccion')
                ->references('id_seccion')
                ->on('tipo_equipo_secciones')
                ->restrictOnDelete();

            $table->index(
                'id_seccion',
                'idx_tipo_equipo_campos_seccion'
            );
        });


        /*
        |--------------------------------------------------------------------------
        | Obtener tipos de equipo existentes
        |--------------------------------------------------------------------------
        */

        $tipos = DB::table('tipos_equipo')
            ->select(
                'id_tipo_equipo',
                'nombre'
            )
            ->orderBy('id_tipo_equipo')
            ->get();


        foreach ($tipos as $tipo) {
            $tipoId =
                (int) $tipo->id_tipo_equipo;

            $tipoNombre =
                trim(
                    (string) $tipo->nombre
                );


            /*
            |--------------------------------------------------------------------------
            | Información general
            |--------------------------------------------------------------------------
            */

            $generalId =
                DB::table('tipo_equipo_secciones')
                    ->insertGetId(
                        [
                            'id_tipo_equipo' =>
                                $tipoId,

                            'clave' =>
                                'general',

                            'nombre' =>
                                'Información general',

                            'orden' =>
                                10,

                            'es_base' =>
                                true,
                        ],
                        'id_seccion'
                    );


            /*
            |--------------------------------------------------------------------------
            | Información del tipo de equipo
            |--------------------------------------------------------------------------
            |
            | Ejemplos:
            |
            | Información de Laptop
            | Información de Monitor
            | Información de Access Point
            |
            */

            $informacionTipoId =
                DB::table('tipo_equipo_secciones')
                    ->insertGetId(
                        [
                            'id_tipo_equipo' =>
                                $tipoId,

                            'clave' =>
                                'informacion_tipo',

                            'nombre' =>
                                'Información de '
                                . $tipoNombre,

                            'orden' =>
                                20,

                            'es_base' =>
                                true,
                        ],
                        'id_seccion'
                    );


            /*
            |--------------------------------------------------------------------------
            | Información adicional
            |--------------------------------------------------------------------------
            */

            $adicionalId =
                DB::table('tipo_equipo_secciones')
                    ->insertGetId(
                        [
                            'id_tipo_equipo' =>
                                $tipoId,

                            'clave' =>
                                'adicional',

                            'nombre' =>
                                'Información adicional',

                            'orden' =>
                                30,

                            'es_base' =>
                                true,
                        ],
                        'id_seccion'
                    );


            /*
            |--------------------------------------------------------------------------
            | Migrar sección legacy: general + compra
            |--------------------------------------------------------------------------
            |
            | Ambos grupos pasan a:
            |
            | Información general
            |
            */

            DB::table('tipo_equipo_campos')
                ->where(
                    'id_tipo_equipo',
                    $tipoId
                )
                ->whereIn(
                    'seccion',
                    [
                        'general',
                        'compra',
                    ]
                )
                ->update([
                    'id_seccion' =>
                        $generalId,
                ]);


            /*
            |--------------------------------------------------------------------------
            | Migrar sección legacy: especifico
            |--------------------------------------------------------------------------
            |
            | Pasa a:
            |
            | Información de {Tipo de equipo}
            |
            */

            DB::table('tipo_equipo_campos')
                ->where(
                    'id_tipo_equipo',
                    $tipoId
                )
                ->where(
                    'seccion',
                    'especifico'
                )
                ->update([
                    'id_seccion' =>
                        $informacionTipoId,
                ]);


            /*
            |--------------------------------------------------------------------------
            | Migrar sección legacy: ubicacion + adicional
            |--------------------------------------------------------------------------
            |
            | Ambos grupos pasan a:
            |
            | Información adicional
            |
            */

            DB::table('tipo_equipo_campos')
                ->where(
                    'id_tipo_equipo',
                    $tipoId
                )
                ->whereIn(
                    'seccion',
                    [
                        'ubicacion',
                        'adicional',
                    ]
                )
                ->update([
                    'id_seccion' =>
                        $adicionalId,
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Verificación final
        |--------------------------------------------------------------------------
        |
        | No permitimos que la migración termine si existe algún campo que no
        | haya podido relacionarse con una sección.
        |
        */

        $sinSeccion =
            DB::table('tipo_equipo_campos')
                ->whereNull('id_seccion')
                ->count();


        if ($sinSeccion > 0) {
            throw new RuntimeException(
                "Existen {$sinSeccion} campos sin sección asignada."
            );
        }
    }


    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Quitar relación de los campos
        |--------------------------------------------------------------------------
        */

        Schema::table('tipo_equipo_campos', function (Blueprint $table) {
            $table->dropForeign([
                'id_seccion',
            ]);

            $table->dropIndex(
                'idx_tipo_equipo_campos_seccion'
            );

            $table->dropColumn(
                'id_seccion'
            );
        });


        /*
        |--------------------------------------------------------------------------
        | Eliminar secciones base creadas por esta migración
        |--------------------------------------------------------------------------
        */

        DB::table('tipo_equipo_secciones')
            ->where(
                'es_base',
                true
            )
            ->whereIn(
                'clave',
                [
                    'general',
                    'informacion_tipo',
                    'adicional',
                ]
            )
            ->delete();
    }
};