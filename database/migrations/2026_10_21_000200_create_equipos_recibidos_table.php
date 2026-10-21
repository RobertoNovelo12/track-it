<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Equipos recibidos desde el teléfono
|--------------------------------------------------------------------------
|
| Bandeja personal: lo que un usuario escanea en su teléfono y «envía a
| la PC» le llega a su misma cuenta (id_usuario). Sale de la bandeja al
| completar la baja del equipo (motivo «baja») o al marcarlo como
| atendido (motivo «manual»).
|
| Un equipo solo puede estar una vez pendiente por usuario (índice único
| parcial), para que escanear dos veces no lo duplique.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('equipos_recibidos')) {
            Schema::create('equipos_recibidos', function (Blueprint $table) {
                $table->bigIncrements('id_recibido');
                $table->bigInteger('id_equipo');
                $table->bigInteger('id_usuario');
                $table->timestamp('recibido_en')->useCurrent();
                $table->timestamp('atendido_en')->nullable();
                $table->string('motivo_atencion', 20)->nullable();

                $table->foreign('id_equipo', 'fk_equipos_recibidos_equipo')
                    ->references('id_equipo')
                    ->on('equipos')
                    ->cascadeOnDelete();

                $table->foreign('id_usuario', 'fk_equipos_recibidos_usuario')
                    ->references('id_usuario')
                    ->on('usuarios')
                    ->cascadeOnDelete();

                $table->index(['id_usuario', 'atendido_en'], 'idx_equipos_recibidos_bandeja');
                $table->index('id_equipo', 'idx_equipos_recibidos_equipo');
            });
        }

        DB::statement(
            'CREATE UNIQUE INDEX IF NOT EXISTS uq_equipos_recibidos_pendiente
             ON equipos_recibidos (id_usuario, id_equipo)
             WHERE atendido_en IS NULL'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos_recibidos');
    }
};
