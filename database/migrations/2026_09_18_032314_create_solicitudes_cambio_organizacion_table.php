<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('solicitudes_cambio_organizacion', function (Blueprint $table) {

            $table->bigIncrements('id_solicitud');


            /*
            |--------------------------------------------------------------------------
            | Usuario que realiza la solicitud
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('id_usuario');


            /*
            |--------------------------------------------------------------------------
            | Cambio solicitado
            |--------------------------------------------------------------------------
            */

            $table->string('campo', 50);

            $table->text('valor_actual')
                ->nullable();

            $table->text('valor_solicitado');

            $table->text('motivo')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Estado de la solicitud
            |--------------------------------------------------------------------------
            */

            $table->string('estado', 20)
                ->default('pendiente');


            /*
            |--------------------------------------------------------------------------
            | Revisión administrativa
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('revisado_por')
                ->nullable();

            $table->text('comentario_revision')
                ->nullable();

            $table->timestamp('fecha_revision')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Fechas
            |--------------------------------------------------------------------------
            */

            $table->timestamp('fecha_solicitud')
                ->useCurrent();


            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['id_usuario', 'estado'],
                'idx_solicitudes_usuario_estado'
            );

            $table->index(
                'estado',
                'idx_solicitudes_estado'
            );


            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->restrictOnDelete();

            $table->foreign('revisado_por')
                ->references('id_usuario')
                ->on('usuarios')
                ->restrictOnDelete();
        });


        /*
        |--------------------------------------------------------------------------
        | Restricción de campos permitidos
        |--------------------------------------------------------------------------
        */

        DB::statement("
            ALTER TABLE solicitudes_cambio_organizacion
            ADD CONSTRAINT chk_solicitudes_campo
            CHECK (
                campo IN (
                    'numero_colaborador',
                    'puesto',
                    'rol',
                    'area',
                    'departamento'
                )
            )
        ");


        /*
        |--------------------------------------------------------------------------
        | Restricción de estados permitidos
        |--------------------------------------------------------------------------
        */

        DB::statement("
            ALTER TABLE solicitudes_cambio_organizacion
            ADD CONSTRAINT chk_solicitudes_estado
            CHECK (
                estado IN (
                    'pendiente',
                    'aprobada',
                    'rechazada',
                    'cancelada'
                )
            )
        ");
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'solicitudes_cambio_organizacion'
        );
    }
};