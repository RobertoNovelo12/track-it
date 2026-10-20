<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Preferencias de cada usuario (Ajustes › Preferencias)
|--------------------------------------------------------------------------
|
| Un usuario sin fila usa los valores predeterminados de
| App\Services\PreferenciasUsuarioService.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('preferencias_usuario')) {
            return;
        }

        Schema::create('preferencias_usuario', function (Blueprint $table) {
            $table->bigInteger('id_usuario')->primary();

            // light | dark | system
            $table->string('tema', 10)->default('system');

            // comoda | compacta
            $table->string('densidad', 10)->default('comoda');

            // dashboard | equipos | asignaciones
            $table->string('pantalla_inicio', 20)->default('dashboard');

            $table->smallInteger('registros_por_pagina')->default(10);

            $table->bigInteger('id_unidad_predeterminada')->nullable();

            $table->string('zona_horaria', 64)->default('America/Mexico_City');

            // 24 | 12
            $table->string('formato_hora', 2)->default('24');

            $table->timestamp('fecha_actualizacion')->nullable();

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnDelete();

            $table->foreign('id_unidad_predeterminada')
                ->references('id_unidad')
                ->on('unidades_organizativas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preferencias_usuario');
    }
};
