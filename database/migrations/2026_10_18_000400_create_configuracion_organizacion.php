<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Configuración de Organización
|--------------------------------------------------------------------------
|
| 1. Crea configuracion_organizacion (una sola fila, id = 1) con valores
|    equivalentes al comportamiento actual:
|      - Sin restricción de dominio.
|      - Campos de registro con la misma obligatoriedad que hoy.
|      - Logotipo predeterminado, escala 100 %, versión blanca en modo oscuro.
|
| 2. Agrega usuarios.id_unidad para registrar el hotel del usuario.
|    Se conserva usuarios.id_area para los módulos que todavía la usan.
|    Los usuarios existentes reciben el hotel creado a partir de su área.
|
| 3. Permite NULL en los campos de registro que ahora pueden ser
|    opcionales u ocultos, y en sedes.codigo.
|
| configuracion_instalacion NO se utiliza: es inmutable por diseño.
|
*/

return new class extends Migration
{
    /*
     * Columnas que pueden quedar vacías cuando el administrador
     * configure el campo como opcional u oculto.
     */
    private const COLUMNAS_OPCIONALES = [
        'usuarios' => [
            'apellido_materno',
            'telefono',
            'numero_colaborador',
            'puesto',
            'id_rol',
        ],
        'sedes' => [
            'codigo',
            'descripcion',
        ],
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException(
                'Esta migración requiere PostgreSQL.'
            );
        }

        if (Schema::hasTable('configuracion_organizacion')) {
            throw new RuntimeException(
                'Ya existe configuracion_organizacion; revisa su contenido antes de migrar.'
            );
        }

        if (! Schema::hasTable('unidades_organizativas')) {
            throw new RuntimeException(
                'Primero debe aplicarse 2026_10_18_000200_generalize_organizational_units.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 1. Tabla de configuración
        |--------------------------------------------------------------------------
        |
        | El logotipo se guarda en la base de datos (base64) porque la
        | aplicación se despliega en contenedores: un archivo subido al
        | disco se perdería en cada despliegue.
        |
        */

        DB::statement(<<<'SQL'
            CREATE TABLE configuracion_organizacion (
                id smallint PRIMARY KEY DEFAULT 1 CHECK (id = 1),

                nombre_organizacion varchar(150) NULL,

                restringir_dominio_correo boolean NOT NULL DEFAULT false,

                dominios_correo jsonb NOT NULL DEFAULT '[]'::jsonb
                    CHECK (jsonb_typeof(dominios_correo) = 'array'),

                campos_registro jsonb NOT NULL DEFAULT '{}'::jsonb
                    CHECK (jsonb_typeof(campos_registro) = 'object'),

                logo_base64 text NULL,

                logo_mime varchar(40) NULL
                    CHECK (
                        logo_mime IS NULL
                        OR logo_mime IN ('image/png', 'image/jpeg', 'image/webp')
                    ),

                logo_nombre_original varchar(255) NULL,

                logo_version varchar(40) NULL,

                logo_escala smallint NOT NULL DEFAULT 100
                    CHECK (logo_escala BETWEEN 50 AND 160),

                logo_modo_oscuro varchar(20) NOT NULL DEFAULT 'blanco'
                    CHECK (logo_modo_oscuro IN ('original', 'invertir', 'blanco')),

                actualizado_por bigint NULL
                    REFERENCES usuarios (id_usuario) ON DELETE SET NULL,

                actualizado_en timestamptz NOT NULL DEFAULT CURRENT_TIMESTAMP,

                CONSTRAINT configuracion_organizacion_logo_completo CHECK (
                    (logo_base64 IS NULL AND logo_mime IS NULL AND logo_version IS NULL)
                    OR (logo_base64 IS NOT NULL AND logo_mime IS NOT NULL AND logo_version IS NOT NULL)
                )
            )
        SQL);

        DB::table('configuracion_organizacion')->insert([
            'id' => 1,
            'nombre_organizacion' => 'Grand Palladium Hotels & Resorts',
            'restringir_dominio_correo' => false,
            'dominios_correo' => json_encode([]),
            'campos_registro' => json_encode([
                'apellido_materno' => 'opcional',
                'telefono' => 'oculto',
                'numero_colaborador' => 'obligatorio',
                'puesto' => 'obligatorio',
                'id_rol' => 'obligatorio',
            ]),
            'logo_escala' => 100,
            'logo_modo_oscuro' => 'blanco',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. Hotel del usuario
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('usuarios', 'id_unidad')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->bigInteger('id_unidad')
                    ->nullable();

                $table->index('id_unidad');

                $table->foreign('id_unidad')
                    ->references('id_unidad')
                    ->on('unidades_organizativas')
                    ->restrictOnDelete();
            });
        }

        // Los hoteles iniciales conservan el área de la que provienen.
        DB::statement(<<<'SQL'
            UPDATE usuarios AS u
            SET id_unidad = uo.id_unidad
            FROM unidades_organizativas AS uo
            WHERE u.id_unidad IS NULL
              AND u.id_area IS NOT NULL
              AND uo.id_area_origen = u.id_area
        SQL);

        /*
        |--------------------------------------------------------------------------
        | 3. Columnas que ahora pueden ser opcionales
        |--------------------------------------------------------------------------
        */

        foreach (self::COLUMNAS_OPCIONALES as $tabla => $columnas) {
            foreach ($columnas as $columna) {
                $obligatoria = DB::table('information_schema.columns')
                    ->where('table_schema', DB::raw('current_schema()'))
                    ->where('table_name', $tabla)
                    ->where('column_name', $columna)
                    ->where('is_nullable', 'NO')
                    ->exists();

                if ($obligatoria) {
                    DB::statement(sprintf(
                        'ALTER TABLE %s ALTER COLUMN %s DROP NOT NULL',
                        $tabla,
                        $columna
                    ));
                }
            }
        }
    }

    public function down(): void
    {
        /*
         * No se restaura NOT NULL en las columnas del punto 3:
         * podrían existir usuarios registrados sin esos datos.
         */
        if (Schema::hasColumn('usuarios', 'id_unidad')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropForeign(['id_unidad']);
                $table->dropIndex(['id_unidad']);
                $table->dropColumn('id_unidad');
            });
        }

        Schema::dropIfExists('configuracion_organizacion');
    }
};
