<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Suscripciones push
|--------------------------------------------------------------------------
|
| Una fila por usuario y dispositivo/navegador que activó
| «Notificaciones en este dispositivo» (Ajustes › Notificaciones).
| endpoint_hash (sha256 del endpoint) evita duplicados; el endpoint
| completo puede superar los 500 caracteres.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('suscripciones_push')) {
            return;
        }

        Schema::create('suscripciones_push', function (Blueprint $table) {
            $table->bigIncrements('id_suscripcion');
            $table->bigInteger('id_usuario');
            $table->text('endpoint');
            $table->string('endpoint_hash', 64)->unique('uq_suscripciones_push_endpoint');
            $table->string('p256dh', 255);
            $table->string('auth', 255);
            $table->string('dispositivo', 255)->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_ultimo_envio')->nullable();

            $table->index('id_usuario', 'idx_suscripciones_push_usuario');

            $table->foreign('id_usuario', 'fk_suscripciones_push_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripciones_push');
    }
};
