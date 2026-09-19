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
        Schema::create('preferencias_seguridad', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Usuario
            |--------------------------------------------------------------------------
            |
            | Usamos id_usuario como clave primaria para garantizar que
            | cada usuario tenga solamente una configuración de seguridad.
            |
            */

            $table->bigInteger('id_usuario')
                ->primary();


            /*
            |--------------------------------------------------------------------------
            | Alertas
            |--------------------------------------------------------------------------
            */

            $table->boolean('alerta_inicio_sesion')
                ->default(true);

            $table->boolean('alerta_actividad_sospechosa')
                ->default(true);


            /*
            |--------------------------------------------------------------------------
            | Fecha de última actualización
            |--------------------------------------------------------------------------
            */

            $table->timestamp('fecha_actualizacion')
                ->useCurrent();


            /*
            |--------------------------------------------------------------------------
            | Relación con usuarios
            |--------------------------------------------------------------------------
            */

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnDelete();
        });
    }


    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'preferencias_seguridad'
        );
    }
};