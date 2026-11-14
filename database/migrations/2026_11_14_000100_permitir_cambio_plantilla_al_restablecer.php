<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * La plantilla sigue protegida contra cambios normales, pero el asistente
 * de configuración inicial (tras restablecer la instancia) puede elegirla:
 * PlantillaInstalacionService::cambiar() activa, solo dentro de su
 * transacción, la variable trackit.permitir_cambio_plantilla.
 * Borrar o vaciar la tabla sigue prohibido.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION impedir_cambio_plantilla_instalacion()
            RETURNS trigger
            LANGUAGE plpgsql
            AS $$
            BEGIN
                IF TG_OP = 'UPDATE'
                    AND current_setting('trackit.permitir_cambio_plantilla', true) = 'on'
                THEN
                    RETURN NULL;
                END IF;

                RAISE EXCEPTION
                    'La plantilla de la instalación no puede cambiarse ni eliminarse.';

                RETURN NULL;
            END;
            $$;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION impedir_cambio_plantilla_instalacion()
            RETURNS trigger
            LANGUAGE plpgsql
            AS $$
            BEGIN
                RAISE EXCEPTION
                    'La plantilla de la instalación no puede cambiarse ni eliminarse.';

                RETURN NULL;
            END;
            $$;
        SQL);
    }
};
