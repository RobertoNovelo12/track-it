<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Códigos no registrados en «Equipos recibidos»
|--------------------------------------------------------------------------
|
| Si el teléfono escanea un código que no está en el inventario, se puede
| enviar igual a la PC para darlo de alta: se guarda el código leído
| (probablemente el número de serie) sin equipo (id_equipo NULL).
| Un mismo código solo puede estar una vez pendiente por usuario.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('equipos_recibidos')) {
            return;
        }

        if (! Schema::hasColumn('equipos_recibidos', 'codigo')) {
            Schema::table('equipos_recibidos', function (Blueprint $table) {
                $table->string('codigo', 100)->nullable();
            });
        }

        DB::statement('ALTER TABLE equipos_recibidos ALTER COLUMN id_equipo DROP NOT NULL');

        DB::statement(
            'CREATE UNIQUE INDEX IF NOT EXISTS uq_equipos_recibidos_codigo_pendiente
             ON equipos_recibidos (id_usuario, codigo)
             WHERE id_equipo IS NULL AND atendido_en IS NULL'
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('equipos_recibidos')) {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS uq_equipos_recibidos_codigo_pendiente');
        DB::table('equipos_recibidos')->whereNull('id_equipo')->delete();
        DB::statement('ALTER TABLE equipos_recibidos ALTER COLUMN id_equipo SET NOT NULL');

        if (Schema::hasColumn('equipos_recibidos', 'codigo')) {
            Schema::table('equipos_recibidos', function (Blueprint $table) {
                $table->dropColumn('codigo');
            });
        }
    }
};
