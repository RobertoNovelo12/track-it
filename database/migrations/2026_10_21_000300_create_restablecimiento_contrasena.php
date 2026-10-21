<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Restablecimiento de contraseña por un administrador
|--------------------------------------------------------------------------
|
| - usuarios.fecha_nacimiento: una de las preguntas de seguridad.
| - usuarios.debe_cambiar_password: tras recibir una contraseña temporal
|   el usuario debe elegir una nueva al entrar.
| - solicitudes_restablecimiento: el usuario respondió sus preguntas en
|   «¿Olvidaste tu contraseña?» y espera a que un administrador le dé una
|   contraseña temporal. Sin una solicitud vigente no se puede restablecer.
| - El estado «Pendiente de aprobación» se muestra solo como «Pendiente».
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('usuarios', 'fecha_nacimiento')) {
                $table->date('fecha_nacimiento')->nullable();
            }

            if (! Schema::hasColumn('usuarios', 'debe_cambiar_password')) {
                $table->boolean('debe_cambiar_password')->default(false);
            }
        });

        if (! Schema::hasTable('solicitudes_restablecimiento')) {
            Schema::create('solicitudes_restablecimiento', function (Blueprint $table) {
                $table->bigIncrements('id_solicitud');
                $table->bigInteger('id_usuario');
                $table->timestamp('fecha_solicitud')->useCurrent();
                $table->string('ip', 45)->nullable();
                $table->timestamp('fecha_atencion')->nullable();
                $table->bigInteger('id_atendio')->nullable();

                $table->foreign('id_usuario', 'fk_solicitudes_restablecimiento_usuario')
                    ->references('id_usuario')
                    ->on('usuarios')
                    ->cascadeOnDelete();

                $table->foreign('id_atendio', 'fk_solicitudes_restablecimiento_atendio')
                    ->references('id_usuario')
                    ->on('usuarios')
                    ->nullOnDelete();

                $table->index(['id_usuario', 'fecha_atencion'], 'idx_solicitudes_restablecimiento_usuario');
            });
        }

        DB::table('estados_usuario')
            ->whereRaw('UPPER(clave) = ?', ['PENDIENTE'])
            ->update(['nombre' => 'Pendiente']);
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_restablecimiento');

        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'debe_cambiar_password')) {
                $table->dropColumn('debe_cambiar_password');
            }

            if (Schema::hasColumn('usuarios', 'fecha_nacimiento')) {
                $table->dropColumn('fecha_nacimiento');
            }
        });

        DB::table('estados_usuario')
            ->whereRaw('UPPER(clave) = ?', ['PENDIENTE'])
            ->update(['nombre' => 'Pendiente de aprobación']);
    }
};
