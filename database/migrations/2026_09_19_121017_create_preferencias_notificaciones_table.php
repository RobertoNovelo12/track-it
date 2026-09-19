<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'preferencias_notificaciones',
            function (Blueprint $table) {

                $table
                    ->bigInteger('id_usuario')
                    ->primary();


                /*
                |--------------------------------------------------------------------------
                | Actividad operativa
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean('notificar_asignaciones_movimientos')
                    ->default(true);

                $table
                    ->boolean('notificar_mantenimientos')
                    ->default(true);

                $table
                    ->boolean('notificar_cambios_equipos')
                    ->default(true);


                /*
                |--------------------------------------------------------------------------
                | Administración
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean('notificar_usuarios_accesos')
                    ->default(true);

                $table
                    ->boolean('notificar_reportes')
                    ->default(false);


                /*
                |--------------------------------------------------------------------------
                | Centro de notificaciones
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean('solo_no_leidas')
                    ->default(false);

                $table
                    ->boolean('mantener_historial')
                    ->default(true);


                /*
                |--------------------------------------------------------------------------
                | Control
                |--------------------------------------------------------------------------
                */

                $table
                    ->timestamp('fecha_actualizacion')
                    ->useCurrent();


                /*
                |--------------------------------------------------------------------------
                | Relación con usuario
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreign('id_usuario')
                    ->references('id_usuario')
                    ->on('usuarios')
                    ->cascadeOnDelete();
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'preferencias_notificaciones'
        );
    }
};