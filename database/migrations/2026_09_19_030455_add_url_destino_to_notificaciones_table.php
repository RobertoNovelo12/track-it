<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar la migración.
     */
    public function up(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Destino opcional
            |--------------------------------------------------------------------------
            |
            | Algunas notificaciones podrán llevar al usuario directamente
            | a la sección relacionada con el evento.
            |
            | Ejemplo:
            |
            | /ajustes?section=seguridad#sesiones
            |
            */

            $table->string(
                'url_destino',
                500
            )
                ->nullable();
        });
    }


    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {

            $table->dropColumn(
                'url_destino'
            );
        });
    }
};