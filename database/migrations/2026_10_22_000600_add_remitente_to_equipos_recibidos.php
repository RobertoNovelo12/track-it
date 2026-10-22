<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Quién envió el equipo (escáner → bandeja de otro usuario)
|--------------------------------------------------------------------------
|
| Desde el teléfono se puede enviar a la propia cuenta o a otro usuario
| con permiso de gestionar. id_remitente guarda quién lo escaneó; la
| bandeja muestra «Enviado por …» cuando no es el mismo usuario.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('equipos_recibidos') || Schema::hasColumn('equipos_recibidos', 'id_remitente')) {
            return;
        }

        Schema::table('equipos_recibidos', function (Blueprint $table) {
            $table->bigInteger('id_remitente')->nullable();

            $table->foreign('id_remitente', 'fk_equipos_recibidos_remitente')
                ->references('id_usuario')
                ->on('usuarios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('equipos_recibidos') || ! Schema::hasColumn('equipos_recibidos', 'id_remitente')) {
            return;
        }

        Schema::table('equipos_recibidos', function (Blueprint $table) {
            $table->dropForeign('fk_equipos_recibidos_remitente');
            $table->dropColumn('id_remitente');
        });
    }
};
