<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Logotipo con fondo en modo oscuro
|--------------------------------------------------------------------------
|
| Nueva apariencia «fondo»: el logotipo se muestra sobre una placa de
| color (logo_color_fondo, blanco por defecto) para logotipos que no se
| leen sobre el tema oscuro.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('configuracion_organizacion')) {
            return;
        }

        DB::statement('ALTER TABLE configuracion_organizacion DROP CONSTRAINT IF EXISTS configuracion_organizacion_logo_modo_oscuro_check');

        DB::statement("ALTER TABLE configuracion_organizacion
            ADD CONSTRAINT configuracion_organizacion_logo_modo_oscuro_check
            CHECK (logo_modo_oscuro IN ('original', 'invertir', 'blanco', 'fondo'))");

        if (! Schema::hasColumn('configuracion_organizacion', 'logo_color_fondo')) {
            DB::statement("ALTER TABLE configuracion_organizacion
                ADD COLUMN logo_color_fondo varchar(7) NOT NULL DEFAULT '#FFFFFF'
                CHECK (logo_color_fondo ~ '^#[0-9A-Fa-f]{6}$')");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('configuracion_organizacion')) {
            return;
        }

        DB::table('configuracion_organizacion')->where('logo_modo_oscuro', 'fondo')->update(['logo_modo_oscuro' => 'original']);

        DB::statement('ALTER TABLE configuracion_organizacion DROP CONSTRAINT IF EXISTS configuracion_organizacion_logo_modo_oscuro_check');

        DB::statement("ALTER TABLE configuracion_organizacion
            ADD CONSTRAINT configuracion_organizacion_logo_modo_oscuro_check
            CHECK (logo_modo_oscuro IN ('original', 'invertir', 'blanco'))");

        if (Schema::hasColumn('configuracion_organizacion', 'logo_color_fondo')) {
            DB::statement('ALTER TABLE configuracion_organizacion DROP COLUMN logo_color_fondo');
        }
    }
};
