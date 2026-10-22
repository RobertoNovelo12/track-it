<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Solicitudes de cambio: «hotel» en lugar de «área»
|--------------------------------------------------------------------------
|
| El área ya no se usa (cada usuario tiene hotel: usuarios.id_unidad).
| Se conserva «area» en la lista para no invalidar solicitudes antiguas.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('solicitudes_cambio_organizacion')) {
            return;
        }

        DB::statement('ALTER TABLE solicitudes_cambio_organizacion DROP CONSTRAINT IF EXISTS chk_solicitudes_campo');

        DB::statement("ALTER TABLE solicitudes_cambio_organizacion
            ADD CONSTRAINT chk_solicitudes_campo
            CHECK (campo IN ('numero_colaborador', 'puesto', 'rol', 'area', 'hotel', 'departamento'))");
    }

    public function down(): void
    {
        if (! Schema::hasTable('solicitudes_cambio_organizacion')) {
            return;
        }

        DB::table('solicitudes_cambio_organizacion')->where('campo', 'hotel')->delete();

        DB::statement('ALTER TABLE solicitudes_cambio_organizacion DROP CONSTRAINT IF EXISTS chk_solicitudes_campo');

        DB::statement("ALTER TABLE solicitudes_cambio_organizacion
            ADD CONSTRAINT chk_solicitudes_campo
            CHECK (campo IN ('numero_colaborador', 'puesto', 'rol', 'area', 'departamento'))");
    }
};
