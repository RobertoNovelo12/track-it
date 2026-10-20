<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Fecha de modificación de equipos
|--------------------------------------------------------------------------
|
| La tabla solo tenía fecha_registro (creación). Esta columna guarda la
| última vez que se guardaron cambios desde «Editar equipo». Los equipos
| que aún no se han editado quedan en NULL («Sin modificaciones»).
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('equipos', 'fecha_actualizacion')) {
            return;
        }

        Schema::table('equipos', function (Blueprint $table) {
            $table->timestamp('fecha_actualizacion')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('equipos', 'fecha_actualizacion')) {
            return;
        }

        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn('fecha_actualizacion');
        });
    }
};
