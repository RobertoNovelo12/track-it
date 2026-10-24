<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Quitar chk_equipo_area_departamento
|--------------------------------------------------------------------------
|
| El área ya no se usa: un equipo pertenece a un hotel y, opcionalmente,
| a un departamento. Esta regla (creada directamente en la base) impedía
| guardar equipos según la combinación de área y departamento.
| equipos.id_area se conserva por compatibilidad con registros antiguos.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE equipos DROP CONSTRAINT IF EXISTS chk_equipo_area_departamento');
    }

    public function down(): void
    {
        // No se vuelve a crear: el área ya no forma parte del modelo.
    }
};
