<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Row Level Security en todas las tablas del esquema public (Supabase)
|--------------------------------------------------------------------------
|
| Supabase publica las tablas de «public» en su API REST (PostgREST).
| Sin RLS, quien tenga la URL del proyecto y la llave anon podría leer o
| modificar datos sin pasar por Track IT.
|
| Activar RLS SIN políticas bloquea esa API para los roles anon y
| authenticated. La app no se ve afectada: se conecta directo a Postgres
| con el usuario dueño de las tablas, que no está sujeto a RLS.
|
| Solo aplica en PostgreSQL. Tablas creadas después de esta migración:
| vuelve a correrla con
|   php artisan migrate:refresh --path=database/migrations/2026_10_28_000100_activar_rls_tablas_publicas.php
| o activa RLS desde el panel de Supabase.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            DO $$
            DECLARE
                t record;
            BEGIN
                FOR t IN
                    SELECT c.relname
                    FROM pg_class c
                    JOIN pg_namespace n ON n.oid = c.relnamespace
                    WHERE n.nspname = 'public'
                      AND c.relkind IN ('r', 'p')
                      AND NOT c.relrowsecurity
                      -- Seguridad extra: solo tablas que el usuario de la app puede seguir
                      -- usando sin políticas (es su dueño o tiene BYPASSRLS).
                      AND (
                          pg_get_userbyid(c.relowner) = current_user
                          OR (SELECT r.rolbypassrls FROM pg_roles r WHERE r.rolname = current_user)
                      )
                LOOP
                    EXECUTE format('ALTER TABLE public.%I ENABLE ROW LEVEL SECURITY', t.relname);
                END LOOP;
            END
            $$;
        SQL);
    }

    public function down(): void
    {
        // No se desactiva: dejar las tablas expuestas no es un estado al que convenga volver.
    }
};
