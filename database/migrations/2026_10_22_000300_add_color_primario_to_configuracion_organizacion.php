<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Color principal de la organización
|--------------------------------------------------------------------------
|
| NULL = colores predeterminados del sistema. Con un color, el layout
| reemplaza --theme-primary y sus variantes (claro y oscuro).
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('configuracion_organizacion')
            && ! Schema::hasColumn('configuracion_organizacion', 'color_primario')) {
            DB::statement("ALTER TABLE configuracion_organizacion
                ADD COLUMN color_primario varchar(7) NULL
                CHECK (color_primario IS NULL OR color_primario ~ '^#[0-9A-Fa-f]{6}$')");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('configuracion_organizacion', 'color_primario')) {
            DB::statement('ALTER TABLE configuracion_organizacion DROP COLUMN color_primario');
        }
    }
};
