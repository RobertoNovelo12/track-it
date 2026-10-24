<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| equipos.id_ubicacion
|--------------------------------------------------------------------------
|
| Destino del campo «Ubicación» (Cocina fría, Cocina caliente…) que cada
| tipo de equipo puede agregar desde «Campos disponibles». Es opcional.
| Si la columna ya existe no se toca.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('equipos', 'id_ubicacion')) {
            return;
        }

        Schema::table('equipos', function (Blueprint $table) {
            $table->bigInteger('id_ubicacion')->nullable();
            $table->index('id_ubicacion', 'idx_equipos_ubicacion');
            $table->foreign('id_ubicacion', 'fk_equipos_ubicacion')
                ->references('id_ubicacion')
                ->on('ubicaciones')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // La columna pudo existir antes de esta migración: no se elimina.
    }
};
