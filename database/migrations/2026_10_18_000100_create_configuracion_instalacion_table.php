<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException(
                'Esta migración requiere PostgreSQL.'
            );
        }

        /*
         * Solo puede existir una configuración de instalación.
         * La clave identifica la plantilla, no los datos editables.
         */
        DB::statement(<<<'SQL'
            CREATE TABLE configuracion_instalacion (
                id smallint PRIMARY KEY DEFAULT 1 CHECK (id = 1),

                plantilla varchar(30) NOT NULL
                    CHECK (plantilla IN ('hoteles', 'escuelas')),

                version_plantilla smallint NOT NULL DEFAULT 1
                    CHECK (version_plantilla > 0),

                configurada_en timestamptz NOT NULL
                    DEFAULT CURRENT_TIMESTAMP
            )
        SQL);

        /*
         * Esta migración corresponde a la instalación hotelera
         * que ya contiene nuestros datos actuales.
         */
        DB::table('configuracion_instalacion')->insert([
            'id' => 1,
            'plantilla' => 'hoteles',
            'version_plantilla' => 1,
        ]);

        /*
         * Impedir cambiar, borrar o vaciar la configuración
         * mediante operaciones normales sobre la tabla.
         */
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION impedir_cambio_plantilla_instalacion()
            RETURNS trigger
            LANGUAGE plpgsql
            AS $$
            BEGIN
                RAISE EXCEPTION
                    'La plantilla de la instalación no puede cambiarse ni eliminarse.';

                RETURN NULL;
            END;
            $$;

            CREATE TRIGGER configuracion_instalacion_inmutable
            BEFORE UPDATE OR DELETE OR TRUNCATE
            ON configuracion_instalacion
            FOR EACH STATEMENT
            EXECUTE FUNCTION impedir_cambio_plantilla_instalacion();
        SQL);
    }

    public function down(): void
    {
        throw new RuntimeException(
            'La plantilla es permanente. Esta migración no permite eliminar su configuración mediante rollback.'
        );
    }
};